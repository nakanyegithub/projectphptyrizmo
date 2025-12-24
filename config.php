<?php

define('APP_NAME', 'Комфорт-Отдых');
define('APP_VERSION', '1.0.0');
define('DB_FILE', __DIR__ . '/komfort_otdyh.db');

define('API_PREFIX', '/api');
define('DEFAULT_PAGE_SIZE', 10);
define('MAX_PAGE_SIZE', 100);


$allowedOrigins = [
    'http://localhost:3000',
    'http://localhost:8080',
    'https://komfort-otdyh.ru'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');


if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}
?>