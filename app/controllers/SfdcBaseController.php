<?php

class SfdcBaseController
{
    protected $model;
    protected $conn;

    public function __construct($db = null)
    {
        if ($db instanceof PDO) {
            $this->conn = $db;
            return;
        }

        require_once __DIR__ . '/../../config/database.php';

        $database = new Database();
        $this->conn = $database->getConnection();
    }

    protected function requireMethod($method)
    {
        $currentMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (strtoupper($currentMethod) !== strtoupper($method)) {
            $this->jsonError('Method not allowed', 405);
        }
    }

    protected function parseFilters(array $source = null)
    {
        $input = $source ?? $_GET;

        $filters = [
            'team' => isset($input['team']) ? trim($input['team']) : '',
            'agent' => isset($input['agent']) ? trim($input['agent']) : '',
            'fiscal_period' => isset($input['fiscal_period']) ? trim($input['fiscal_period']) : '',
            'month' => isset($input['month']) ? (int)$input['month'] : '',
            'quarter' => isset($input['quarter']) ? (int)$input['quarter'] : '',
            'year' => isset($input['year']) ? (int)$input['year'] : '',
            'real_flag' => isset($input['real_flag']) ? trim((string)$input['real_flag']) : '',
            'start' => isset($input['start']) ? max(0, (int)$input['start']) : 0,
            'length' => isset($input['length']) ? max(10, (int)$input['length']) : 25,
            'draw' => isset($input['draw']) ? (int)$input['draw'] : 1,
            'search' => '',
            'order_column' => '',
            'order_dir' => 'asc',
        ];

        if (isset($input['search']) && is_array($input['search'])) {
            $filters['search'] = trim($input['search']['value'] ?? '');
        } elseif (isset($input['search'])) {
            $filters['search'] = trim((string)$input['search']);
        }

        if (isset($input['order'][0]['dir'])) {
            $dir = strtolower((string)$input['order'][0]['dir']);
            $filters['order_dir'] = in_array($dir, ['asc', 'desc'], true) ? $dir : 'asc';
        }

        if (isset($input['order'][0]['column'])) {
            $filters['order_column'] = (string)$input['order'][0]['column'];
        }

        return $filters;
    }

    protected function jsonSuccess($data = [], $statusCode = 200, array $extra = [])
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        echo json_encode(array_merge([
            'success' => true,
            'data' => $data
        ], $extra));

        exit;
    }

    protected function jsonError($message = 'Unknown error', $statusCode = 400, array $extra = [])
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        echo json_encode(array_merge([
            'success' => false,
            'error' => $message
        ], $extra));

        exit;
    }

    protected function getConnection()
    {
        return $this->conn;
    }
}
