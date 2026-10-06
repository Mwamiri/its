<?php

namespace App\Controllers;

use App\Models\SettingModel;
use DateTimeImmutable;
use JsonException;

class ItsReportBuilder extends ItsBase
{
    private const REPORT_MODULES = [
        'tickets' => [
            'label' => 'Tickets',
            'date' => 'tickets.created_at',
            'status' => 'tickets.status',
            'fields' => [
                'ticket_number' => ['tickets.ticket_number', 'Ticket number'],
                'subject' => ['tickets.subject', 'Subject'],
                'client' => ['clients.name', 'Client'],
                'priority' => ['tickets.priority', 'Priority'],
                'status' => ['tickets.status', 'Status'],
                'visit_date' => ['tickets.visit_date', 'Visit date'],
                'created_at' => ['tickets.created_at', 'Created'],
            ],
        ],
        'tasks' => [
            'label' => 'Tasks',
            'date' => 'tasks.created_at',
            'status' => 'tasks.status',
            'fields' => [
                'ticket_number' => ['tickets.ticket_number', 'Ticket number'],
                'client' => ['clients.name', 'Client'],
                'item' => ['tasks.item', 'Item'],
                'status' => ['tasks.status', 'Status'],
                'scope' => ['tasks.scope', 'Scope'],
                'parts_cost' => ['tasks.parts_cost', 'Parts cost'],
                'labor_cost' => ['tasks.labor_cost', 'Labor cost'],
                'created_at' => ['tasks.created_at', 'Created'],
            ],
        ],
        'clients' => [
            'label' => 'Clients',
            'date' => 'clients.created_at',
            'status' => null,
            'fields' => [
                'name' => ['clients.name', 'Name'],
                'contact_person' => ['clients.contact_person', 'Contact person'],
                'email' => ['clients.email', 'Email'],
                'phone' => ['clients.phone', 'Phone'],
                'created_at' => ['clients.created_at', 'Created'],
            ],
        ],
        'assets' => [
            'label' => 'Assets',
            'date' => 'assets.created_at',
            'status' => 'assets.status',
            'fields' => [
                'client' => ['clients.name', 'Client'],
                'name' => ['assets.name', 'Asset'],
                'type' => ['assets.type', 'Type'],
                'brand' => ['assets.brand', 'Brand'],
                'model' => ['assets.model', 'Model'],
                'serial_number' => ['assets.serial_number', 'Serial number'],
                'status' => ['assets.status', 'Status'],
                'location' => ['assets.location', 'Location'],
                'created_at' => ['assets.created_at', 'Created'],
            ],
        ],
        'quotes' => [
            'label' => 'Quotes',
            'date' => 'quotes.created_at',
            'status' => 'quotes.status',
            'fields' => [
                'quote_number' => ['quotes.quote_number', 'Quote number'],
                'client' => ['clients.name', 'Client'],
                'subject' => ['quotes.subject', 'Subject'],
                'status' => ['quotes.status', 'Status'],
                'total' => ['quotes.total', 'Total'],
                'approved_at' => ['quotes.approved_at', 'Approved'],
                'created_at' => ['quotes.created_at', 'Created'],
            ],
        ],
    ];

    public function index()
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }

        $config = $this->normalizeConfig([
            'module' => $this->request->getGet('module'),
            'columns' => $this->request->getGet('columns'),
            'from' => $this->request->getGet('from'),
            'to' => $this->request->getGet('to'),
            'status' => $this->request->getGet('status'),
            'group' => $this->request->getGet('group'),
            'sort' => $this->request->getGet('sort'),
            'direction' => $this->request->getGet('direction'),
        ]);
        $errors = [];
        $rows = [];
        if ($config === null) {
            $errors[] = 'Choose a valid report module, columns, date range, group, and sort order.';
            $config = $this->defaultConfig();
        } elseif (($config['from'] !== '' && !$this->validDate($config['from']))
            || ($config['to'] !== '' && !$this->validDate($config['to']))) {
            $errors[] = 'Use valid calendar dates for the reporting range.';
        } elseif ($config['from'] !== '' && $config['to'] !== '' && $config['from'] > $config['to']) {
            $errors[] = 'The start date must be on or before the end date.';
        } else {
            $rows = $this->runReport($config);
            $format = $this->request->getGet('export');
            if (in_array($format, ['csv', 'json', 'gzip'], true)) {
                return $this->export($rows, $config, $format);
            }
        }

        return view('itsupport/report_builder', [
            'title' => 'Report builder',
            'modules' => self::REPORT_MODULES,
            'config' => $config,
            'rows' => $rows,
            'errors' => $errors,
            'savedReports' => $this->savedReports(),
        ]);
    }

    public function save()
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }
        $name = trim((string) $this->request->getPost('name'));
        $config = $this->normalizeConfig([
            'module' => $this->request->getPost('module'),
            'columns' => $this->request->getPost('columns'),
            'from' => $this->request->getPost('from'),
            'to' => $this->request->getPost('to'),
            'status' => $this->request->getPost('status'),
            'group' => $this->request->getPost('group'),
            'sort' => $this->request->getPost('sort'),
            'direction' => $this->request->getPost('direction'),
        ]);
        if ($name === '' || mb_strlen($name) > 100 || $config === null) {
            return redirect()->to(base_url('its-report-builder'))
                ->with('err', 'Enter a report name and choose valid report settings before saving.');
        }
        if (($config['from'] !== '' && !$this->validDate($config['from']))
            || ($config['to'] !== '' && !$this->validDate($config['to']))
            || ($config['from'] !== '' && $config['to'] !== '' && $config['from'] > $config['to'])) {
            return redirect()->to(base_url('its-report-builder'))
                ->with('err', 'Choose a valid date range before saving this report.');
        }

        $reports = $this->savedReports();
        $reports[] = ['id' => bin2hex(random_bytes(8)), 'name' => $name, 'config' => $config];
        $reports = array_slice($reports, -50);
        SettingModel::put('saved_report_templates', json_encode($reports, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        $this->audit('report_template_saved', 'reports', $name);
        return redirect()->to(base_url('its-report-builder'))->with('ok', 'Report template saved.');
    }

    public function load(string $id)
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }
        foreach ($this->savedReports() as $report) {
            if (hash_equals($report['id'], $id)) {
                return redirect()->to(base_url('its-report-builder?' . http_build_query($report['config'])));
            }
        }
        return redirect()->to(base_url('its-report-builder'))->with('err', 'That saved report no longer exists.');
    }

    public function delete(string $id)
    {
        if ($response = $this->needAdmin()) {
            return $response;
        }
        $reports = array_values(array_filter(
            $this->savedReports(),
            static fn(array $report): bool => !hash_equals($report['id'], $id),
        ));
        SettingModel::put('saved_report_templates', json_encode($reports, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        $this->audit('report_template_deleted', 'reports', $id);
        return redirect()->to(base_url('its-report-builder'))->with('ok', 'Saved report deleted.');
    }

    private function defaultConfig(): array
    {
        return ['module' => 'tickets', 'columns' => ['ticket_number', 'subject', 'client', 'status'], 'from' => '', 'to' => '', 'status' => '', 'group' => '', 'sort' => 'created_at', 'direction' => 'desc'];
    }

    private function normalizeConfig(array $raw): ?array
    {
        $module = $raw['module'] ?? 'tickets';
        if (!is_string($module) || !isset(self::REPORT_MODULES[$module])) {
            return null;
        }
        $fields = self::REPORT_MODULES[$module]['fields'];
        $columns = $raw['columns'] ?? [];
        if (!is_array($columns)) {
            return null;
        }
        $columns = array_values(array_unique(array_filter($columns, static fn($column): bool => is_string($column) && isset($fields[$column]))));
        if (count($columns) > 12) {
            return null;
        }
        if (!$columns) {
            $columns = array_slice(array_keys($fields), 0, min(4, count($fields)));
        }
        $from = $raw['from'] ?? '';
        $to = $raw['to'] ?? '';
        $status = $raw['status'] ?? '';
        $group = $raw['group'] ?? '';
        $sort = $raw['sort'] ?? '';
        $direction = strtolower((string) ($raw['direction'] ?? 'desc'));
        if (!is_string($from) || !is_string($to) || !is_string($status) || !is_string($group) || !is_string($sort)
            || ($status !== '' && (!self::REPORT_MODULES[$module]['status'] || !in_array($status, ['new','in_progress','waiting_parts','waiting_approval','completed','closed','draft','sent','approved','rejected','active','inactive','maintenance'], true)))
            || ($group !== '' && !isset($fields[$group]))
            || ($sort !== '' && !isset($fields[$sort]))
            || !in_array($direction, ['asc', 'desc'], true)) {
            return null;
        }
        return compact('module', 'columns', 'from', 'to', 'status', 'group', 'sort', 'direction');
    }

    private function runReport(array $config): array
    {
        $module = $config['module'];
        $definition = self::REPORT_MODULES[$module];
        $table = $module;
        $query = $this->db->table($table);
        if ($module === 'tickets' || $module === 'assets' || $module === 'quotes') {
            $query->join('clients', 'clients.id = ' . $table . '.client_id', 'left');
        } elseif ($module === 'tasks') {
            $query->join('tickets', 'tickets.id = tasks.ticket_id', 'left')
                ->join('clients', 'clients.id = tickets.client_id', 'left');
        }

        $fields = $definition['fields'];
        if ($config['group'] !== '') {
            $groupKey = $config['group'];
            $query->select($fields[$groupKey][0] . ' AS group_value', false)
                ->select('COUNT(*) AS records')
                ->groupBy($fields[$groupKey][0]);
            $query->orderBy('records', 'DESC');
        } else {
            foreach ($config['columns'] as $key) {
                $query->select($fields[$key][0] . ' AS `' . $key . '`', false);
            }
            if ($config['sort'] !== '') {
                $query->orderBy($fields[$config['sort']][0], $config['direction']);
            } else {
                $query->orderBy($definition['date'], 'DESC');
            }
        }
        if ($config['from'] !== '') {
            $query->where($definition['date'] . ' >=', $config['from'] . ' 00:00:00');
        }
        if ($config['to'] !== '') {
            $query->where($definition['date'] . ' <', (new DateTimeImmutable($config['to']))->modify('+1 day')->format('Y-m-d') . ' 00:00:00');
        }
        if ($config['status'] !== '' && $definition['status']) {
            $query->where($definition['status'], $config['status']);
        }
        return $query->limit(5000)->get()->getResultArray();
    }

    private function export(array $rows, array $config, string $format)
    {
        $stamp = date('Ymd-His');
        if ($format === 'json' || $format === 'gzip') {
            try {
                $payload = json_encode(['module' => $config['module'], 'generated_at' => date(DATE_ATOM), 'rows' => $rows], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } catch (JsonException $exception) {
                log_message('error', 'Report JSON encoding failed: {message}', ['message' => $exception->getMessage()]);
                return redirect()->to(base_url('its-report-builder'))->with('err', 'The report export could not be encoded.');
            }
            if ($format === 'json') {
                return $this->response->download('it-support-report-' . $stamp . '.json', $payload)
                    ->setContentType('application/json; charset=UTF-8')
                    ->setHeader('Cache-Control', 'no-store, private');
            }
            $compressed = gzencode($payload, 9);
            if ($compressed === false) {
                log_message('error', 'Unable to compress report JSON export.');
                return redirect()->to(base_url('its-report-builder'))->with('err', 'The compressed report export could not be created.');
            }
            return $this->response->download('it-support-report-' . $stamp . '.json.gz', $compressed)
                ->setContentType('application/gzip')
                ->setHeader('Cache-Control', 'no-store, private');
        }

        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            log_message('error', 'Unable to create temporary CSV stream for report export.');
            return redirect()->to(base_url('its-report-builder'))->with('err', 'The CSV export could not be created.');
        }
        fputcsv($stream, $config['group'] !== '' ? ['Group', 'Records'] : array_map(static fn(string $key): string => self::REPORT_MODULES[$config['module']]['fields'][$key][1], $config['columns']), ',', '"', '');
        foreach ($rows as $row) {
            $values = $config['group'] !== '' ? [$row['group_value'] ?? '', (string) $row['records']] : array_values($row);
            fputcsv($stream, array_map($this->spreadsheetSafe(...), array_map(static fn($value): string => is_scalar($value) ? (string) $value : '', $values)), ',', '"', '');
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        if ($csv === false) {
            log_message('error', 'Unable to read temporary CSV stream for report export.');
            return redirect()->to(base_url('its-report-builder'))->with('err', 'The CSV export could not be read.');
        }
        return $this->response->download('it-support-report-' . $stamp . '.csv', $csv)
            ->setContentType('text/csv; charset=UTF-8')
            ->setHeader('Cache-Control', 'no-store, private');
    }

    private function spreadsheetSafe(string $value): string
    {
        return preg_match('/^[\s]*[=+\-@]/', $value) ? "'" . $value : $value;
    }

    private function validDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function savedReports(): array
    {
        try {
            $reports = json_decode((string) SettingModel::get('saved_report_templates', '[]'), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            log_message('error', 'Saved report templates are not valid JSON: {message}', ['message' => $exception->getMessage()]);
            return [];
        }
        return is_array($reports) ? array_values(array_filter($reports, static fn($report): bool => is_array($report)
            && is_string($report['id'] ?? null) && is_string($report['name'] ?? null) && is_array($report['config'] ?? null))) : [];
    }
}
