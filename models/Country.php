<?php
class Country {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare(
            "SELECT * FROM countries ORDER BY name LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM countries WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO countries (name, code, description, visa_required) 
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['name'],
            $data['code'],
            $data['description'] ?? null,
            $data['visa_required'] ?? 0
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
            "UPDATE countries SET " . implode(', ', $fields) . " WHERE id = ?"
        );
        return $stmt->execute($values);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM countries WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function search($keyword) {
        $stmt = $this->db->prepare(
            "SELECT * FROM countries 
             WHERE name LIKE ? OR code LIKE ? OR description LIKE ?
             ORDER BY name"
        );
        $searchTerm = "%$keyword%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>