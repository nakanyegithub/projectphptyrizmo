<?php
class Tour {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($page = 1, $limit = 10, $activeOnly = false) {
        $offset = ($page - 1) * $limit;
        $where = $activeOnly ? "WHERE is_active = 1" : "";
        
        $stmt = $this->db->prepare(
            "SELECT t.*, c.name as country_name, c.code as country_code
             FROM tours t
             JOIN countries c ON t.country_id = c.id
             $where
             ORDER BY t.start_date LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT t.*, c.name as country_name, c.code as country_code
             FROM tours t
             JOIN countries c ON t.country_id = c.id
             WHERE t.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO tours (title, country_id, description, start_date, end_date, 
                               price, max_participants, is_active) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['title'],
            $data['country_id'],
            $data['description'] ?? null,
            $data['start_date'],
            $data['end_date'],
            $data['price'],
            $data['max_participants'] ?? 20,
            $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        
        $stmt = $this->db->prepare(
            "UPDATE tours SET " . implode(', ', $fields) . " WHERE id = ?"
        );
        return $stmt->execute($values);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM tours WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function search($filters) {
        $where = [];
        $params = [];
        
        if (!empty($filters['country_id'])) {
            $where[] = "t.country_id = ?";
            $params[] = $filters['country_id'];
        }
        
        if (!empty($filters['start_date'])) {
            $where[] = "t.start_date >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $where[] = "t.end_date <= ?";
            $params[] = $filters['end_date'];
        }
        
        if (!empty($filters['max_price'])) {
            $where[] = "t.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (isset($filters['is_active'])) {
            $where[] = "t.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $whereClause = $where ? "WHERE " . implode(' AND ', $where) : "";
        
        $stmt = $this->db->prepare(
            "SELECT t.*, c.name as country_name, c.code as country_code
             FROM tours t
             JOIN countries c ON t.country_id = c.id
             $whereClause
             ORDER BY t.start_date"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getParticipants($tourId) {
        $stmt = $this->db->prepare(
            "SELECT c.*, b.participants_count, b.status
             FROM bookings b
             JOIN clients c ON b.client_id = c.id
             WHERE b.tour_id = ? AND b.status != 'cancelled'"
        );
        $stmt->execute([$tourId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateParticipantsCount($tourId) {
        $stmt = $this->db->prepare(
            "UPDATE tours SET current_participants = (
                SELECT COALESCE(SUM(participants_count), 0)
                FROM bookings
                WHERE tour_id = ? AND status NOT IN ('cancelled', 'pending')
            ) WHERE id = ?"
        );
        return $stmt->execute([$tourId, $tourId]);
    }
}
?>