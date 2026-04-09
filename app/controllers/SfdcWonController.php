<?php

require_once __DIR__ . '/SfdcBaseController.php';
require_once __DIR__ . '/../models/SfdcWonModel.php';

class SfdcWonController extends SfdcBaseController
{
    public function __construct($db = null)
    {
        parent::__construct($db);
        $this->model = new SfdcWonModel($this->conn);
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
            $this->jsonError('Failed to load won data: ' . $e->getMessage(), 500);
        }
    }

    public function getWonById($id)
    {
        $this->requireMethod('GET');

        try {
            $row = $this->model->getById($id);

            if (!$row) {
                $this->jsonError('Won record not found', 404);
            }

            $this->jsonSuccess($row);
        } catch (Exception $e) {
            $this->jsonError('Failed to load won record: ' . $e->getMessage(), 500);
        }
    }

    public function updateWonField($data = null)
    {
        $this->requireMethod('POST');

        try {
            $payload = $data ?? $_POST;

            $id = isset($payload['id']) ? (int)$payload['id'] : 0;
            $field = isset($payload['field']) ? trim($payload['field']) : '';
            $value = $payload['value'] ?? null;

            if ($id <= 0) {
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
}
