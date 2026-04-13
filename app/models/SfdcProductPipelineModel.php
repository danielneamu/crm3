<?php

require_once __DIR__ . '/SfdcBaseModel.php';

class SfdcProductPipelineModel extends SfdcBaseModel
{
    protected $table = 'sfdc_product_pipeline';

    /**
     * Override parseFilters to include product_family and stage
     * These are product-pipeline-specific filters
     */
    protected function parseFilters(array $filters = [])
    {
        // Get base filters from parent
        $baseFilters = parent::parseFilters($filters);

        // Add product pipeline specific filters
        $baseFilters['product_family'] = isset($filters['product_family']) ? trim($filters['product_family']) : '';
        $baseFilters['stage'] = isset($filters['stage']) ? trim($filters['stage']) : '';

        return $baseFilters;
    }

    /**
     * Get unique teams from product pipeline (overrides parent)
     * Queries sfdc_product_pipeline directly to show all teams with data
     * 
     * @return array List of Owner_Role values
     */
    public function getTeams($source = 'main')
    {
        $sql = "
            SELECT DISTINCT Owner_Role
            FROM {$this->table}
            WHERE Owner_Role IS NOT NULL
              AND TRIM(Owner_Role) <> ''
            ORDER BY Owner_Role ASC
        ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get unique agents from product pipeline (overrides parent)
     * Queries sfdc_product_pipeline directly to show all agents with data
     * 
     * @return array List of Opportunity_Owner values
     */
    public function getAgents($source = 'main')
    {
        $sql = "
            SELECT DISTINCT Opportunity_Owner
            FROM {$this->table}
            WHERE Opportunity_Owner IS NOT NULL
              AND TRIM(Opportunity_Owner) <> ''
            ORDER BY Opportunity_Owner ASC
        ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get unique stages from product pipeline
     * Returns all distinct Stage values for dropdown filter
     * 
     * @return array List of Stage values
     */
    public function getStages()
    {
        $sql = "
            SELECT DISTINCT Stage
            FROM {$this->table}
            WHERE Stage IS NOT NULL
              AND TRIM(Stage) <> ''
            ORDER BY Stage ASC
        ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get all product pipeline rows with filters applied
     * 
     * @param array $filters Team, Agent, Stage, Month, Year, etc.
     * @return array Rows with grouping/sorting metadata
     */
    public function getAll(array $filters = [])
    {
        $params = [];
        $where = $this->buildProductPipelineWhereClause($filters, $params);

        $sql = "
            SELECT
                Product_Pipeline_ID,
                Opportunity_Reference_ID,
                Opportunity_Owner,
                Owner_Role,
                Account_Name,
                Opportunity_Name,
                Fiscal_VAT_Number,
                Fiscal_Period,
                Stage,
                Probability_Percent,
                Age,
                Created_Date,
                Close_Date,
                Last_Modified_Date,
                Last_Stage_Change_Date,
                Contract_Term_Months,
                Description,
                Annual_Order_Value_Multi,
                Product_Family,
                Product_Name,
                Product_Code,
                Product_Annual_Recurring_Order_Value,
                Link
            FROM {$this->table}
            {$where}
            ORDER BY Close_Date DESC, Product_Pipeline_ID DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add metadata for grouping and sorting
        foreach ($rows as &$row) {
            $closeDate = $row['Close_Date'] ?? null;
            $ownerRole = trim((string)($row['Owner_Role'] ?? ''));
            $productFamily = trim((string)($row['Product_Family'] ?? ''));

            if (!empty($closeDate) && $closeDate !== '0000-00-00') {
                $ts = strtotime($closeDate);
                $monthNum = (int)date('n', $ts);
                $yearNum = (int)date('Y', $ts);

                $row['Group_Month_Label'] = date('F Y', $ts);
                $row['Group_Month_Sort'] = date('Y-m', $ts);

                // Fiscal quarter (Apr-Mar calendar)
                if ($monthNum >= 4 && $monthNum <= 6) {
                    $row['Group_Fiscal_Quarter'] = 'Q1';
                } elseif ($monthNum >= 7 && $monthNum <= 9) {
                    $row['Group_Fiscal_Quarter'] = 'Q2';
                } elseif ($monthNum >= 10 && $monthNum <= 12) {
                    $row['Group_Fiscal_Quarter'] = 'Q3';
                } else {
                    $row['Group_Fiscal_Quarter'] = 'Q4';
                }

                $row['Group_Fiscal_Year'] = ($monthNum >= 4) ? $yearNum : ($yearNum - 1);
                $row['Group_Fiscal_Label'] = $row['Group_Fiscal_Quarter'] . ' FY' . $row['Group_Fiscal_Year'];
            } else {
                $row['Group_Month_Label'] = 'No Close Date';
                $row['Group_Month_Sort'] = '0000-00';
                $row['Group_Fiscal_Quarter'] = '';
                $row['Group_Fiscal_Year'] = '';
                $row['Group_Fiscal_Label'] = 'No Fiscal Period';
            }

            $row['Group_Team_Label'] = $ownerRole !== '' ? $ownerRole : 'No Team';
            $row['Group_Product_Label'] = $productFamily !== '' ? $productFamily : 'Uncategorized';
        }

        unset($row);

        return $rows;
    }

    /**
     * Get single product pipeline row by ID
     * 
     * @param int $id Product_Pipeline_ID
     * @return array|false Row data or false if not found
     */
    public function getById($id)
    {
        $sql = "
            SELECT
                Product_Pipeline_ID,
                Opportunity_Reference_ID,
                Opportunity_Owner,
                Owner_Role,
                Account_Name,
                Opportunity_Name,
                Fiscal_VAT_Number,
                Fiscal_Period,
                Stage,
                Probability_Percent,
                Age,
                Created_Date,
                Close_Date,
                Last_Modified_Date,
                Last_Stage_Change_Date,
                Contract_Term_Months,
                Description,
                Annual_Order_Value_Multi,
                Product_Family,
                Product_Name,
                Product_Code,
                Product_Annual_Recurring_Order_Value,
                Link
            FROM {$this->table}
            WHERE Product_Pipeline_ID = :id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get unique product families
     * 
     * @return array List of product families
     */
    public function getProductFamilies()
    {
        $sql = "
            SELECT DISTINCT Product_Family
            FROM {$this->table}
            WHERE Product_Family IS NOT NULL
              AND TRIM(Product_Family) <> ''
            ORDER BY Product_Family ASC
        ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get dashboard data for a fiscal year
     * Aggregates ARROV by Product Family + Owner Role + Month
     * 
     * @param int $fiscalYear Fiscal year (e.g., 2026 = Apr 2025 - Mar 2026)
     * @return array Dashboard structure with teams, families, monthly aggregates, KPIs
     */
    public function getDashboardData($fiscalYear)
    {
        $range = $this->getFiscalYearRange($fiscalYear);
        $startDate = $range['start'];
        $endDate = $range['end'];

        // Query: aggregate by month, team, product family
        $sql = "
            SELECT 
                MONTH(Close_Date) as month_num,
                Owner_Role as team,
                COALESCE(Product_Family, 'Uncategorized') as product_family,
                SUM(CAST(Product_Annual_Recurring_Order_Value AS DECIMAL(15,2))) as total_arrov,
                COUNT(*) as deal_count
            FROM {$this->table}
            WHERE Close_Date >= :start_date
              AND Close_Date <= :end_date
            GROUP BY MONTH(Close_Date), Owner_Role, Product_Family
            ORDER BY MONTH(Close_Date), Owner_Role, Product_Family
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Extract unique teams and families, sort alphabetically
        $teams = array_unique(array_map(function ($row) {
            return $row['team'];
        }, $rows));
        sort($teams);

        $families = array_unique(array_map(function ($row) {
            return $row['product_family'];
        }, $rows));
        sort($families);

        // Initialize data structure: months 1-12, all teams, all families
        $months = range(1, 12);
        $data = [];

        // Add 'All' (aggregate across all families)
        $data['All'] = [
            'arrov' => [],
            'kpi' => ['total_arrov' => 0, 'deal_count' => 0, 'avg_arrov' => 0]
        ];

        // Add per-family buckets
        foreach ($families as $family) {
            $data[$family] = [
                'arrov' => [],
                'kpi' => ['total_arrov' => 0, 'deal_count' => 0, 'avg_arrov' => 0]
            ];
        }

        // Initialize arrays for each team with zero values
        foreach ($teams as $team) {
            $data['All']['arrov'][$team] = array_fill(0, 12, 0);

            foreach ($families as $family) {
                $data[$family]['arrov'][$team] = array_fill(0, 12, 0);
            }
        }

        // Populate data from query results
        foreach ($rows as $row) {
            $monthIdx = (int)$row['month_num'] - 1; // 0-indexed for array
            $team = $row['team'];
            $family = $row['product_family'];
            $arrov = (float)$row['total_arrov'];
            $deals = (int)$row['deal_count'];

            // Ensure team arrays exist
            if (!isset($data[$family]['arrov'][$team])) {
                $data[$family]['arrov'][$team] = array_fill(0, 12, 0);
            }

            // Update family-specific data
            $data[$family]['arrov'][$team][$monthIdx] = $arrov;

            // Accumulate family-specific KPIs
            $data[$family]['kpi']['total_arrov'] += $arrov;
            $data[$family]['kpi']['deal_count'] += $deals;

            // Also accumulate to 'All'
            $data['All']['arrov'][$team][$monthIdx] += $arrov;
            $data['All']['kpi']['total_arrov'] += $arrov;
            $data['All']['kpi']['deal_count'] += $deals;
        }

        // Calculate averages per family
        foreach (array_merge(['All'], $families) as $familyKey) {
            if (!isset($data[$familyKey])) {
                continue;
            }

            $kpi = &$data[$familyKey]['kpi'];

            // Count months with data (non-zero)
            $monthsWithData = 0;
            foreach ($data[$familyKey]['arrov'] as $teamData) {
                foreach ($teamData as $value) {
                    if ($value > 0) {
                        $monthsWithData++;
                        break;
                    }
                }
            }

            $kpi['avg_arrov'] = $monthsWithData > 0 ? round($kpi['total_arrov'] / $monthsWithData, 2) : 0;
        }

        return [
            'fiscal_year' => (int)$fiscalYear,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'teams' => $teams,
            'families' => $families,
            'months' => $months,
            'data' => $data
        ];
    }

    /**
     * Get fiscal year date range (April 1 – March 31)
     * 
     * @param int $fiscalYear Fiscal year number
     * @return array { 'start': '2025-04-01', 'end': '2026-03-31' }
     */
    public function getFiscalYearRange($fiscalYear)
    {
        $fy = (int)$fiscalYear;
        $startYear = $fy - 1;
        $endYear = $fy;

        return [
            'start' => "$startYear-04-01",
            'end' => "$endYear-03-31"
        ];
    }

    /**
     * Get current fiscal year
     * 
     * @return int Current fiscal year
     */
    public static function getCurrentFiscalYear()
    {
        $month = (int)date('n');
        $year = (int)date('Y');
        return $month >= 4 ? $year + 1 : $year;
    }

    /**
     * Build WHERE clause for product pipeline filtering
     * 
     * @param array $filters Team, Agent, Stage, Month, Quarter, Year, etc.
     * @param array $params Reference to bind parameters
     * @return string WHERE clause (empty string if no filters)
     */
    protected function buildProductPipelineWhereClause(array $filters, array &$params)
    {
        $filters = $this->parseFilters($filters);
        $conditions = [];

        if ($filters['team'] !== '') {
            $conditions[] = 'Owner_Role = :team';
            $params[':team'] = $filters['team'];
        }

        if ($filters['agent'] !== '') {
            $conditions[] = 'Opportunity_Owner = :agent';
            $params[':agent'] = $filters['agent'];
        }

        if ($filters['fiscal_period'] !== '') {
            $conditions[] = 'Fiscal_Period = :fiscal_period';
            $params[':fiscal_period'] = $filters['fiscal_period'];
        }

        if ($filters['month'] !== '') {
            $conditions[] = 'MONTH(Close_Date) = :month';
            $params[':month'] = (int)$filters['month'];
        }

        if ($filters['quarter'] !== '') {
            $conditions[] = "(
        CASE
            WHEN MONTH(Close_Date) BETWEEN 4 AND 6 THEN 1
            WHEN MONTH(Close_Date) BETWEEN 7 AND 9 THEN 2
            WHEN MONTH(Close_Date) BETWEEN 10 AND 12 THEN 3
            WHEN MONTH(Close_Date) BETWEEN 1 AND 3 THEN 4
        END
    ) = :quarter";
            $params[':quarter'] = (int)$filters['quarter'];
        }

        if ($filters['year'] !== '') {
            $conditions[] = 'YEAR(Close_Date) = :year';
            $params[':year'] = (int)$filters['year'];
        }

        // Add product family filter
        if (isset($filters['product_family']) && $filters['product_family'] !== '') {
            $conditions[] = 'Product_Family = :product_family';
            $params[':product_family'] = $filters['product_family'];
        }

        // Add stage filter - now properly handles empty/null values
        if (isset($filters['stage']) && $filters['stage'] !== '') {
            $conditions[] = 'Stage = :stage';
            $params[':stage'] = $filters['stage'];
        }

        return $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    }
}
