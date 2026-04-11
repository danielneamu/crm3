<?php

/**
 * SfdcPipelineDashboardModel
 * 
 * Aggregates pipeline opportunity data by (month, team, type) for dashboard charts.
 * Includes all types (Fixed, ICT, Other, null).
 * Uses fiscal year (April 1 – March 31).
 * Returns zero-filled monthly aggregates across all teams.
 */

require_once __DIR__ . '/SfdcPipelineModel.php';

class SfdcPipelineDashboardModel extends SfdcPipelineModel
{
    /**
     * Get dashboard data for a fiscal year
     * 
     * @param int $fiscalYear Fiscal year (e.g., 2026 = April 1, 2025 – March 31, 2026)
     * @return array {
     *   'fiscal_year': int,
     *   'date_range': { 'start': 'YYYY-MM-DD', 'end': 'YYYY-MM-DD' },
     *   'teams': ['Team A', 'Team B', ...] (alphabetically ordered),
     *   'months': [1, 2, 3, ..., 12],
     *   'data': {
     *     'All': {
     *       'amount': { 'Team A': [0, 100, 200, ...], 'Team B': [...], ... },
     *       'expected_revenue': { 'Team A': [...], 'Team B': [...], ... },
     *       'kpi': { 'total_amount': 5000, 'total_expected_revenue': 3000, 'deal_count': 15, 'avg_amount': 333.33, 'avg_expected_revenue': 200 }
     *     },
     *     'Fixed': { ... },
     *     'ICT': { ... },
     *     'Other': { ... }
     *   }
     * }
     */
    public function getDashboardData($fiscalYear)
    {
        $range = $this->getFiscalYearRange($fiscalYear);
        $startDate = $range['start'];
        $endDate = $range['end'];

        // Query: aggregate by month, team, type (all types included)
        $sql = "
            SELECT 
                MONTH(Close_Date) as month_num,
                Owner_Role as team,
                COALESCE(Type, 'Untyped') as type,
                SUM(CAST(Amount AS DECIMAL(15,2))) as total_amount,
                SUM(CAST(Expected_Revenue AS DECIMAL(15,2))) as total_expected_revenue,
                COUNT(*) as deal_count
            FROM {$this->table}
            WHERE Close_Date >= :start_date
              AND Close_Date <= :end_date
            GROUP BY MONTH(Close_Date), Owner_Role, Type
            ORDER BY MONTH(Close_Date), Owner_Role, Type
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Extract unique teams and sort alphabetically
        $teams = array_unique(array_map(function ($row) {
            return $row['team'];
        }, $rows));
        sort($teams);

        // Extract unique types
        $types = array_unique(array_map(function ($row) {
            return $row['type'];
        }, $rows));
        sort($types);

        // Initialize data structure: months 1-12, all teams, all types + 'All' aggregate
        $months = range(1, 12);
        $data = [];

        // Add 'All' (aggregate across all types)
        $data['All'] = [
            'amount' => [],
            'expected_revenue' => [],
            'kpi' => ['total_amount' => 0, 'total_expected_revenue' => 0, 'deal_count' => 0, 'avg_amount' => 0, 'avg_expected_revenue' => 0]
        ];

        // Add per-type buckets
        foreach ($types as $type) {
            $data[$type] = [
                'amount' => [],
                'expected_revenue' => [],
                'kpi' => ['total_amount' => 0, 'total_expected_revenue' => 0, 'deal_count' => 0, 'avg_amount' => 0, 'avg_expected_revenue' => 0]
            ];
        }

        // Initialize arrays for each team with zero values
        foreach ($teams as $team) {
            $data['All']['amount'][$team] = array_fill(0, 12, 0);
            $data['All']['expected_revenue'][$team] = array_fill(0, 12, 0);

            foreach ($types as $type) {
                $data[$type]['amount'][$team] = array_fill(0, 12, 0);
                $data[$type]['expected_revenue'][$team] = array_fill(0, 12, 0);
            }
        }

        // Populate data from query results
        foreach ($rows as $row) {
            $monthIdx = (int)$row['month_num'] - 1; // 0-indexed for array
            $team = $row['team'];
            $type = $row['type'];
            $amount = (float)$row['total_amount'];
            $expRevenue = (float)$row['total_expected_revenue'];
            $deals = (int)$row['deal_count'];

            // Ensure team arrays exist
            if (!isset($data[$type]['amount'][$team])) {
                $data[$type]['amount'][$team] = array_fill(0, 12, 0);
                $data[$type]['expected_revenue'][$team] = array_fill(0, 12, 0);
            }

            // Update type-specific data
            $data[$type]['amount'][$team][$monthIdx] = $amount;
            $data[$type]['expected_revenue'][$team][$monthIdx] = $expRevenue;

            // Accumulate type-specific KPIs
            $data[$type]['kpi']['total_amount'] += $amount;
            $data[$type]['kpi']['total_expected_revenue'] += $expRevenue;
            $data[$type]['kpi']['deal_count'] += $deals;

            // Also accumulate to 'All'
            $data['All']['amount'][$team][$monthIdx] += $amount;
            $data['All']['expected_revenue'][$team][$monthIdx] += $expRevenue;
            $data['All']['kpi']['total_amount'] += $amount;
            $data['All']['kpi']['total_expected_revenue'] += $expRevenue;
            $data['All']['kpi']['deal_count'] += $deals;
        }

        // Calculate averages per type
        foreach (array_merge(['All'], $types) as $typeKey) {
            if (!isset($data[$typeKey])) {
                continue;
            }

            $kpi = &$data[$typeKey]['kpi'];

            // Count months with data (non-zero)
            $monthsWithData = 0;
            foreach ($data[$typeKey]['amount'] as $teamData) {
                foreach ($teamData as $value) {
                    if ($value > 0) {
                        $monthsWithData++;
                        break;
                    }
                }
            }

            $kpi['avg_amount'] = $monthsWithData > 0 ? round($kpi['total_amount'] / $monthsWithData, 2) : 0;
            $kpi['avg_expected_revenue'] = $monthsWithData > 0 ? round($kpi['total_expected_revenue'] / $monthsWithData, 2) : 0;
        }

        return [
            'fiscal_year' => (int)$fiscalYear,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'teams' => $teams,
            'types' => $types,
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
        $startYear = $fy - 1; // April of previous calendar year
        $endYear = $fy;       // March of current fiscal year

        return [
            'start' => "$startYear-04-01",
            'end' => "$endYear-03-31"
        ];
    }

    /**
     * Get current fiscal year (handles April/May boundary)
     * 
     * @return int Current fiscal year
     */
    public static function getCurrentFiscalYear()
    {
        $month = (int)date('n');
        $year = (int)date('Y');

        // If month >= 4 (April), we're in FY of next calendar year
        // Otherwise, we're in FY of current calendar year
        return $month >= 4 ? $year + 1 : $year;
    }
}
