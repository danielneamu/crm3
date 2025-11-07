<?php

/**
 * Report Controller
 * Business logic for report generation and filtering
 * Orchestrates between API requests and Report model
 */

class ReportController
{
    private $report;

    public function __construct($db)
    {
        require_once __DIR__ . '/../models/Report.php';
        $this->report = new Report($db);
    }

    /**
     * Get Agent Performance Report
     * Validates filters and returns formatted agent metrics
     * 
     * @param array $filters Raw filters from request
     * @return array Formatted response
     */
    public function getAgentPerformance($filters = [])
    {
        try {
            // Validate and normalize filters
            $validatedFilters = $this->validateFilters($filters, ['team', 'dateRange', 'status']);

            // Execute query
            $data = $this->report->getAgentPerformance($validatedFilters);

            // Format currency fields
            $data = $this->formatCurrencyFields($data, ['total_tcv', 'signed_tcv', 'avg_project_value']);

            return [
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ];
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get Projects Since April 1st Report
     * Validates fiscal year and other filters, returns project list
     * 
     * @param array $filters Raw filters from request (must include fiscalYear)
     * @return array Formatted response
     */
    public function getProjectsSinceApril($filters = [])
    {
        try {
            // Validate fiscal year filter
            if (empty($filters['fiscalYear'])) {
                throw new Exception('Fiscal year filter required (current or previous)');
            }

            if (!in_array($filters['fiscalYear'], ['current', 'previous'])) {
                throw new Exception('Invalid fiscal year. Must be "current" or "previous"');
            }

            // Validate and normalize other filters
            $validatedFilters = $this->validateFilters($filters, ['team', 'status', 'projectType']);
            $validatedFilters['fiscalYear'] = $filters['fiscalYear'];

            // Execute query
            $data = $this->report->getProjectsSinceApril($validatedFilters);

            // Format currency fields
            $data = $this->formatCurrencyFields($data, ['aov']);

            return [
                'success' => true,
                'data' => $data,
                'count' => count($data),
                'fiscalYear' => $filters['fiscalYear']
            ];
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get Project Timeline Report
     * Validates filters and returns detailed project list with status history
     * 
     * @param array $filters Raw filters from request
     * @return array Formatted response
     */
    public function getProjectTimeline($filters = [])
    {
        try {
            // Validate and normalize filters
            $validatedFilters = $this->validateFilters($filters, ['dateRange', 'team', 'status']);

            // Execute query
            $data = $this->report->getProjectTimeline($validatedFilters);

            // Format currency fields
            $data = $this->formatCurrencyFields($data, ['tcv_project', 'aov']);

            return [
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ];
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get Filter Options for Dropdowns
     * Returns available teams, statuses, project types
     * 
     * @return array Filter options
     */
    public function getFilterOptions()
    {
        try {
            $options = $this->report->getFilterOptions();

            return [
                'success' => true,
                'data' => $options
            ];
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Validate and normalize filter input
     * Ensures only allowed filters are used
     * 
     * @param array $filters Raw input filters
     * @param array $allowed Allowed filter types
     * @return array Validated filters
     */
    private function validateFilters($filters, $allowed = [])
    {
        $validated = [];

        // Team filter
        if (in_array('team', $allowed) && !empty($filters['team'])) {
            $team = is_array($filters['team']) ? $filters['team'] : explode(',', $filters['team']);
            $team = array_map('trim', $team);
            $team = array_filter($team);
            if (!empty($team)) {
                $validated['team'] = $team;
            }
        }

        // Date range filter
        if (in_array('dateRange', $allowed)) {
            $dateFrom = !empty($filters['dateFrom']) ? trim($filters['dateFrom']) : null;
            $dateTo = !empty($filters['dateTo']) ? trim($filters['dateTo']) : null;

            if ($dateFrom && $dateTo) {
                // Validate date format (YYYY-MM-DD)
                if (!$this->isValidDate($dateFrom) || !$this->isValidDate($dateTo)) {
                    throw new Exception('Invalid date format. Use YYYY-MM-DD');
                }

                // Ensure dateFrom <= dateTo
                if ($dateFrom > $dateTo) {
                    throw new Exception('Start date cannot be after end date');
                }

                $validated['dateFrom'] = $dateFrom;
                $validated['dateTo'] = $dateTo;
            }
        }

        // Status filter
        if (in_array('status', $allowed) && !empty($filters['status'])) {
            $allowedStatuses = ['New', 'Qualifying', 'Design', 'Pending', 'Contract Signed', 'Completed', 'Cancelled', 'Offer Refused', 'No Solution'];

            $status = is_array($filters['status']) ? $filters['status'] : explode(',', $filters['status']);
            $status = array_map('trim', $status);
            $status = array_intersect($status, $allowedStatuses); // Whitelist
            $status = array_filter($status);

            if (!empty($status)) {
                $validated['status'] = array_values($status); // Re-index array
            }
        }

        // Project Type filter
        if (in_array('projectType', $allowed) && !empty($filters['projectType'])) {
            $allowedTypes = ['ICT/IOT', 'Fixed', 'Mobile', 'Other'];

            $type = is_array($filters['projectType']) ? $filters['projectType'] : explode(',', $filters['projectType']);
            $type = array_map('trim', $type);
            $type = array_intersect($type, $allowedTypes); // Whitelist
            $type = array_filter($type);

            if (!empty($type)) {
                $validated['projectType'] = array_values($type); // Re-index array
            }
        }

        return $validated;
    }

    /**
     * Validate date format (YYYY-MM-DD)
     * 
     * @param string $date Date string
     * @return bool Valid date
     */
    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Format currency fields to 2 decimal places
     * 
     * @param array $data Array of records
     * @param array $fields Field names to format
     * @return array Formatted data
     */
    private function formatCurrencyFields($data, $fields = [])
    {
        return array_map(function ($row) use ($fields) {
            foreach ($fields as $field) {
                if (isset($row[$field])) {
                    $row[$field] = round($row[$field], 2);
                }
            }
            return $row;
        }, $data);
    }

    /**
     * Standard error response format
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @return array Error response
     */
    private function errorResponse($message, $code = 400)
    {
        http_response_code($code);
        return [
            'success' => false,
            'error' => $message
        ];
    }

    /**
     * Export report data to CSV
     * 
     * @param string $reportType Report type (agent_performance, projects_since_april, project_timeline)
     * @param array $filters Filters to apply
     * @param string $filename Output filename
     * @return void Outputs CSV file
     */
    public function exportToCSV($reportType, $filters = [], $filename = '')
    {
        // Clear output buffer to prevent headers from being sent
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            // Get report data based on type
            switch ($reportType) {
                case 'agent_performance':
                    $result = $this->getAgentPerformance($filters);
                    $filename = $filename ?: 'agent_performance_' . date('Y-m-d');
                    break;

                case 'projects_since_april':
                    $result = $this->getProjectsSinceApril($filters);
                    $filename = $filename ?: 'projects_since_april_' . date('Y-m-d');
                    break;

                case 'project_timeline':
                    $result = $this->getProjectTimeline($filters);
                    $filename = $filename ?: 'project_timeline_' . date('Y-m-d');
                    break;

                default:
                    throw new Exception('Invalid report type');
            }

            if (!$result['success']) {
                throw new Exception($result['error']);
            }

            $data = $result['data'];

            if (empty($data)) {
                throw new Exception('No data to export');
            }

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"$filename.csv\"");

            // Open output stream
            $fp = fopen('php://output', 'w');

            // Write UTF-8 BOM for Excel compatibility
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write header row with explicit escape character
            fputcsv($fp, array_keys($data[0]), ',', '"', '"');

            // Write data rows with explicit escape character
            foreach ($data as $row) {
                fputcsv($fp, $row, ',', '"', '"');
            }

            fclose($fp);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Export failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}
