<?php

/**
 * SfdcWonDashboardModel
 * 
 * Aggregates won opportunity data by (month, team, type) for dashboard charts.
 * Filters Type != 'Other', uses fiscal year (April 1 – March 31).
 * Returns zero-filled monthly aggregates across all teams.
 */

require_once __DIR__ . '/SfdcWonModel.php';

class SfdcWonDashboardModel extends SfdcWonModel
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
     *     'ICT': {
     *       'aov': { 'Team A': [0, 100, 200, ...], 'Team B': [...], ... },
     *       'npv': { 'Team A': [...], 'Team B': [...], ... },
     *       'kpi': { 'total_aov': 5000, 'total_npv': 3000, 'deal_count': 15, 'avg_aov': 333.33, 'avg_npv': 200 }
     *     },
     *     'Fixed': { ... }
     *   }
     * }
     */
    public function getDashboardData($fiscalYear)
    {
        $range = $this->getFiscalYearRange($fiscalYear);
        $startDate = $range['start'];
        $endDate = $range['end'];

        // Query: aggregate by month, team, type (exclude 'Other')
        $sql = "
            SELECT 
                MONTH(Close_Date) as month_num,
                Owner_Role as team,
                Type as type,
                SUM(CAST(Revised_AOV AS DECIMAL(15,2))) as total_aov,
                SUM(CAST(Revised_NPV AS DECIMAL(15,2))) as total_npv,
                COUNT(*) as deal_count
            FROM {$this->table}
            WHERE Close_Date >= :start_date
              AND Close_Date <= :end_date
              AND Type IN ('ICT', 'Fixed')
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

        // Initialize data structure: months 1-12, all teams, both types
        $months = range(1, 12);
        $data = [
            'ICT' => [
                'aov' => [],
                'npv' => [],
                'kpi' => ['total_aov' => 0, 'total_npv' => 0, 'deal_count' => 0, 'avg_aov' => 0, 'avg_npv' => 0]
            ],
            'Fixed' => [
                'aov' => [],
                'npv' => [],
                'kpi' => ['total_aov' => 0, 'total_npv' => 0, 'deal_count' => 0, 'avg_aov' => 0, 'avg_npv' => 0]
            ]
        ];

        // Initialize arrays for each team with zero values
        foreach ($teams as $team) {
            $data['ICT']['aov'][$team] = array_fill(0, 12, 0);
            $data['ICT']['npv'][$team] = array_fill(0, 12, 0);
            $data['Fixed']['aov'][$team] = array_fill(0, 12, 0);
            $data['Fixed']['npv'][$team] = array_fill(0, 12, 0);
        }

        // Populate data from query results
        foreach ($rows as $row) {
            $monthIdx = (int)$row['month_num'] - 1; // 0-indexed for array
            $team = $row['team'];
            $type = $row['type'];
            $aov = (float)$row['total_aov'];
            $npv = (float)$row['total_npv'];
            $deals = (int)$row['deal_count'];

            if (isset($data[$type])) {
                if (!isset($data[$type]['aov'][$team])) {
                    $data[$type]['aov'][$team] = array_fill(0, 12, 0);
                    $data[$type]['npv'][$team] = array_fill(0, 12, 0);
                }

                $data[$type]['aov'][$team][$monthIdx] = $aov;
                $data[$type]['npv'][$team][$monthIdx] = $npv;

                // Accumulate KPIs
                $data[$type]['kpi']['total_aov'] += $aov;
                $data[$type]['kpi']['total_npv'] += $npv;
                $data[$type]['kpi']['deal_count'] += $deals;
            }
        }

        // Calculate averages per month
        foreach (['ICT', 'Fixed'] as $type) {
            $kpi = &$data[$type]['kpi'];

            // Count months with data (non-zero)
            $monthsWithData = 0;
            foreach ($data[$type]['aov'] as $teamData) {
                foreach ($teamData as $value) {
                    if ($value > 0) {
                        $monthsWithData++;
                        break;
                    }
                }
            }

            $kpi['avg_aov'] = $monthsWithData > 0 ? round($kpi['total_aov'] / $monthsWithData, 2) : 0;
            $kpi['avg_npv'] = $monthsWithData > 0 ? round($kpi['total_npv'] / $monthsWithData, 2) : 0;
        }

        return [
            'fiscal_year' => (int)$fiscalYear,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'teams' => $teams,
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
