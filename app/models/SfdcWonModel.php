<?php

require_once __DIR__ . '/SfdcBaseModel.php';

class SfdcWonModel extends SfdcBaseModel
{
    protected $table = 'sfdc_won';

    public function getAll(array $filters = [])
    {
        $params = [];
        $where = $this->buildWonWhereClause($filters, $params);

        $sql = "
            SELECT
                id,
                Opportunity_Reference_ID,
                Owner_Role,
                Opportunity_Owner,
                Account_Name,
                Fiscal_VAT_Number,
                Product_Name,
                Product_Family,
                Opportunity_Name,
                Opportunity_Product_Created_Date,
                Created_Date,
                Close_Date,
                Product_Term_months,
                Annual_Order_Value_Multi,
                Product_Annual_Recurring_Order_Value,
                Description,
                Product_TCV,
                Age,
                Link,
                Type,
                Revised_AOV,
                Revised_NPV,
                last_updated
            FROM {$this->table}
            {$where}
            ORDER BY Close_Date DESC, id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['Parsed_Description_NPV'] = $this->extractNpvFromDescription($row['Description'] ?? '');
            $row['Debug_Description'] = $row['Description'] ?? '';
            $row['Calculated_Revised_AOV'] = $this->getEffectiveRevisedAov($row);
            $row['Calculated_Revised_NPV'] = $this->getEffectiveRevisedNpv($row);

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

        }

        unset($row);

        return $rows;
    }

    public function getById($id)
    {
        $sql = "
            SELECT
                id,
                Opportunity_Reference_ID,
                Owner_Role,
                Opportunity_Owner,
                Account_Name,
                Fiscal_VAT_Number,
                Product_Name,
                Product_Family,
                Opportunity_Name,
                Opportunity_Product_Created_Date,
                Created_Date,
                Close_Date,
                Product_Term_months,
                Annual_Order_Value_Multi,
                Product_Annual_Recurring_Order_Value,
                Description,
                Product_TCV,
                Age,
                Link,
                Type,
                Revised_AOV,
                Revised_NPV,
                last_updated
            FROM {$this->table}
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $row['Calculated_Revised_AOV'] = $this->getEffectiveRevisedAov($row);
            $row['Calculated_Revised_NPV'] = $this->getEffectiveRevisedNpv($row);
        }

        return $row;
    }

    public function updateEditableField($id, $field, $value)
    {
        $allowedFields = ['Revised_AOV', 'Revised_NPV', 'Type'];

        if (!in_array($field, $allowedFields, true)) {
            throw new InvalidArgumentException('Field not allowed for update.');
        }

        if ($field === 'Type') {
            $allowedTypes = ['Fixed', 'ICT', 'Other', '', null];
            if (!in_array($value, $allowedTypes, true)) {
                throw new InvalidArgumentException('Invalid Type value.');
            }
        }

        if (in_array($field, ['Revised_AOV', 'Revised_NPV'], true)) {
            if ($value === '' || $value === null) {
                $value = 0;
            }

            if (!is_numeric($value)) {
                throw new InvalidArgumentException($field . ' must be numeric.');
            }

            $value = number_format((float)$value, 2, '.', '');
        }

        $sql = "UPDATE {$this->table} SET {$field} = :value WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':value', $value);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getTypeOptions()
    {
        return ['Fixed', 'ICT', 'Other'];
    }

    public function getDashboardSummary(array $filters = [])
    {
        $params = [];
        $where = $this->buildWonWhereClause($filters, $params);

        $sql = "
            SELECT
                COUNT(*) AS total_deals,
                SUM(Product_TCV) AS total_product_tcv,
                SUM(Product_Annual_Recurring_Order_Value) AS total_source_aov,
                SUM(
                    CASE
                        WHEN Revised_AOV IS NOT NULL AND Revised_AOV <> 0
                            THEN Revised_AOV
                        ELSE Product_Annual_Recurring_Order_Value
                    END
                ) AS total_effective_revised_aov,
                SUM(
                    CASE
                        WHEN Revised_NPV IS NOT NULL AND Revised_NPV <> 0
                            THEN Revised_NPV
                        ELSE Product_TCV
                    END
                ) AS total_effective_revised_npv
            FROM {$this->table}
            {$where}
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function buildWonWhereClause(array $filters, array &$params)
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

        return $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    }

    protected function getEffectiveRevisedAov(array $row)
    {
        $revised = isset($row['Revised_AOV']) ? (float)$row['Revised_AOV'] : 0;
        $source = isset($row['Product_Annual_Recurring_Order_Value']) ? (float)$row['Product_Annual_Recurring_Order_Value'] : 0;

        return $revised != 0.0 ? $revised : $source;
    }

    protected function getEffectiveRevisedNpv(array $row)
    {
        $revised = isset($row['Revised_NPV']) ? (float)$row['Revised_NPV'] : 0;

        if ($revised != 0.0) {
            return $revised;
        }

        $parsed = $this->extractNpvFromDescription($row['Description'] ?? '');

        if ($parsed !== null) {
            return $parsed;
        }

        return 0;
    }
    protected function extractNpvFromDescription($description)
    {
        if ($description === null) {
            return null;
        }

        $description = html_entity_decode(strip_tags((string)$description));
        $description = trim($description);

        if ($description === '') {
            return null;
        }

        $directValue = $this->normalizeNumericString($description);
        if ($directValue !== null) {
            return round($directValue, 2);
        }

        $patterns = [
            '/NPV\b.{0,40}?([0-9][0-9\.,\s]*)/i',
            '/Net\s+Present\s+Value\b.{0,40}?([0-9][0-9\.,\s]*)/i'
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
}
