<?php
echo "<h1>🧪 Тестирование API Комфорт-отдых</h1>";


$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST']; 
$baseUrl = $protocol . '://' . $host . '/';


echo "<p><strong>Базовый URL:</strong> {$baseUrl}</p>";
echo "<p><strong>Текущая папка:</strong> " . __DIR__ . "</p>";

$endpoints = [
    'Здоровье системы' => 'api/health',
    'Все страны' => 'api/countries',
    'Активные туры' => 'api/tours?active=1',
    'Статистика' => 'api/statistics',
    'Главная страница' => ''  
];

foreach ($endpoints as $name => $endpoint) {
    echo "<h3>🔍 {$name}</h3>";
    
    $fullUrl = $baseUrl . $endpoint;
    echo "<p><strong>URL:</strong> <a href='{$fullUrl}' target='_blank'>{$endpoint}</a></p>";
    echo "<p><strong>Полный URL:</strong> {$fullUrl}</p>";
    
    
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === FALSE || $httpCode >= 400) {
        echo "<p style='color: red;'>❌ Ошибка: HTTP код {$httpCode}</p>";
        
       
        $context = stream_context_create([
            'http' => ['timeout' => 3]
        ]);
        $altResponse = @file_get_contents($fullUrl, false, $context);
        
        if ($altResponse === FALSE) {
            echo "<p style='color: red;'>Детали: ";
            $error = error_get_last();
            echo $error['message'] ?? 'Неизвестная ошибка';
            echo "</p>";
            
           
            echo "<p><strong>Прямая проверка:</strong> ";
            if ($endpoint === 'api/health' && file_exists('api/index.php')) {
                echo "Файл API существует<br>";

                $_SERVER['REQUEST_URI'] = '/' . $endpoint;
                ob_start();
                include 'api/index.php';
                $localResponse = ob_get_clean();
                if (!empty($localResponse)) {
                    echo "✅ API отвечает локально<br>";
                    echo "<details><summary>Ответ:</summary>";
                    echo "<pre>" . htmlspecialchars($localResponse) . "</pre>";
                    echo "</details>";
                }
            }
            echo "</p>";
        } else {
            echo "<p style='color: green;'>✅ Ответ получен через file_get_contents</p>";
            $data = json_decode($altResponse, true);
            echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
        }
    } else {
        echo "<p style='color: green;'>✅ HTTP код: {$httpCode}</p>";
        $data = json_decode($response, true);
        echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    }
    echo "<hr>";
}


echo "<h3>📁 Проверка файлов проекта</h3>";

$requiredFiles = [
    'index.php' => 'Главная страница',
    'api/index.php' => 'API контроллер',
    'create_database.php' => 'Инициализация БД',
    'models/Country.php' => 'Модель стран',
    'models/Client.php' => 'Модель клиентов',
    'models/Tour.php' => 'Модель туров',
    'models/Booking.php' => 'Модель бронирований'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ {$description}: {$file}</p>";
    } else {
        echo "<p style='color: red;'>❌ {$description}: {$file} - не найден</p>";
    }
}

echo "<h3>🗄️ Проверка базы данных</h3>";
if (file_exists('komfort_otdyh.db')) {
    $size = filesize('komfort_otdyh.db');
    echo "<p>✅ Файл базы данных найден</p>";
    echo "<p>📏 Размер: " . number_format($size) . " байт</p>";
    
    if ($size > 1000) {
        echo "<p style='color: green;'>✅ База данных инициализирована</p>";
        

        try {
            $db = new SQLite3('komfort_otdyh.db');
            $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
            
            echo "<p><strong>Таблицы в БД:</strong></p><ul>";
            while ($table = $tables->fetchArray(SQLITE3_ASSOC)) {
                $count = $db->querySingle("SELECT COUNT(*) FROM " . $table['name']);
                echo "<li>{$table['name']} - {$count} записей</li>";
            }
            echo "</ul>";
            $db->close();
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠ Не удалось прочитать БД: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ База данных пуста или повреждена</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Файл базы данных не найден</p>";
    echo "<p><a href='create_database.php'>Создать базу данных</a></p>";
}

echo "<h3>🌐 Информация о сервере</h3>";
echo "<ul>";
echo "<li>PHP версия: " . phpversion() . "</li>";
echo "<li>Сервер: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Порт: " . $_SERVER['SERVER_PORT'] . "</li>";
echo "<li>HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "</li>";
echo "<li>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</li>";
echo "</ul>";


echo "<h3>🔧 Прямой тест API</h3>";
echo "<p><button onclick='runDirectTest()'>Запустить прямой тест API</button></p>";
echo "<div id='directTestResult'></div>";

echo "<h3>🔗 Быстрые ссылки для проверки</h3>";
echo "<ul>";
echo "<li><a href='{$baseUrl}'>Главная страница</a></li>";
echo "<li><a href='{$baseUrl}api_test.php'>Расширенный тест</a></li>";
echo "<li><a href='{$baseUrl}create_database.php'>Пересоздать БД</a></li>";
echo "</ul>";
?>

<script>
function runDirectTest() {
    const resultDiv = document.getElementById('directTestResult');
    resultDiv.innerHTML = 'Выполняю тест...';
    
    fetch('<?php echo $baseUrl; ?>api/index.php/health')
        .then(response => {
            resultDiv.innerHTML = 'Статус: ' + response.status + '<br>';
            return response.text();
        })
        .then(data => {
            resultDiv.innerHTML += 'Ответ: <pre>' + data + '</pre>';
        })
        .catch(error => {
            resultDiv.innerHTML = 'Ошибка: ' + error;
        });
}
</script>