<?php

require_once __DIR__ . '/SfdcBaseModel.php';

class SfdcPipelineModel extends SfdcBaseModel
{
    protected $table = 'sfdc_main';

    public function getAll(array $filters = [])
    {
        $params = [];
        $where = $this->buildPipelineWhereClause($filters, $params);

        $sql = "
            SELECT
                Opportunity_Reference_ID,
                Opportunity_Owner,
                Owner_Role,
                Account_Name,
                Opportunity_Name,
                Fiscal_VAT_Number,
                Fiscal_Period,
                Amount,
                Expected_Revenue,
                Annual_Order_Value_Multi,
                Description,
                Stage,
                Probability_Percent,
                Age,
                Created_Date,
                Close_Date,
                Last_Modified_Date,
                Contract_Term_Months,
                Link,
                Type,
                Real_Flag
            FROM {$this->table}
            {$where}
            ORDER BY Close_Date DESC, Opportunity_Reference_ID DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            // Parse Description as number (like NPV extraction)
            $row['Parsed_Description_Value'] = $this->extractNumberFromDescription($row['Description'] ?? '');
            $row['Debug_Description'] = $row['Description'] ?? '';

            // Group by month and team
            $closeDate = $row['Close_Date'] ?? null;
            $ownerRole = trim((string)($row['Owner_Role'] ?? ''));

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

            // Normalize Real_Flag: null or 1 = true
            $realFlag = $row['Real_Flag'];
            $row['Real_Flag_Display'] = ($realFlag === null || $realFlag == 1) ? 'Yes' : 'No';
        }

        unset($row);

        return $rows;
    }

    public function getById($id)
    {
        $sql = "
            SELECT
                Opportunity_Reference_ID,
                Opportunity_Owner,
                Owner_Role,
                Account_Name,
                Opportunity_Name,
                Fiscal_VAT_Number,
                Fiscal_Period,
                Amount,
                Expected_Revenue,
                Annual_Order_Value_Multi,
                Description,
                Stage,
                Probability_Percent,
                Age,
                Created_Date,
                Close_Date,
                Last_Modified_Date,
                Contract_Term_Months,
                Link,
                Type,
                Real_Flag
            FROM {$this->table}
            WHERE Opportunity_Reference_ID = :id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (string)$id, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $row['Parsed_Description_Value'] = $this->extractNumberFromDescription($row['Description'] ?? '');
            $realFlag = $row['Real_Flag'];
            $row['Real_Flag_Display'] = ($realFlag === null || $realFlag == 1) ? 'Yes' : 'No';
        }

        return $row;
    }

    public function updateEditableField($id, $field, $value)
    {
        $allowedFields = ['Type', 'Real_Flag'];

        if (!in_array($field, $allowedFields, true)) {
            throw new InvalidArgumentException('Field not allowed for update.');
        }

        if ($field === 'Type') {
            $allowedTypes = ['Fixed', 'ICT', 'Other', '', null];
            if (!in_array($value, $allowedTypes, true)) {
                throw new InvalidArgumentException('Invalid Type value.');
            }
        }

        if ($field === 'Real_Flag') {
            // Accept: 0, 1, null, '0', '1', '', 'Yes', 'No'
            if (in_array($value, [0, 1, null, '0', '1', '', 'Yes', 'No'], true)) {
                // Convert Yes/No to 1/0
                if ($value === 'Yes' || $value == 1) {
                    $value = 1;
                } elseif ($value === 'No' || $value == 0 || $value === '' || $value === null) {
                    $value = 0;
                }
            } else {
                throw new InvalidArgumentException('Invalid Real_Flag value.');
            }
        }

        $sql = "UPDATE {$this->table} SET {$field} = :value WHERE Opportunity_Reference_ID = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':value', $value);
        $stmt->bindValue(':id', (string)$id, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function getTypeOptions()
    {
        return ['Fixed', 'ICT', 'Other'];
    }

    /**
     * Extract numeric value from Description field
     * Handles: direct number, "NPV: 1000", "Net Present Value: 500", etc.
     * Returns null if no number found
     */
    protected function extractNumberFromDescription($description)
    {
        if ($description === null) {
            return null;
        }

        $description = html_entity_decode(strip_tags((string)$description));
        $description = trim($description);

        if ($description === '') {
            return null;
        }

        // Try direct number parse
        $directValue = $this->normalizeNumericString($description);
        if ($directValue !== null) {
            return round($directValue, 2);
        }

        // Try patterns: "NPV: 1000", "Net Present Value: 500", etc.
        $patterns = [
            '/NPV\b.{0,40}?([0-9][0-9\.,\s]*)/i',
            '/Net\s+Present\s+Value\b.{0,40}?([0-9][0-9\.,\s]*)/i',
            '/Value\b.{0,40}?([0-9][0-9\.,\s]*)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                $value = $this->normalizeNumericString($matches[1]);
                if ($value !== null) {
                    return round($value, 2);
                }
            }
        }

        return null;
    }

    /**
     * Normalize numeric strings with various formats (1000.50, 1000,50, 1.000,50, etc.)
     */
    protected function normalizeNumericString($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string)$value);
        $value = preg_replace('/[^\d,\.\-\s]/', '', $value);
        $value = str_replace(' ', '', $value);

        if ($value === '') {
            return null;
        }

        $lastDot = strrpos($value, '.');
        $lastComma = strrpos($value, ',');

        if ($lastDot !== false && $lastComma !== false) {
            if ($lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($lastComma !== false) {
            $parts = explode(',', $value);
            if (count($parts) === 2 && strlen(end($parts)) <= 2) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } else {
            $dotParts = explode('.', $value);
            if (count($dotParts) > 2) {
                $last = array_pop($dotParts);
                $value = implode('', $dotParts) . '.' . $last;
            }
        }

        return is_numeric($value) ? (float)$value : null;
    }

    protected function buildPipelineWhereClause(array $filters, array &$params)
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

        if ($filters['real_flag'] !== '' && in_array($filters['real_flag'], ['0', '1'], true)) {
            $conditions[] = 'Real_Flag = :real_flag';
            $params[':real_flag'] = (int)$filters['real_flag'];
        }

        return $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    }
}