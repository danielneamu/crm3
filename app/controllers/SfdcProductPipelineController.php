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
     * Override parseFilters to include product_family and stage
     * These are product-pipeline-specific filters not in base class
     * 
     * @param array $source Source array (defaults to $_GET)
     * @return array Parsed filters including product_family and stage
     */
    protected function parseFilters(array $source = null)
    {
        $filters = parent::parseFilters($source);
        $input = $source ?? $_GET;

        // Add product pipeline specific filters
        $filters['product_family'] = isset($input['product_family']) ? trim($input['product_family']) : '';
        $filters['stage'] = isset($input['stage']) ? trim($input['stage']) : '';

        return $filters;
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
     * Get unique stages (for filter dropdown)
     * 
     * GET /api/sfdc_product_pipeline.php?action=get_stages
     */
    public function getStages()
    {
        $this->requireMethod('GET');

        try {
            $stages = $this->model->getStages();
            $this->jsonSuccess($stages);
        } catch (Exception $e) {
            $this->jsonError('Failed to load stages: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get dashboard data for a fiscal year
     * 
     * GET /api/sfdc_product_pipeline.php?action=get_dashboard_data&fiscal_year=2026
     */
    /**
     * Get dashboard data for a fiscal year
     *
     * GET /api/sfdc_product_pipeline.php?action=get_dashboard_data&fiscal_year=2026
     * GET /api/sfdc_product_pipeline.php?action=get_dashboard_data&fiscal_year=2026&product_families=A,B&product_names=X,Y
     */
    public function getDashboardData()
    {
        $this->requireMethod('GET');

        try {
            $fiscalYear = isset($_GET['fiscal_year'])
                ? (int)$_GET['fiscal_year']
                : SfdcProductPipelineModel::getCurrentFiscalYear();

            $productFamilies = [];
            if (isset($_GET['product_families']) && trim((string)$_GET['product_families']) !== '') {
                $productFamilies = array_values(array_filter(array_map('trim', explode(',', (string)$_GET['product_families']))));
            }

            $productNames = [];
            if (isset($_GET['product_names']) && trim((string)$_GET['product_names']) !== '') {
                $productNames = array_values(array_filter(array_map('trim', explode(',', (string)$_GET['product_names']))));
            }

            $filters = [
                'fiscal_year' => $fiscalYear,
                'product_families' => $productFamilies,
                'product_names' => $productNames,
            ];

            // 1) Filter options for current fiscal year
            $filterOptions = $this->model->loadFilterOptions($fiscalYear);

            // 2) Raw filtered rows
            $rawRows = $this->model->getFilteredRawRows($filters);

            // 3) Centralized cleaning layer
            $cleanedRows = $this->model->cleanRowArrovValues($rawRows);

            // 4) Deduplicated opportunity dataset
            $uniqueOppRows = $this->model->buildUniqueOpportunityRows($cleanedRows);

            // 5) Dashboard payload
            $payload = [
                'filters' => $filterOptions,
                'cards' => $this->model->getKpiCards($uniqueOppRows),
                'charts' => [
                    'stage' => $this->model->getStageChart($uniqueOppRows),
                    'team' => $this->model->getTeamChart($uniqueOppRows),
                    'ageAov' => $this->model->getAgeScatter($uniqueOppRows),
                    'probability' => $this->model->getProbabilityChart($uniqueOppRows),
                    'productFamilyMix' => $this->model->getProductFamilyMixChart($cleanedRows),
                    'closeTimeline' => $this->model->getCloseTimeline($uniqueOppRows),
                    'monthlyTeamFiscal' => $this->model->getMonthlyTeamFiscalChart($uniqueOppRows),
                ],
            ];

            $this->jsonSuccess($payload);
        } catch (Exception $e) {
            $this->jsonError('Failed to load dashboard data: ' . $e->getMessage(), 500);
        }
    }
}
