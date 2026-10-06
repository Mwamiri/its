<?php

namespace App\Controllers;

use App\Models\CustomFormModel;
use App\Models\FormSubmissionModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use DateTimeImmutable;
use JsonException;

class ItsForms extends ItsBase
{
    private const FIELD_TYPES = ['text', 'textarea', 'number', 'email', 'phone', 'date', 'url', 'select', 'radio', 'checkbox', 'file'];
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
        if ($response = $this->needLogin()) {
            return $response;
        }

        $form = (new CustomFormModel())->find($id);
        if (!$form || (!(int) $form['active'] && ($this->user()['role'] ?? '') !== 'admin')) {
            return redirect()->to(base_url('its-forms'))->with('err', 'That form is unavailable.');
        }

        $fields = $this->decodeFields($form['fields_json']);
        if (!$fields) {
            log_message('error', 'Form {id} has an invalid field definition.', ['id' => $id]);
            return redirect()->to(base_url('its-forms'))->with('err', 'This form is unavailable because its definition is invalid.');
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
                    $submissionId = (new FormSubmissionModel())->insert([
                        'form_id' => $id,
                        'submitted_by' => (int) $this->user()['id'],
                        'answers_json' => $answersJson,
                        'submitted_at' => date('Y-m-d H:i:s'),
                    ]);
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
                    return redirect()->to(base_url('its-forms'))->with('ok', 'Your response has been submitted.');
            }

            $this->response->setHeader('Cache-Control', 'no-store, private');
        }

        return view('itsupport/form_submit', [
            'title' => $form['title'],
            'form' => $form,
            'fields' => $fields,
            'errors' => $errors,
            'submitted' => $submitted,
        ]);
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
            return $this->exportCsv($rows);
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
        foreach (array_slice($input, 0, 30) as $rawField) {
            if (!is_array($rawField)) {
                continue;
            }
            $keyValue = $rawField['key'] ?? '';
            $labelValue = $rawField['label'] ?? '';
            $typeValue = $rawField['type'] ?? '';
            $optionValue = $rawField['options'] ?? '';
            if (!is_string($keyValue) || !is_string($labelValue) || !is_string($typeValue) || !is_string($optionValue)) {
                return null;
            }
            $key = strtolower(trim($keyValue));
            $label = mb_substr(trim($labelValue), 0, 120);
            $type = trim($typeValue);
            if ($key === '' && $label === '' && $type === '') {
                continue;
            }
            if (!preg_match('/^[a-z][a-z0-9_]{0,39}$/', $key) || $label === '' || !in_array($type, self::FIELD_TYPES, true) || isset($keys[$key])) {
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
            $fields[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'required' => ($rawField['required'] ?? '') === '1',
                'options' => $options,
            ];
            $keys[$key] = true;
        }

        return $fields && count($input) <= 30 ? $fields : null;
    }

    private function decodeFields(string $json): array
    {
        try {
            $fields = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return [];
        }
        if (!is_array($fields) || count($fields) > 30) {
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
        return $fields;
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

    private function exportCsv(array $rows)
    {
        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            log_message('error', 'Unable to create temporary CSV stream for form reports.');
            return redirect()->to(base_url('its-form-reports'))->with('err', 'The CSV export could not be created.');
        }

        fputcsv($stream, ['Submission ID', 'Form', 'Submitted At', 'Submitted By', 'Answers (JSON)'], ',', '"', '');
        foreach ($rows as $row) {
            $values = [
                (string) $row['id'],
                (string) $row['form_title'],
                (string) $row['submitted_at'],
                (string) ($row['submitter_name'] ?? 'Unknown'),
                (string) $row['answers_json'],
            ];
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
