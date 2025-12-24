<?php
class Database {
    private $pdo;
    private static $instance = null;

    private function __construct() {
        try {
            $this->pdo = new PDO('sqlite:' . __DIR__ . '/komfort_otdyh.db');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTables();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function createTables() {
        $queries = [
            // Таблица стран
            "CREATE TABLE IF NOT EXISTS countries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                code TEXT NOT NULL UNIQUE,
                description TEXT,
                visa_required INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",

            // Таблица клиентов
            "CREATE TABLE IF NOT EXISTS clients (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                passport_number TEXT NOT NULL UNIQUE,
                phone TEXT,
                email TEXT,
                date_of_birth DATE,
                registration_date DATETIME DEFAULT CURRENT_TIMESTAMP
            )",

            // Таблица туров
            "CREATE TABLE IF NOT EXISTS tours (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                country_id INTEGER NOT NULL,
                description TEXT,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                price REAL NOT NULL,
                max_participants INTEGER DEFAULT 20,
                current_participants INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
            )",

            // Таблица бронирований
            "CREATE TABLE IF NOT EXISTS bookings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER NOT NULL,
                tour_id INTEGER NOT NULL,
                booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                status TEXT DEFAULT 'pending',
                participants_count INTEGER DEFAULT 1,
                total_price REAL NOT NULL,
                notes TEXT,
                FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
                FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE,
                UNIQUE(client_id, tour_id) -- один клиент не может бронировать один тур дважды
            )",

            // Создание индексов
            "CREATE INDEX IF NOT EXISTS idx_clients_passport ON clients(passport_number)",
            "CREATE INDEX IF NOT EXISTS idx_tours_dates ON tours(start_date, end_date)",
            "CREATE INDEX IF NOT EXISTS idx_tours_active ON tours(is_active)",
            "CREATE INDEX IF NOT EXISTS idx_bookings_status ON bookings(status)",
            "CREATE INDEX IF NOT EXISTS idx_bookings_client ON bookings(client_id)",
            "CREATE INDEX IF NOT EXISTS idx_bookings_tour ON bookings(tour_id)"
        ];

        foreach ($queries as $query) {
            $this->pdo->exec($query);
        }


        $this->seedInitialData();
    }

    private function seedInitialData() {
        // Проверяем, есть ли страны
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM countries");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            // Добавляем страны
            $countries = [
                ['Турция', 'TR', 'Страна на стыке Европы и Азии', 0],
                ['Египет', 'EG', 'Страна пирамид и Красного моря', 1],
                ['Испания', 'ES', 'Солнечная страна фламенко', 1],
                ['Тайланд', 'TH', 'Страна улыбок и храмов', 1],
                ['ОАЭ', 'AE', 'Современные эмираты', 0]
            ];

            $stmt = $this->pdo->prepare(
                "INSERT INTO countries (name, code, description, visa_required) VALUES (?, ?, ?, ?)"
            );

            foreach ($countries as $country) {
                $stmt->execute($country);
            }
        }
    }
}
?>