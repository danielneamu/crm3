<?php

class PartnerController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllPartners()
    {
        $stmt = $this->conn->prepare("
            SELECT 
                p.id_parteneri,
                p.name_parteneri,
                p.type_parteneri,
                p.created_at,
                GROUP_CONCAT(DISTINCT t.tag ORDER BY t.tag SEPARATOR ', ') AS tags,
                GROUP_CONCAT(DISTINCT t.id ORDER BY t.tag) AS tag_ids,
                COUNT(DISTINCT pc.id) AS contact_count
            FROM parteneri p
            LEFT JOIN partagmap pm ON p.id_parteneri = pm.idpart
            LEFT JOIN partags t ON pm.idtag = t.id
            LEFT JOIN partcontacts pc ON p.id_parteneri = pc.partner_id AND pc.active = 1
            GROUP BY p.id_parteneri
            ORDER BY p.name_parteneri
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPartner($id)
    {
        // Get partner info
        $stmt = $this->conn->prepare("SELECT * FROM parteneri WHERE id_parteneri = ?");
        $stmt->execute([$id]);
        $partner = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$partner) return null;

        // Get contacts
        $stmt = $this->conn->prepare("
            SELECT * FROM partcontacts 
            WHERE partner_id = ? AND active = 1
            ORDER BY name
        ");
        $stmt->execute([$id]);
        $partner['contacts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get tags
        $stmt = $this->conn->prepare("
            SELECT t.id, t.tag 
            FROM partags t
            JOIN partagmap pm ON t.id = pm.idtag
            WHERE pm.idpart = ?
        ");
        $stmt->execute([$id]);
        $partner['tags'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $partner;
    }

    public function getAllTags()
    {
        $stmt = $this->conn->prepare("SELECT id, tag, comment FROM partags ORDER BY tag");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function savePartner($data)
    {
        try {
            $this->conn->beginTransaction();

            if (!empty($data['id_parteneri'])) {
                // Update
                $stmt = $this->conn->prepare("
                    UPDATE parteneri 
                    SET name_parteneri = ?, type_parteneri = ?
                    WHERE id_parteneri = ?
                ");
                $stmt->execute([
                    $data['name_parteneri'],
                    $data['type_parteneri'],
                    $data['id_parteneri']
                ]);
                $partnerId = $data['id_parteneri'];
            } else {
                // Insert
                $stmt = $this->conn->prepare("
                    INSERT INTO parteneri (name_parteneri, type_parteneri) 
                    VALUES (?, ?)
                ");
                $stmt->execute([
                    $data['name_parteneri'],
                    $data['type_parteneri']
                ]);
                $partnerId = $this->conn->lastInsertId();
            }

            // Update tags
            $this->updatePartnerTags($partnerId, $data['tags'] ?? []);

            // Update contacts
            $this->updatePartnerContacts($partnerId, $data['contacts'] ?? []);

            $this->conn->commit();
            return ['success' => true, 'id' => $partnerId];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function updatePartnerTags($partnerId, $tagIds)
    {
        // Delete existing
        $stmt = $this->conn->prepare("DELETE FROM partagmap WHERE idpart = ?");
        $stmt->execute([$partnerId]);

        // Insert new
        if (!empty($tagIds)) {
            $stmt = $this->conn->prepare("INSERT INTO partagmap (idpart, idtag) VALUES (?, ?)");
            foreach ($tagIds as $tagId) {
                $stmt->execute([$partnerId, $tagId]);
            }
        }
    }

    private function updatePartnerContacts($partnerId, $contacts)
    {
        // Deactivate all existing
        $stmt = $this->conn->prepare("UPDATE partcontacts SET active = 0 WHERE partner_id = ?");
        $stmt->execute([$partnerId]);

        // Insert/update new contacts
        foreach ($contacts as $contact) {
            if (!empty($contact['id'])) {
                // Update existing
                $stmt = $this->conn->prepare("
                    UPDATE partcontacts 
                    SET name = ?, role = ?, phone = ?, email = ?, comments = ?, active = 1
                    WHERE id = ?
                ");
                $stmt->execute([
                    $contact['name'],
                    $contact['role'],
                    $contact['phone'],
                    $contact['email'],
                    $contact['comments'] ?? '',
                    $contact['id']
                ]);
            } else {
                // Insert new
                $stmt = $this->conn->prepare("
                    INSERT INTO partcontacts (partner_id, name, role, phone, email, comments, active)
                    VALUES (?, ?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([
                    $partnerId,
                    $contact['name'],
                    $contact['role'],
                    $contact['phone'],
                    $contact['email'],
                    $contact['comments'] ?? ''
                ]);
            }
        }
    }
    public function saveTag($data)
    {
        try {
            if (!empty($data['id'])) {
                // Update
                $stmt = $this->conn->prepare("
                UPDATE partags 
                SET tag = ?, comment = ?
                WHERE id = ?
            ");
                $stmt->execute([
                    $data['tag'],
                    $data['comment'] ?? '',
                    $data['id']
                ]);
            } else {
                // Insert
                $stmt = $this->conn->prepare("
                INSERT INTO partags (tag, comment) 
                VALUES (?, ?)
            ");
                $stmt->execute([
                    $data['tag'],
                    $data['comment'] ?? ''
                ]);
            }
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteTag($id)
    {
        try {
            // Check if tag is in use
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM partagmap WHERE idtag = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return ['success' => false, 'error' => 'Tag is in use and cannot be deleted'];
            }

            $stmt = $this->conn->prepare("DELETE FROM partags WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}


