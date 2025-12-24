<?php

echo "<h1>Тестирование API Комфорт-отдых</h1>";

$baseUrl = 'http://localhost' . dirname($_SERVER['PHP_SELF']);

$endpoints = [
    'Здоровье системы' => '/api/health',
    'Все страны' => '/api/countries',
    'Активные туры' => '/api/tours?active=1',
    'Статистика' => '/api/statistics'
];

foreach ($endpoints as $name => $endpoint) {
    echo "<h3>{$name}</h3>";
    echo "<p>URL: <a href='{$baseUrl}{$endpoint}' target='_blank'>{$endpoint}</a></p>";
    
    $url = $baseUrl . $endpoint;
    $response = @file_get_contents($url);
    
    if ($response === FALSE) {
        echo "<p style='color: red;'>Ошибка при запросе к API</p>";
        echo "<p>Проверьте, что сервер запущен и файлы на месте.</p>";
    } else {
        $data = json_decode($response, true);
        echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    }
    echo "<hr>";
}


echo "<h3>Проверка базы данных</h3>";
if (file_exists('komfort_otdyh.db')) {
    $size = filesize('komfort_otdyh.db');
    echo "<p>База данных найдена. Размер: " . number_format($size) . " байт</p>";
    
    if ($size > 0) {
        echo "<p style='color: green;'>✓ База данных инициализирована корректно</p>";
    } else {
        echo "<p style='color: orange;'>⚠ База данных пуста</p>";
    }
} else {
    echo "<p style='color: red;'>✗ База данных не найдена</p>";
    echo "<p>Запустите create_database.php для создания базы данных</p>";
}

echo "<h3>Структура проекта</h3>";
echo "<ul>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        if (is_dir($file)) {
            echo "<li><strong>[Папка]</strong> {$file}/</li>";
        } else {
            echo "<li>{$file}</li>";
        }
    }
}
echo "</ul>";
?>