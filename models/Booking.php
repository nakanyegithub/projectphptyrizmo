<?php
class Booking {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($page = 1, $limit = 10, $status = null) {
        $offset = ($page - 1) * $limit;
        $where = $status ? "WHERE status = ?" : "";
        $params = $status ? [$status] : [];
        
        $stmt = $this->db->prepare(
            "SELECT b.*, 
                    c.first_name || ' ' || c.last_name as client_name,
                    t.title as tour_title,
                    co.name as country_name
             FROM bookings b
             JOIN clients c ON b.client_id = c.id
             JOIN tours t ON b.tour_id = t.id
             JOIN countries co ON t.country_id = co.id
             $where
             ORDER BY b.booking_date DESC
             LIMIT ? OFFSET ?"
        );
        
        if ($status) {
            $stmt->bindValue(1, $status);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT b.*, 
                    c.first_name, c.last_name, c.passport_number, c.phone, c.email,
                    t.title as tour_title, t.start_date, t.end_date, t.price,
                    co.name as country_name
             FROM bookings b
             JOIN clients c ON b.client_id = c.id
             JOIN tours t ON b.tour_id = t.id
             JOIN countries co ON t.country_id = co.id
             WHERE b.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $this->db->beginTransaction();
        
        try {
            // Проверяем доступность тура
            $tourStmt = $this->db->prepare(
                "SELECT max_participants, current_participants, price, is_active 
                 FROM tours WHERE id = ?"
            );
            $tourStmt->execute([$data['tour_id']]);
            $tour = $tourStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tour || $tour['is_active'] == 0) {
                throw new Exception("Тур недоступен для бронирования");
            }
            
            $available = $tour['max_participants'] - $tour['current_participants'];
            $participants = $data['participants_count'] ?? 1;
            
            if ($available < $participants) {
                throw new Exception("Недостаточно свободных мест");
            }
            
            // Создаем бронирование
            $stmt = $this->db->prepare(
                "INSERT INTO bookings (client_id, tour_id, participants_count, total_price, notes, status) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            
            $totalPrice = $tour['price'] * $participants;
            
            $success = $stmt->execute([
                $data['client_id'],
                $data['tour_id'],
                $participants,
                $totalPrice,
                $data['notes'] ?? null,
                $data['status'] ?? 'pending'
            ]);
            
            if ($success) {
                // Обновляем счетчик участников
                $tourModel = new Tour();
                $tourModel->updateParticipantsCount($data['tour_id']);
                
                $this->db->commit();
                return $this->db->lastInsertId();
            }
            
            $this->db->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateStatus($id, $status) {
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare(
                "UPDATE bookings SET status = ? WHERE id = ?"
            );
            $success = $stmt->execute([$status, $id]);
            
            if ($success) {
                // Обновляем счетчик участников в туре
                $booking = $this->getById($id);
                if ($booking) {
                    $tourModel = new Tour();
                    $tourModel->updateParticipantsCount($booking['tour_id']);
                }
                
                $this->db->commit();
                return true;
            }
            
            $this->db->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $this->db->beginTransaction();
        
        try {
            // Получаем информацию о бронировании перед удалением
            $booking = $this->getById($id);
            
            $stmt = $this->db->prepare("DELETE FROM bookings WHERE id = ?");
            $success = $stmt->execute([$id]);
            
            if ($success && $booking) {
                // Обновляем счетчик участников
                $tourModel = new Tour();
                $tourModel->updateParticipantsCount($booking['tour_id']);
                
                $this->db->commit();
                return true;
            }
            
            $this->db->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getStatistics($startDate = null, $endDate = null) {
        $where = [];
        $params = [];
        
        if ($startDate) {
            $where[] = "b.booking_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $where[] = "b.booking_date <= ?";
            $params[] = $endDate;
        }
        
        $whereClause = $where ? "WHERE " . implode(' AND ', $where) : "";
        
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                SUM(total_price) as total_revenue,
                SUM(CASE WHEN status = 'confirmed' THEN total_price ELSE 0 END) as confirmed_revenue,
                AVG(participants_count) as avg_participants
             FROM bookings b
             $whereClause"
        );
        
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>