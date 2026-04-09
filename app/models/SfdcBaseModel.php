<?php

class SfdcBaseModel
{
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

    protected function getConnection()
    {
        return $this->conn;
    }

    public function getTeams($source = 'main')
    {
        $table = $this->resolveSourceTable($source);

        $sql = "
            SELECT DISTINCT Owner_Role
            FROM {$table}
            WHERE Owner_Role IS NOT NULL
              AND TRIM(Owner_Role) <> ''
            ORDER BY Owner_Role ASC
        ";

        $stmt = $this->conn->query($sql);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAgents($source = 'main')
    {
        $table = $this->resolveSourceTable($source);

        $sql = "
            SELECT DISTINCT Opportunity_Owner
            FROM {$table}
            WHERE Opportunity_Owner IS NOT NULL
              AND TRIM(Opportunity_Owner) <> ''
            ORDER BY Opportunity_Owner ASC
        ";

        $stmt = $this->conn->query($sql);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getFiscalPeriods($source = 'main')
    {
        $table = $this->resolveSourceTable($source);

        $sql = "
            SELECT DISTINCT Fiscal_Period
            FROM {$table}
            WHERE Fiscal_Period IS NOT NULL
              AND TRIM(Fiscal_Period) <> ''
            ORDER BY Fiscal_Period DESC
        ";

        $stmt = $this->conn->query($sql);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function parseFilters(array $filters = [])
    {
        return [
            'team' => isset($filters['team']) ? trim($filters['team']) : '',
            'agent' => isset($filters['agent']) ? trim($filters['agent']) : '',
            'fiscal_period' => isset($filters['fiscal_period']) ? trim($filters['fiscal_period']) : '',
            'month' => isset($filters['month']) ? trim($filters['month']) : '',
            'quarter' => isset($filters['quarter']) ? trim($filters['quarter']) : '',
            'year' => isset($filters['year']) ? trim($filters['year']) : '',
            'real_flag' => isset($filters['real_flag']) ? trim((string)$filters['real_flag']) : '',
        ];
    }



    protected function getFiscalQuarterSql(string $dateColumn = 'Close_Date'): string
    {
        return "(
        CASE
            WHEN MONTH($dateColumn) BETWEEN 4 AND 6 THEN 1
            WHEN MONTH($dateColumn) BETWEEN 7 AND 9 THEN 2
            WHEN MONTH($dateColumn) BETWEEN 10 AND 12 THEN 3
            WHEN MONTH($dateColumn) BETWEEN 1 AND 3 THEN 4
        END
    )";
    }
    protected function getFiscalYearSql(string $dateColumn = 'Close_Date'): string
    {
        return "(
        CASE
            WHEN MONTH($dateColumn) >= 4 THEN YEAR($dateColumn)
            ELSE YEAR($dateColumn) - 1
        END
    )";
    }


    protected function buildCommonWhereClause(array $filters, array &$params, $tableAlias = '')
    {
        $filters = $this->parseFilters($filters);
        $prefix = $tableAlias !== '' ? $tableAlias . '.' : '';
        $conditions = [];

        if ($filters['team'] !== '') {
            $conditions[] = "{$prefix}Owner_Role = :team";
            $params[':team'] = $filters['team'];
        }

        if ($filters['agent'] !== '') {
            $conditions[] = "{$prefix}Opportunity_Owner = :agent";
            $params[':agent'] = $filters['agent'];
        }

        if ($filters['fiscal_period'] !== '') {
            $conditions[] = "{$prefix}Fiscal_Period = :fiscal_period";
            $params[':fiscal_period'] = $filters['fiscal_period'];
        }

        if ($filters['month'] !== '') {
            $conditions[] = "MONTH({$prefix}Close_Date) = :month";
            $params[':month'] = (int)$filters['month'];
        }

        if ($filters['quarter'] !== '') {
            $conditions[] = $this->getFiscalQuarterSql("{$prefix}Close_Date") . " = :quarter";
            $params[':quarter'] = (int)$filters['quarter'];
        }

        if ($filters['year'] !== '') {
            $conditions[] = $this->getFiscalYearSql("{$prefix}Close_Date") . " = :year";
            $params[':year'] = (int)$filters['year'];
       }

        if ($filters['real_flag'] !== '' && in_array($filters['real_flag'], ['0', '1'], true)) {
            $conditions[] = "{$prefix}Real_Flag = :real_flag";
            $params[':real_flag'] = (int)$filters['real_flag'];
        }

        return $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    }

    protected function resolveSourceTable($source)
    {
        $map = [
            'main' => 'sfdc_main',
            'pipeline' => 'sfdc_main',
            'won' => 'sfdc_won',
        ];

        $source = strtolower(trim((string)$source));

        if (!isset($map[$source])) {
            throw new InvalidArgumentException('Invalid SFDC source table requested.');
        }

        return $map[$source];
    }


    
}
