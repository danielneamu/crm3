<?php

require_once __DIR__ . '/SfdcBaseController.php';
require_once __DIR__ . '/../models/SfdcPipelineModel.php';

class SfdcPipelineController extends SfdcBaseController
{
    public function __construct($db = null)
    {
        parent::__construct($db);
        $this->model = new SfdcPipelineModel($this->conn);
    }

    public function getWon()
    {
        $this->requireMethod('GET');

        try {
            $filters = $this->parseFilters($_GET);
            $rows = $this->model->getAll($filters);

            $this->jsonSuccess($rows, 200, [
                'recordsTotal' => count($rows),
                'recordsFiltered' => count($rows),
                'draw' => (int)($filters['draw'] ?? 1)
            ]);
        } catch (Exception $e) {
            $this->jsonError('Failed to load pipeline data: ' . $e->getMessage(), 500);
        }
    }

    public function getWonById($id)
    {
        $this->requireMethod('GET');

        try {
            $row = $this->model->getById($id);

            if (!$row) {
                $this->jsonError('Pipeline record not found', 404);
            }

            $this->jsonSuccess($row);
        } catch (Exception $e) {
            $this->jsonError('Failed to load pipeline record: ' . $e->getMessage(), 500);
        }
    }

    public function updateWonField($data = null)
    {
        $this->requireMethod('POST');

        try {
            $payload = $data ?? $_POST;

            $id = isset($payload['id']) ? trim($payload['id']) : '';
            $field = isset($payload['field']) ? trim($payload['field']) : '';
            $value = $payload['value'] ?? null;

            if ($id === '') {
                $this->jsonError('Missing or invalid id', 400);
            }

            if ($field === '') {
                $this->jsonError('Missing field name', 400);
            }

            if ($field === 'Type') {
                $value = is_string($value) ? trim($value) : $value;
                if ($value === '') {
                    $value = null;
                }
            }

            if ($field === 'Real_Flag') {
                // Convert string boolean to int
                if ($value === 'Yes' || $value == 1 || $value === 1) {
                    $value = 1;
                } else {
                    $value = 0;
                }
            }

            $this->model->updateEditableField($id, $field, $value);
            $updatedRow = $this->model->getById($id);

            $this->jsonSuccess([
                'message' => 'Field updated successfully',
                'row' => $updatedRow
            ]);
        } catch (InvalidArgumentException $e) {
            $this->jsonError($e->getMessage(), 400);
        } catch (Exception $e) {
            $this->jsonError('Failed to update field: ' . $e->getMessage(), 500);
        }
    }

    public function getTypeOptions()
    {
        $this->requireMethod('GET');

        try {
            $this->jsonSuccess($this->model->getTypeOptions());
        } catch (Exception $e) {
            $this->jsonError('Failed to load type options: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get dashboard aggregated data for a fiscal year
     * Called via: GET /api/sfdc_pipeline.php?action=get_dashboard_data&fiscal_year=2026
     */
    public function getDashboardData()
    {
        $this->requireMethod('GET');

        try {
            $fiscalYear = isset($_GET['fiscal_year'])
                ? (int)$_GET['fiscal_year']
                : SfdcPipelineDashboardModel::getCurrentFiscalYear();

            require_once __DIR__ . '/../models/SfdcPipelineDashboardModel.php';
            $dashboardModel = new SfdcPipelineDashboardModel($this->conn);

            $data = $dashboardModel->getDashboardData($fiscalYear);

            $this->jsonSuccess($data);
        } catch (Exception $e) {
            $this->jsonError('Failed to load dashboard data: ' . $e->getMessage(), 500);
        }
    }
}
