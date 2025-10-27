<?php

class CompanyController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllCompanies()
    {
        $stmt = $this->conn->prepare("
            SELECT 
                id_companies,
                name_companies,
                city_companies,
                fiscal_code,
                address,
                created_at
            FROM companies
            ORDER BY name_companies
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCompany($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM companies WHERE id_companies = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveCompany($data)
    {
        try {
            if (!empty($data['id_companies'])) {
                // Update
                $stmt = $this->conn->prepare("
                    UPDATE companies 
                    SET name_companies = ?, city_companies = ?, fiscal_code = ?, address = ?
                    WHERE id_companies = ?
                ");
                $stmt->execute([
                    $data['name_companies'],
                    $data['city_companies'],
                    $data['fiscal_code'],
                    $data['address'],
                    $data['id_companies']
                ]);
                return ['success' => true, 'id' => $data['id_companies']];
            } else {
                // Insert
                $stmt = $this->conn->prepare("
                    INSERT INTO companies (name_companies, city_companies, fiscal_code, address) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['name_companies'],
                    $data['city_companies'],
                    $data['fiscal_code'],
                    $data['address']
                ]);
                return ['success' => true, 'id' => $this->conn->lastInsertId()];
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'error' => 'Company name or fiscal code already exists'];
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteCompany($id)
    {
        try {
            // Check if company has projects
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM projects WHERE company_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return ['success' => false, 'error' => 'Company has projects and cannot be deleted'];
            }

            $stmt = $this->conn->prepare("DELETE FROM companies WHERE id_companies = ?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
