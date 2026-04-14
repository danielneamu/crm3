<?php

require_once __DIR__ . '/SfdcBaseModel.php';

class SfdcProductPipelineModel extends SfdcBaseModel
{
    protected $table = 'sfdc_product_pipeline';

    protected function parseFilters(array $filters = [])
    {
        $baseFilters = parent::parseFilters($filters);
        $baseFilters['product_family'] = isset($filters['product_family']) ? trim($filters['product_family']) : '';
        $baseFilters['stage'] = isset($filters['stage']) ? trim($filters['stage']) : '';
        return $baseFilters;
    }

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

        if (isset($filters['product_family']) && $filters['product_family'] !== '') {
            $conditions[] = 'Product_Family = :product_family';
            $params[':product_family'] = $filters['product_family'];
        }

        if (isset($filters['stage']) && $filters['stage'] !== '') {
            $conditions[] = 'Stage = :stage';
            $params[':stage'] = $filters['stage'];
        }

        return $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    }

    /****** DASHBOARD METHODS ******/

    public function loadFilterOptions($fiscalYear)
    {
        $range = $this->getFiscalYearRange($fiscalYear);

        $sql = "
            SELECT DISTINCT
                Product_Family,
                Product_Name
            FROM {$this->table}
            WHERE Close_Date >= :start_date
              AND Close_Date <= :end_date
              AND Product_Name IS NOT NULL
              AND TRIM(Product_Name) <> ''
            ORDER BY Product_Family ASC, Product_Name ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':start_date', $range['start'], PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $range['end'], PDO::PARAM_STR);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $families = [];
        $allNames = [];
        $namesByFamily = [];

        foreach ($rows as $row) {
            $family = trim((string)($row['Product_Family'] ?? ''));
            $name = trim((string)($row['Product_Name'] ?? ''));

            if ($family !== '' && !in_array($family, $families, true)) {
                $families[] = $family;
            }

            if ($name !== '' && !in_array($name, $allNames, true)) {
                $allNames[] = $name;
            }

            if ($family !== '' && $name !== '') {
                if (!isset($namesByFamily[$family])) {
                    $namesByFamily[$family] = [];
                }

                if (!in_array($name, $namesByFamily[$family], true)) {
                    $namesByFamily[$family][] = $name;
                }
            }
        }

        sort($families);
        sort($allNames);

        foreach ($namesByFamily as $family => $names) {
            sort($names);
            $namesByFamily[$family] = array_values($names);
        }

        return [
            'fiscalYear' => (int)$fiscalYear,
            'productFamilies' => array_values($families),
            'allProductNames' => array_values($allNames),
            'productNamesByFamily' => $namesByFamily
        ];
    }

    public function getFilteredRawRows(array $filters)
    {
        $fiscalYear = isset($filters['fiscal_year']) ? (int)$filters['fiscal_year'] : self::getCurrentFiscalYear();
        $range = $this->getFiscalYearRange($fiscalYear);

        $params = [
            ':start_date' => $range['start'],
            ':end_date' => $range['end']
        ];

        $conditions = [
            'Close_Date >= :start_date',
            'Close_Date <= :end_date'
        ];

        $productFamilies = isset($filters['product_families']) && is_array($filters['product_families'])
            ? array_values(array_filter(array_map('trim', $filters['product_families']), function ($v) {
                return $v !== '';
            }))
            : [];

        $productNames = isset($filters['product_names']) && is_array($filters['product_names'])
            ? array_values(array_filter(array_map('trim', $filters['product_names']), function ($v) {
                return $v !== '';
            }))
            : [];

        if (!empty($productFamilies)) {
            $familyPlaceholders = [];
            foreach ($productFamilies as $index => $family) {
                $key = ':product_family_' . $index;
                $familyPlaceholders[] = $key;
                $params[$key] = $family;
            }
            $conditions[] = 'Product_Family IN (' . implode(', ', $familyPlaceholders) . ')';
        }

        if (!empty($productNames)) {
            $namePlaceholders = [];
            foreach ($productNames as $index => $name) {
                $key = ':product_name_' . $index;
                $namePlaceholders[] = $key;
                $params[$key] = $name;
            }
            $conditions[] = 'Product_Name IN (' . implode(', ', $namePlaceholders) . ')';
        }

        $sql = "
            SELECT
                Product_Pipeline_ID,
                Opportunity_Reference_ID,
                Opportunity_Owner,
                Owner_Role,
                Account_Name,
                Opportunity_Name,
                Fiscal_Period,
                Stage,
                Probability_Percent,
                Age,
                Close_Date,
                Contract_Term_Months,
                Annual_Order_Value_Multi,
                Product_Family,
                Product_Name,
                Product_Code,
                Product_Annual_Recurring_Order_Value
            FROM {$this->table}
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY Opportunity_Reference_ID ASC, Product_Pipeline_ID ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function normalizeDashboardRow(array $row)
    {
        $contractTerm = isset($row['Contract_Term_Months']) ? (float)$row['Contract_Term_Months'] : 0;
        $aovMulti = isset($row['Annual_Order_Value_Multi']) ? (float)$row['Annual_Order_Value_Multi'] : 0;
        $arrov = isset($row['Product_Annual_Recurring_Order_Value']) ? (float)$row['Product_Annual_Recurring_Order_Value'] : 0;

        return [
            'product_pipeline_id' => isset($row['Product_Pipeline_ID']) ? (int)$row['Product_Pipeline_ID'] : null,
            'opp_ref' => trim((string)($row['Opportunity_Reference_ID'] ?? '')),
            'agent' => trim((string)($row['Opportunity_Owner'] ?? '')),
            'team' => trim((string)($row['Owner_Role'] ?? '')),
            'account_name' => trim((string)($row['Account_Name'] ?? '')),
            'opportunity_name' => trim((string)($row['Opportunity_Name'] ?? '')),
            'fiscal_period' => trim((string)($row['Fiscal_Period'] ?? '')),
            'stage' => trim((string)($row['Stage'] ?? '')),
            'probability' => isset($row['Probability_Percent']) ? (float)$row['Probability_Percent'] : 0,
            'age' => isset($row['Age']) ? (float)$row['Age'] : 0,
            'close_date' => $row['Close_Date'] ?? null,
            'contract_term_months' => $contractTerm,
            'aov_multi' => $aovMulti,
            'product_family' => trim((string)($row['Product_Family'] ?? '')),
            'product_name' => trim((string)($row['Product_Name'] ?? '')),
            'product_code' => trim((string)($row['Product_Code'] ?? '')),
            'arrov' => $arrov
        ];
    }

    /**
     * DATASET 2: Clean ARROV values
     * 
     * Rule: If ARROV > AOV_Multi, normalize by /12. Otherwise keep raw.
     * Check if sum of cleaned ARROV = AOV_Multi for each opp.
     * Log mismatches to error_log.
     */
    public function cleanRowArrovValues(array $rows)
    {
        $grouped = [];

        foreach ($rows as $row) {
            $normalized = $this->normalizeDashboardRow($row);
            $oppRef = $normalized['opp_ref'];

            if ($oppRef === '') {
                continue;
            }

            if (!isset($grouped[$oppRef])) {
                $grouped[$oppRef] = [];
            }

            $grouped[$oppRef][] = $normalized;
        }

        $cleanedRows = [];
        $mismatches = [];

        foreach ($grouped as $oppRef => $oppRows) {
            $aovMulti = (float)$oppRows[0]['aov_multi'];
            $cleanedSum = 0.0;

            // Apply normalization rule
            foreach ($oppRows as $row) {
                $rawArrov = (float)$row['arrov'];

                if ($rawArrov > $aovMulti) {
                    // Normalize: ARROV / 12
                    $cleanedArrov = round($rawArrov / 12, 2);
                } else {
                    // Keep raw
                    $cleanedArrov = $rawArrov;
                }

                $row['cleaned_arrov'] = $cleanedArrov;
                $row['cleaned_aov'] = $aovMulti;
                $cleanedRows[] = $row;
                $cleanedSum += $cleanedArrov;
            }

            // Check if sum matches AOV Multi
            if (abs($cleanedSum - $aovMulti) >= 0.01) {
                $mismatches[] = sprintf(
                    '%s: cleaned sum %.2f ≠ AOV Multi %.2f',
                    $oppRef,
                    round($cleanedSum, 2),
                    round($aovMulti, 2)
                );
            }
        }

        //*
        //if (!empty($mismatches)) {
        //  error_log('SFDC Product Pipeline Normalization Mismatches: ' . implode(' | ', $mismatches));
        //}

        return $cleanedRows;
    }

    public function buildUniqueOpportunityRows(array $rows)
    {
        $unique = [];

        foreach ($rows as $row) {
            $oppRef = trim((string)($row['opp_ref'] ?? ''));

            if ($oppRef === '') {
                continue;
            }

            if (!isset($unique[$oppRef])) {
                $unique[$oppRef] = [
                    'opp_ref' => $oppRef,
                    'agent' => $row['agent'] ?? '',
                    'team' => $row['team'] ?? '',
                    'account_name' => $row['account_name'] ?? '',
                    'opportunity_name' => $row['opportunity_name'] ?? '',
                    'stage' => $row['stage'] ?? '',
                    'probability' => isset($row['probability']) ? (float)$row['probability'] : 0,
                    'age' => isset($row['age']) ? (float)$row['age'] : 0,
                    'close_date' => $row['close_date'] ?? null,
                    'cleaned_aov' => isset($row['cleaned_aov']) ? (float)$row['cleaned_aov'] : 0
                ];
            }
        }

        return array_values($unique);
    }

    public function getKpiCards(array $uniqueOppRows)
    {
        $totalPipeline = 0.0;
        $weightedPipeline = 0.0;
        $totalAge = 0.0;
        $oppCount = count($uniqueOppRows);

        foreach ($uniqueOppRows as $row) {
            $aov = isset($row['cleaned_aov']) ? (float)$row['cleaned_aov'] : 0;
            $probability = isset($row['probability']) ? (float)$row['probability'] : 0;
            $age = isset($row['age']) ? (float)$row['age'] : 0;

            $totalPipeline += $aov;
            $weightedPipeline += $aov * ($probability / 100);
            $totalAge += $age;
        }

        return [
            'totalPipelineAov' => round($totalPipeline, 2),
            'weightedPipeline' => round($weightedPipeline, 2),
            'avgAge' => $oppCount > 0 ? round($totalAge / $oppCount, 2) : 0,
            'oppCount' => $oppCount
        ];
    }

    public function getStageChart(array $uniqueOppRows)
    {
        $totals = [];

        foreach ($uniqueOppRows as $row) {
            $stage = trim((string)($row['stage'] ?? ''));
            if ($stage === '') {
                $stage = 'Unknown';
            }

            if (!isset($totals[$stage])) {
                $totals[$stage] = 0.0;
            }

            $totals[$stage] += (float)$row['cleaned_aov'];
        }

        arsort($totals);

        return [
            'labels' => array_keys($totals),
            'values' => array_values($totals)
        ];
    }

    public function getTeamChart(array $uniqueOppRows)
    {
        $teams = [];
        $stages = [];
        $matrix = [];

        foreach ($uniqueOppRows as $row) {
            $team = trim((string)($row['team'] ?? ''));
            $stage = trim((string)($row['stage'] ?? ''));

            if ($team === '') {
                $team = 'No Team';
            }

            if ($stage === '') {
                $stage = 'Unknown';
            }

            if (!in_array($team, $teams, true)) {
                $teams[] = $team;
            }

            if (!in_array($stage, $stages, true)) {
                $stages[] = $stage;
            }

            if (!isset($matrix[$stage])) {
                $matrix[$stage] = [];
            }

            if (!isset($matrix[$stage][$team])) {
                $matrix[$stage][$team] = 0.0;
            }

            $matrix[$stage][$team] += (float)$row['cleaned_aov'];
        }

        sort($teams);
        sort($stages);

        $datasets = [];
        foreach ($stages as $stage) {
            $data = [];
            foreach ($teams as $team) {
                $data[] = isset($matrix[$stage][$team]) ? round($matrix[$stage][$team], 2) : 0;
            }

            $datasets[] = [
                'label' => $stage,
                'data' => $data
            ];
        }

        return [
            'labels' => $teams,
            'datasets' => $datasets
        ];
    }

    public function getAgeScatter(array $uniqueOppRows)
    {
        $datasetsByStage = [];

        foreach ($uniqueOppRows as $row) {
            $stage = trim((string)($row['stage'] ?? ''));
            if ($stage === '') {
                $stage = 'Unknown';
            }

            if (!isset($datasetsByStage[$stage])) {
                $datasetsByStage[$stage] = [];
            }

            $datasetsByStage[$stage][] = [
                'x' => (float)($row['age'] ?? 0),
                'y' => (float)($row['cleaned_aov'] ?? 0),
                'oppRef' => $row['opp_ref'] ?? '',
                'opportunityName' => $row['opportunity_name'] ?? ''
            ];
        }

        $datasets = [];
        foreach ($datasetsByStage as $stage => $points) {
            $datasets[] = [
                'label' => $stage,
                'data' => $points
            ];
        }

        return [
            'datasets' => $datasets
        ];
    }

    public function getProbabilityChart(array $uniqueOppRows)
    {
        $buckets = [
            '0-10' => ['count' => 0, 'aov' => 0.0],
            '11-25' => ['count' => 0, 'aov' => 0.0],
            '26-50' => ['count' => 0, 'aov' => 0.0],
            '51-75' => ['count' => 0, 'aov' => 0.0],
            '76-100' => ['count' => 0, 'aov' => 0.0],
        ];

        foreach ($uniqueOppRows as $row) {
            $prob = (float)($row['probability'] ?? 0);
            $aov = (float)($row['cleaned_aov'] ?? 0);

            if ($prob <= 10) {
                $bucket = '0-10';
            } elseif ($prob <= 25) {
                $bucket = '11-25';
            } elseif ($prob <= 50) {
                $bucket = '26-50';
            } elseif ($prob <= 75) {
                $bucket = '51-75';
            } else {
                $bucket = '76-100';
            }

            $buckets[$bucket]['count']++;
            $buckets[$bucket]['aov'] += $aov;
        }

        $countValues = [];
        $aovValues = [];

        foreach ($buckets as $bucket) {
            $countValues[] = (int)$bucket['count'];
            $aovValues[] = round((float)$bucket['aov'], 2);
        }

        return [
            'labels' => array_values(array_keys($buckets)),
            'countValues' => array_values($countValues),
            'aovValues' => array_values($aovValues)
        ];
    }

    public function getProductFamilyMixChart(array $cleanedRows)
    {
        $totals = [];

        foreach ($cleanedRows as $row) {
            $family = trim((string)($row['product_family'] ?? ''));
            if ($family === '') {
                $family = 'Uncategorized';
            }

            if (!isset($totals[$family])) {
                $totals[$family] = 0.0;
            }

            $totals[$family] += (float)($row['cleaned_arrov'] ?? 0);
        }

        arsort($totals);

        return [
            'labels' => array_keys($totals),
            'values' => array_values($totals)
        ];
    }

    public function getCloseTimeline(array $uniqueOppRows)
    {
        $points = [];

        foreach ($uniqueOppRows as $row) {
            $closeDate = $row['close_date'] ?? null;
            if (empty($closeDate) || $closeDate === '0000-00-00') {
                continue;
            }

            $points[] = [
                'x' => $closeDate,
                'y' => (float)($row['cleaned_aov'] ?? 0),
                'r' => 6,
                'stage' => $row['stage'] ?? '',
                'oppRef' => $row['opp_ref'] ?? '',
                'opportunityName' => $row['opportunity_name'] ?? ''
            ];
        }

        return [
            'points' => $points
        ];
    }

    public function getMonthlyTeamFiscalChart(array $cleanedRows)
    {
        $fiscalMonthMap = [
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar'
        ];

        $labels = array_values($fiscalMonthMap);
        $teams = [];
        $matrix = [];

        foreach ($cleanedRows as $row) {
            $team = trim((string)($row['team'] ?? ''));
            $closeDate = $row['close_date'] ?? null;

            if ($team === '') {
                $team = 'No Team';
            }

            if (empty($closeDate) || $closeDate === '0000-00-00') {
                continue;
            }

            $month = (int)date('n', strtotime($closeDate));
            if (!isset($fiscalMonthMap[$month])) {
                continue;
            }

            $monthLabel = $fiscalMonthMap[$month];

            if (!in_array($team, $teams, true)) {
                $teams[] = $team;
            }

            if (!isset($matrix[$team])) {
                $matrix[$team] = [];
            }

            if (!isset($matrix[$team][$monthLabel])) {
                $matrix[$team][$monthLabel] = 0.0;
            }

            $matrix[$team][$monthLabel] += (float)($row['cleaned_arrov'] ?? 0);
        }

        sort($teams);

        $datasets = [];
        foreach ($teams as $team) {
            $data = [];
            foreach ($labels as $label) {
                $data[] = isset($matrix[$team][$label]) ? round($matrix[$team][$label], 2) : 0;
            }

            $datasets[] = [
                'label' => $team,
                'data' => $data
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }

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

    public static function getCurrentFiscalYear()
    {
        $month = (int)date('n');
        $year = (int)date('Y');
        return $month >= 4 ? $year + 1 : $year;
    }
}
