<?php

namespace App\Controllers;

use App\Models\CustomFormModel;
use App\Models\FormSubmissionModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use DateTimeImmutable;
use JsonException;

class ItsForms extends ItsBase
{
    private const FIELD_TYPES = ['text', 'textarea', 'number', 'email', 'phone', 'date', 'url', 'select', 'radio', 'checkbox', 'file', 'section'];
    private const MAX_FIELDS = 40;
    private const UPLOAD_TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'text/plain' => 'txt',
        'text/csv' => 'csv',
    ];

    public function index()
    {
        if ($response = $this->needLogin()) {
            return $response;
        }

        $isAdmin = ($this->user()['role'] ?? '') === 'admin';
        $formModel = new CustomFormModel();
        $query = $formModel->orderBy('title');
        if (!$isAdmin) {
            $query->where('active', 1);
            if ($this->db->fieldExists('audience', 'custom_forms')) $query->whereIn('audience', ['staff', 'both']);
        }
        $forms = $query->findAll();
        $editId = $isAdmin ? $this->getInt('edit') : 0;
        $editForm = $editId ? $formModel->find($editId) : null;
        $fieldCounts = [];
        foreach ($forms as $form) {
            $fieldCounts[$form['id']] = count($this->decodeFields($form['fields_json']));
        }

        return view('itsupport/forms', [
            'title' => 'Forms',
            'forms' => $forms,
            'isAdmin' => $isAdmin,
            'editForm' => $editForm,
            'formFields' => $this->decodeFields($editForm['fields_json'] ?? ''),
            'formSettings' => $editForm ? $this->formSettings($editForm) : ['confirmation' => '', 'notify' => '', 'audience' => 'staff'],
            'hasAudience' => $this->db->fieldExists('audience', 'custom_forms'),
            'fieldTypes' => self::FIELD_TYPES,
            'maxFields' => self::MAX_FIELDS,
            'fieldCounts' => $fieldCounts,
            'submissionCount' => (new FormSubmissionModel())->countAllResults(),
        ]);
    }

    public function saveForm()
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }

        $title = $this->postText('title', 190);
        $description = $this->postText('description', 4000);
        $fields = $this->normalizeFields($this->request->getPost('fields'));
        if ($title === '' || $fields === null) {
            return redirect()->to(base_url('its-forms'))
                ->with('err', $title === '' ? 'Enter a form title.' : 'Add one or more valid fields. Field keys must be unique; select fields need options.');
        }

        try {
            $fieldsJson = json_encode($fields, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            log_message('error', 'Dynamic form definition encoding failed: {message}', ['message' => $exception->getMessage()]);
            return redirect()->to(base_url('its-forms'))->with('err', 'The form definition could not be saved.');
        }

        $formId = $this->postInt('id');
        $model = new CustomFormModel();
        $data = [
            'title' => $title,
            'description' => $description,
            'fields_json' => $fieldsJson,
            'active' => $this->request->getPost('active') === '1' ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('audience', 'custom_forms')) {
            $audience = (string) $this->request->getPost('audience');
            $emails = array_values(array_filter(array_map('trim', explode(',', $this->postText('notify', 500))), static fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
            $data['audience'] = in_array($audience, ['staff', 'clients', 'both'], true) ? $audience : 'staff';
            $data['settings_json'] = json_encode(['confirmation' => $this->postText('confirmation', 500), 'notify' => implode(', ', array_slice($emails, 0, 10))], JSON_UNESCAPED_UNICODE);
        }

        if ($formId) {
            if (!$model->find($formId)) {
                return redirect()->to(base_url('its-forms'))->with('err', 'That form no longer exists.');
            }
            $model->update($formId, $data);
            $this->audit('form_updated', 'forms', $title);
            $message = 'Form updated.';
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $model->insert($data);
            $this->audit('form_created', 'forms', $title);
            $message = 'Form created.';
        }

        return redirect()->to(base_url('its-forms'))->with('ok', $message);
    }

    public function archiveForm(int $id)
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }

        $model = new CustomFormModel();
        $form = $model->find($id);
        if (!$form) {
            return redirect()->to(base_url('its-forms'))->with('err', 'That form no longer exists.');
        }

        $model->update($id, ['active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->audit('form_archived', 'forms', $form['title']);
        return redirect()->to(base_url('its-forms'))->with('ok', 'Form archived. Existing submissions are retained.');
    }

    public function submit(int $id)
    {
        $user = $this->user();
        if (!$user) {
            return redirect()->to(base_url('its-login'));
        }
        // Client portal users may fill forms published to clients; staff go through the normal role checks.
        $isClient = ($user['role'] ?? '') === 'client';
        if (!$isClient && ($response = $this->needLogin())) {
            return $response;
        }
        $home = $isClient ? 'its-portal' : 'its-forms';

        $form = (new CustomFormModel())->find($id);
        $settings = $form ? $this->formSettings($form) : null;
        $allowed = $form && (($user['role'] ?? '') === 'admin' || ((int) $form['active'] && ($isClient
            ? in_array($settings['audience'], ['clients', 'both'], true) && !empty($user['client_id'])
            : in_array($settings['audience'], ['staff', 'both'], true))));
        if (!$allowed) {
            return redirect()->to(base_url($home))->with('err', 'That form is unavailable.');
        }

        $fields = $this->decodeFields($form['fields_json']);
        if (!$fields) {
            log_message('error', 'Form {id} has an invalid field definition.', ['id' => $id]);
            return redirect()->to(base_url($home))->with('err', 'This form is unavailable because its definition is invalid.');
        }

        $answers = [];
        $errors = [];
        $submitted = [];
        $uploads = [];
        if ($this->request->getMethod() === 'POST') {
            $rawAnswers = $this->request->getPost('answer');
            $rawAnswers = is_array($rawAnswers) ? $rawAnswers : [];
            $files = $this->request->getFiles();

            foreach ($fields as $field) {
                if ($field['type'] === 'section') {
                    continue;
                }
                // Fields hidden by a "show if" rule are neither validated nor stored.
                if (!$this->fieldVisible($field, $submitted)) {
                    $answers[$field['key']] = '';
                    $submitted[$field['key']] = '';
                    continue;
                }
                $raw = $rawAnswers[$field['key']] ?? '';
                if ($field['type'] === 'checkbox') {
                    $value = is_string($raw) && in_array($raw, ['1', 'yes', 'on'], true) ? 'Yes' : 'No';
                    if ($field['required'] && $value !== 'Yes') {
                        $errors[$field['key']] = 'Please check this box to continue.';
                    }
                } elseif ($field['type'] === 'file') {
                    $value = '';
                    $upload = $files['answer'][$field['key']] ?? null;
                    if ($upload instanceof \CodeIgniter\HTTP\Files\UploadedFile && $upload->getError() !== UPLOAD_ERR_NO_FILE) {
                        $mime = $upload->getMimeType();
                        if (!$upload->isValid() || !isset(self::UPLOAD_TYPES[$mime]) || $upload->getSize() > 5 * 1024 * 1024) {
                            $errors[$field['key']] = 'Choose a valid PDF, image, CSV, or text file under 5 MB.';
                        } else {
                            $uploads[$field['key']] = $upload;
                        }
                    } elseif ($field['required']) {
                        $errors[$field['key']] = 'Please choose a file to upload.';
                    }
                } else {
                    $value = is_string($raw) || is_numeric($raw) ? trim((string) $raw) : '';
                    if (strlen($value) > 5000) {
                        $errors[$field['key']] = 'Keep this response under 5,000 bytes.';
                    } elseif ($field['required'] && $value === '') {
                        $errors[$field['key']] = 'This field is required.';
                    } elseif ($value !== '' && !$this->validAnswer($field, $value)) {
                        $errors[$field['key']] = 'Enter a valid ' . $field['type'] . ' value.';
                    }
                }

                $answers[$field['key']] = $value;
                $submitted[$field['key']] = $value;
            }

            $storedFiles = [];
            if (!$errors) {
                foreach ($uploads as $key => $upload) {
                    $mime = $upload->getMimeType();
                    $extension = self::UPLOAD_TYPES[$mime];
                    $directory = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'forms';
                    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
                        log_message('error', 'Unable to create private form upload directory.');
                        $errors[$key] = 'The upload could not be stored. Please contact an administrator.';
                        break;
                    }
                    $storedName = bin2hex(random_bytes(20)) . '.' . $extension;
                    try {
                        $upload->move($directory, $storedName);
                    } catch (\RuntimeException $exception) {
                        log_message('error', 'Unable to store form attachment: {message}', ['message' => $exception->getMessage()]);
                        $errors[$key] = 'The upload could not be stored. Please try again.';
                        break;
                    }
                    $storedFiles[] = $directory . DIRECTORY_SEPARATOR . $storedName;
                    $answers[$key] = [
                        'stored_name' => $storedName,
                        'original_name' => mb_substr(basename($upload->getClientName()), 0, 190),
                    ];
                }
            }

            if (!$errors) {
                try {
                    $answersJson = json_encode($answers, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                } catch (JsonException $exception) {
                    $this->discardUploads($storedFiles);
                    log_message('error', 'Form submission encoding failed for form {id}: {message}', [
                        'id' => $id,
                        'message' => $exception->getMessage(),
                    ]);
                    $errors['_form'] = 'Your response could not be saved. Please try again.';
                }
            }

            if (!$errors) {
                try {
                    $row = [
                        'form_id' => $id,
                        'submitted_by' => (int) $this->user()['id'],
                        'answers_json' => $answersJson,
                        'submitted_at' => date('Y-m-d H:i:s'),
                    ];
                    if ($isClient && $this->db->fieldExists('client_id', 'form_submissions')) {
                        $row['client_id'] = (int) $user['client_id'];
                    }
                    $submissionId = (new FormSubmissionModel())->insert($row);
                    if (!$submissionId) {
                        $this->discardUploads($storedFiles);
                        log_message('error', 'Form submission insert returned no ID for form {id}.', ['id' => $id]);
                        $errors['_form'] = 'Your response could not be saved. Please try again.';
                    }
                } catch (DatabaseException $exception) {
                    $this->discardUploads($storedFiles);
                    log_message('error', 'Form submission insert failed for form {id}: {message}', [
                        'id' => $id,
                        'message' => $exception->getMessage(),
                    ]);
                    $errors['_form'] = 'Your response could not be saved. Please try again.';
                }
            } elseif ($storedFiles) {
                $this->discardUploads($storedFiles);
            }

            if (!$errors) {
                $this->audit('form_submitted', 'forms', 'Form ID ' . $id);
                $this->notifySubmission($form, $settings, $fields, $answers, (int) $submissionId);
                return redirect()->to(base_url($home))->with('ok', $settings['confirmation'] !== '' ? $settings['confirmation'] : 'Your response has been submitted.');
            }

            $this->response->setHeader('Cache-Control', 'no-store, private');
        }

        return view('itsupport/form_submit', [
            'title' => $form['title'],
            'form' => $form,
            'fields' => $fields,
            'errors' => $errors,
            'submitted' => $submitted,
            'home' => $home,
        ]);
    }

    /** Emails the addresses configured on the form with a summary of the new response. */
    private function notifySubmission(array $form, array $settings, array $fields, array $answers, int $submissionId): void
    {
        $to = array_filter(array_map('trim', explode(',', $settings['notify'])));
        if (!$to) return;
        $rows = '';
        foreach ($fields as $f) {
            if ($f['type'] === 'section') continue;
            $v = $answers[$f['key']] ?? '';
            $v = is_array($v) ? ($v['original_name'] ?? 'file') . ' (attachment)' : (string) $v;
            if ($v === '') continue;
            $rows .= '<tr><th style="text-align:left;border:1px solid #ddd;padding:6px;background:#f8fafc">' . esc($f['label']) . '</th><td style="border:1px solid #ddd;padding:6px">' . nl2br(esc($v)) . '</td></tr>';
        }
        $who = $this->user()['name'] ?? 'Someone';
        $html = '<h2 style="font-family:Arial">' . esc($form['title']) . '</h2><p style="font-family:Arial">New response #' . $submissionId . ' from ' . esc($who) . ' on ' . date('Y-m-d H:i') . '.</p><table style="border-collapse:collapse;font-family:Arial;font-size:13px">' . $rows . '</table><p style="font-family:Arial"><a href="' . base_url('its-form-reports?form_id=' . (int) $form['id']) . '">View all responses</a></p>';
        try {
            (new \App\Libraries\MailLib())->send($to, 'New response: ' . $form['title'], $html);
        } catch (\Throwable $e) {
            log_message('error', 'Form notification failed: {m}', ['m' => $e->getMessage()]);
        }
    }

    public function reports()
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }

        $formId = $this->getInt('form_id');
        $from = (string) $this->request->getGet('from');
        $to = (string) $this->request->getGet('to');
        $errors = [];
        if (($from !== '' && !$this->validDate($from)) || ($to !== '' && !$this->validDate($to))) {
            $errors[] = 'Use valid calendar dates for the reporting range.';
        }
        if ($from !== '' && $to !== '' && $from > $to) {
            $errors[] = 'The start date must be on or before the end date.';
        }

        $query = $this->db->table('form_submissions')
            ->select('form_submissions.id, form_submissions.form_id, form_submissions.submitted_by, form_submissions.answers_json, form_submissions.submitted_at, custom_forms.title AS form_title, users.name AS submitter_name')
            ->join('custom_forms', 'custom_forms.id = form_submissions.form_id')
            ->join('users', 'users.id = form_submissions.submitted_by', 'left')
            ->orderBy('form_submissions.submitted_at', 'DESC');
        if ($formId) {
            $query->where('form_submissions.form_id', $formId);
        }
        if ($from !== '' && $this->validDate($from)) {
            $query->where('form_submissions.submitted_at >=', $from . ' 00:00:00');
        }
        if ($to !== '' && $this->validDate($to)) {
            $query->where('form_submissions.submitted_at <', (new DateTimeImmutable($to))->modify('+1 day')->format('Y-m-d') . ' 00:00:00');
        }

        $export = $this->request->getGet('export') === 'csv';
        $rows = $query->limit($export ? 10000 : 500)->get()->getResultArray();
        if ($export) {
            // A single form exports one column per field; mixed forms fall back to a JSON column.
            $form = $formId ? (new CustomFormModel())->find($formId) : null;
            return $this->exportCsv($rows, $form ? array_values(array_filter($this->decodeFields($form['fields_json']), static fn($f) => $f['type'] !== 'section')) : []);
        }

        return view('itsupport/form_reports', [
            'title' => 'Form reports',
            'forms' => (new CustomFormModel())->orderBy('title')->findAll(),
            'rows' => $rows,
            'formId' => $formId,
            'from' => $from,
            'to' => $to,
            'errors' => $errors,
        ]);
    }

    public function downloadFile(int $submissionId, string $fieldKey)
    {
        if ($response = $this->needLogin()) {
            return $response;
        }
        if (!preg_match('/^[a-z][a-z0-9_]{0,39}$/', $fieldKey)) {
            return $this->response->setStatusCode(404);
        }

        $submission = (new FormSubmissionModel())->find($submissionId);
        if (!$submission || (($this->user()['role'] ?? '') !== 'admin' && (int) $submission['submitted_by'] !== (int) $this->user()['id'])) {
            return $this->response->setStatusCode(404);
        }
        try {
            $answers = json_decode($submission['answers_json'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            log_message('error', 'Unable to read form submission {id} attachments: {message}', ['id' => $submissionId, 'message' => $exception->getMessage()]);
            return $this->response->setStatusCode(404);
        }
        $file = $answers[$fieldKey] ?? null;
        if (!is_array($file) || !preg_match('/^[a-f0-9]{40}\.(pdf|jpg|png|webp|txt|csv)$/', $file['stored_name'] ?? '')) {
            return $this->response->setStatusCode(404);
        }
        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . $file['stored_name'];
        if (!is_file($path)) {
            return $this->response->setStatusCode(404);
        }

        $this->response->setHeader('Cache-Control', 'no-store, private');
        return $this->response->download($path, null)->setFileName(basename((string) ($file['original_name'] ?? 'attachment')));
    }

    private function discardUploads(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_file($path) && !unlink($path)) {
                log_message('error', 'Unable to remove orphaned private form attachment {path}.', ['path' => $path]);
            }
        }
    }

    private function normalizeFields($input): ?array
    {
        if (!is_array($input)) {
            return null;
        }

        $fields = [];
        $keys = [];
        foreach (array_slice(array_values($input), 0, self::MAX_FIELDS) as $rawField) {
            if (!is_array($rawField)) {
                continue;
            }
            $str = static fn(string $k): string => is_string($rawField[$k] ?? null) ? trim($rawField[$k]) : '';
            $label = mb_substr($str('label'), 0, 160);
            $type = $str('type');
            $optionValue = $str('options');
            if ($label === '') {
                continue; // blank rows are ignored
            }
            // Keys are optional in the v3 builder: derive one from the label and keep it unique.
            $key = strtolower($str('key'));
            if ($key === '') {
                $base = substr(trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower($label)), '_'), 0, 34);
                $base = preg_match('/^[a-z]/', $base) ? $base : 'f_' . $base;
                $key = $base; $i = 2;
                while (isset($keys[$key])) $key = $base . '_' . $i++;
            }
            if (!preg_match('/^[a-z][a-z0-9_]{0,39}$/', $key) || !in_array($type, self::FIELD_TYPES, true) || isset($keys[$key])) {
                return null;
            }

            $options = [];
            if (in_array($type, ['select', 'radio'], true)) {
                $options = array_values(array_unique(array_filter(array_map(
                    static fn(string $option): string => mb_substr(trim($option), 0, 100),
                    explode(',', $optionValue),
                ), static fn(string $option): bool => $option !== '')));
                if (!$options || count($options) > 30) {
                    return null;
                }
            }
            $showIf = null;
            $condField = strtolower($str('show_if_field'));
            if ($condField !== '' && isset($keys[$condField])) {
                $showIf = ['field' => $condField, 'value' => mb_substr($str('show_if_value'), 0, 100)];
            }
            $fields[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'required' => $type !== 'section' && ($rawField['required'] ?? '') === '1',
                'options' => $options,
                'help' => mb_substr($str('help'), 0, 300),
                'placeholder' => mb_substr($str('placeholder'), 0, 120),
                'width' => $str('width') === 'half' ? 'half' : 'full',
                'show_if' => $showIf,
            ];
            $keys[$key] = true;
        }

        return $fields && count($input) <= self::MAX_FIELDS ? $fields : null;
    }

    /** Whether a field is visible given the answers so far (conditional "show if" rules). */
    private function fieldVisible(array $field, array $values): bool
    {
        $rule = $field['show_if'] ?? null;
        if (!is_array($rule) || empty($rule['field'])) return true;
        $actual = (string) ($values[$rule['field']] ?? '');
        return $rule['value'] === '' ? $actual !== '' && $actual !== 'No' : $actual === $rule['value'];
    }

    /** Form-level options: who can fill it, confirmation text and who is emailed on each response. */
    private function formSettings(array $form): array
    {
        $s = json_decode((string) ($form['settings_json'] ?? ''), true);
        $s = is_array($s) ? $s : [];
        return ['confirmation' => (string) ($s['confirmation'] ?? ''), 'notify' => (string) ($s['notify'] ?? ''), 'audience' => in_array($form['audience'] ?? 'staff', ['staff', 'clients', 'both'], true) ? ($form['audience'] ?? 'staff') : 'staff'];
    }

    private function decodeFields(string $json): array
    {
        try {
            $fields = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return [];
        }
        if (!is_array($fields) || count($fields) > self::MAX_FIELDS) {
            return [];
        }
        foreach ($fields as $field) {
            if (!is_array($field)
                || !isset($field['key'], $field['label'], $field['type'])
                || !is_string($field['key'])
                || !is_string($field['label'])
                || !in_array($field['type'], self::FIELD_TYPES, true)
                || !preg_match('/^[a-z][a-z0-9_]{0,39}$/', $field['key'])
                || (in_array($field['type'], ['select', 'radio'], true) && (!is_array($field['options'] ?? null) || !$field['options']))) {
                return [];
            }
        }
        // Forms saved before v3 lack the newer properties; fill defaults so they keep working unchanged.
        return array_map(static fn(array $f): array => $f + ['options' => [], 'required' => false, 'help' => '', 'placeholder' => '', 'width' => 'full', 'show_if' => null], array_values($fields));
    }

    private function validAnswer(array $field, string $value): bool
    {
        return match ($field['type']) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'number' => is_numeric($value),
            'date' => $this->validDate($value),
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            'phone' => (bool) preg_match('/^[0-9+().\-\s]{7,30}$/', $value),
            'select', 'radio' => in_array($value, $field['options'], true),
            default => true,
        };
    }

    private function validDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function exportCsv(array $rows, array $fields = [])
    {
        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            log_message('error', 'Unable to create temporary CSV stream for form reports.');
            return redirect()->to(base_url('its-form-reports'))->with('err', 'The CSV export could not be created.');
        }

        fputcsv($stream, array_merge(['Submission ID', 'Form', 'Submitted At', 'Submitted By'], $fields ? array_column($fields, 'label') : ['Answers (JSON)']), ',', '"', '');
        foreach ($rows as $row) {
            $values = [
                (string) $row['id'],
                (string) $row['form_title'],
                (string) $row['submitted_at'],
                (string) ($row['submitter_name'] ?? 'Unknown'),
            ];
            if ($fields) {
                $answers = json_decode((string) $row['answers_json'], true) ?: [];
                foreach ($fields as $f) {
                    $v = $answers[$f['key']] ?? '';
                    $values[] = is_array($v) ? (string) ($v['original_name'] ?? '') : (string) $v;
                }
            } else {
                $values[] = (string) $row['answers_json'];
            }
            fputcsv($stream, array_map($this->spreadsheetSafe(...), $values), ',', '"', '');
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        if ($csv === false) {
            log_message('error', 'Unable to read temporary CSV stream for form reports.');
            return redirect()->to(base_url('its-form-reports'))->with('err', 'The CSV export could not be read.');
        }

        return $this->response->download('form-submissions-' . date('Ymd-His') . '.csv', $csv)
            ->setContentType('text/csv; charset=UTF-8')
            ->setHeader('Cache-Control', 'no-store, private');
    }

    private function spreadsheetSafe(string $value): string
    {
        return preg_match('/^[\s]*[=+\-@]/', $value) ? "'" . $value : $value;
    }

    private function postText(string $key, int $maxLength): string
    {
        $value = $this->request->getPost($key);
        return is_string($value) ? mb_substr(trim($value), 0, $maxLength) : '';
    }

    private function postInt(string $key): int
    {
        $value = $this->request->getPost($key);
        return is_string($value) && ctype_digit($value) ? (int) $value : 0;
    }

    private function getInt(string $key): int
    {
        $value = $this->request->getGet($key);
        return is_string($value) && ctype_digit($value) ? (int) $value : 0;
    }
}
