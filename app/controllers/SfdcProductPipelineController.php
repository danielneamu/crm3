<?php

require_once __DIR__ . '/SfdcBaseController.php';
require_once __DIR__ . '/../models/SfdcProductPipelineModel.php';

class SfdcProductPipelineController extends SfdcBaseController
{
    public function __construct($db = null)
    {
        parent::__construct($db);
        $this->model = new SfdcProductPipelineModel($this->conn);
    }

    /**
     * Get all product pipeline rows (with filters)
     * 
     * GET /api/sfdc_product_pipeline.php?action=get_products&team=...&stage=...
     */
    public function getProducts()
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
            $this->jsonError('Failed to load product pipeline data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single product row by ID
     * 
     * GET /api/sfdc_product_pipeline.php?action=get_product_by_id&id=123
     */
    public function getProductById($id)
    {
        $this->requireMethod('GET');

        try {
            $row = $this->model->getById($id);

            if (!$row) {
                $this->jsonError('Product pipeline record not found', 404);
            }

            $this->jsonSuccess($row);
        } catch (Exception $e) {
            $this->jsonError('Failed to load product record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get unique product families (for filter dropdown)
     * 
     * GET /api/sfdc_product_pipeline.php?action=get_product_families
     */
    public function getProductFamilies()
    {
        $this->requireMethod('GET');

        try {
            $families = $this->model->getProductFamilies();
            $this->jsonSuccess($families);
        } catch (Exception $e) {
            $this->jsonError('Failed to load product families: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get dashboard data for a fiscal year
     * 
     * GET /api/sfdc_product_pipeline.php?action=get_dashboard_data&fiscal_year=2026
     */
    public function getDashboardData()
    {
        $this->requireMethod('GET');

        try {
            $fiscalYear = isset($_GET['fiscal_year'])
                ? (int)$_GET['fiscal_year']
                : SfdcProductPipelineModel::getCurrentFiscalYear();

            $data = $this->model->getDashboardData($fiscalYear);

            $this->jsonSuccess($data);
        } catch (Exception $e) {
            $this->jsonError('Failed to load dashboard data: ' . $e->getMessage(), 500);
        }
    }
}
