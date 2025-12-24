<?php
class Client {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare(
            "SELECT * FROM clients ORDER BY last_name, first_name LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO clients (first_name, last_name, passport_number, phone, email, date_of_birth) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['passport_number'],
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['date_of_birth'] ?? null
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
            "UPDATE clients SET " . implode(', ', $fields) . " WHERE id = ?"
        );
        return $stmt->execute($values);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM clients WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getBookings($clientId) {
        $stmt = $this->db->prepare(
            "SELECT b.*, t.title as tour_title, c.name as country_name
             FROM bookings b
             JOIN tours t ON b.tour_id = t.id
             JOIN countries c ON t.country_id = c.id
             WHERE b.client_id = ?
             ORDER BY b.booking_date DESC"
        );
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>