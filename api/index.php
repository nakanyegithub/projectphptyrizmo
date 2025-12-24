<?php
require_once __DIR__ . '/../create_database.php';
require_once __DIR__ . '/../models/Country.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Booking.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обработка OPTIONS запросов
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Получение пути запроса
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Удаление префикса /api если есть
$requestUri = str_replace('/api', '', $requestUri);
$pathSegments = explode('/', trim($requestUri, '/'));

// Основной путь
$resource = $pathSegments[0] ?? '';
$id = $pathSegments[1] ?? null;

// Обработка JSON входных данных
$inputData = json_decode(file_get_contents('php://input'), true) ?? [];

// Функция для отправки ответа
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// Функция для отправки ошибки
function sendError($message, $statusCode = 400) {
    sendResponse(['error' => $message], $statusCode);
}

// Роутинг
try {
    switch ($resource) {
        case 'countries':
            $countryModel = new Country();
            
            switch ($requestMethod) {
                case 'GET':
                    if ($id) {
                        $country = $countryModel->getById($id);
                        if ($country) {
                            sendResponse($country);
                        } else {
                            sendError('Страна не найдена', 404);
                        }
                    } else {
                        $page = $_GET['page'] ?? 1;
                        $limit = $_GET['limit'] ?? 10;
                        $keyword = $_GET['search'] ?? null;
                        
                        if ($keyword) {
                            $countries = $countryModel->search($keyword);
                        } else {
                            $countries = $countryModel->getAll($page, $limit);
                        }
                        sendResponse($countries);
                    }
                    break;
                    
                case 'POST':
                    if (empty($inputData['name']) || empty($inputData['code'])) {
                        sendError('Требуются поля: name, code');
                    }
                    
                    if ($countryModel->create($inputData)) {
                        sendResponse(['message' => 'Страна создана'], 201);
                    } else {
                        sendError('Ошибка при создании страны');
                    }
                    break;
                    
                case 'PUT':
                    if (!$id) sendError('Требуется ID страны');
                    if ($countryModel->update($id, $inputData)) {
                        sendResponse(['message' => 'Страна обновлена']);
                    } else {
                        sendError('Ошибка при обновлении страны');
                    }
                    break;
                    
                case 'DELETE':
                    if (!$id) sendError('Требуется ID страны');
                    if ($countryModel->delete($id)) {
                        sendResponse(['message' => 'Страна удалена']);
                    } else {
                        sendError('Ошибка при удалении страны');
                    }
                    break;
                    
                default:
                    sendError('Метод не поддерживается', 405);
            }
            break;
            
        case 'clients':
            $clientModel = new Client();
            
            switch ($requestMethod) {
                case 'GET':
                    if ($id) {
                        $client = $clientModel->getById($id);
                        if ($client) {
                            if (isset($_GET['with_bookings']) && $_GET['with_bookings'] == '1') {
                                $client['bookings'] = $clientModel->getBookings($id);
                            }
                            sendResponse($client);
                        } else {
                            sendError('Клиент не найден', 404);
                        }
                    } else {
                        $page = $_GET['page'] ?? 1;
                        $limit = $_GET['limit'] ?? 10;
                        $clients = $clientModel->getAll($page, $limit);
                        sendResponse($clients);
                    }
                    break;
                    
                case 'POST':
                    $required = ['first_name', 'last_name', 'passport_number'];
                    foreach ($required as $field) {
                        if (empty($inputData[$field])) {
                            sendError("Требуется поле: $field");
                        }
                    }
                    
                    if ($clientModel->create($inputData)) {
                        sendResponse(['message' => 'Клиент создан'], 201);
                    } else {
                        sendError('Ошибка при создании клиента');
                    }
                    break;
                    
                case 'PUT':
                    if (!$id) sendError('Требуется ID клиента');
                    if ($clientModel->update($id, $inputData)) {
                        sendResponse(['message' => 'Клиент обновлен']);
                    } else {
                        sendError('Ошибка при обновлении клиента');
                    }
                    break;
                    
                case 'DELETE':
                    if (!$id) sendError('Требуется ID клиента');
                    if ($clientModel->delete($id)) {
                        sendResponse(['message' => 'Клиент удален']);
                    } else {
                        sendError('Ошибка при удалении клиента');
                    }
                    break;
                    
                default:
                    sendError('Метод не поддерживается', 405);
            }
            break;
            
        case 'tours':
            $tourModel = new Tour();
            
            switch ($requestMethod) {
                case 'GET':
                    if ($id) {
                        $tour = $tourModel->getById($id);
                        if ($tour) {
                            if (isset($_GET['with_participants']) && $_GET['with_participants'] == '1') {
                                $tour['participants'] = $tourModel->getParticipants($id);
                            }
                            sendResponse($tour);
                        } else {
                            sendError('Тур не найден', 404);
                        }
                    } else {
                        $page = $_GET['page'] ?? 1;
                        $limit = $_GET['limit'] ?? 10;
                        $activeOnly = isset($_GET['active']) && $_GET['active'] == '1';
                        
                        // Если есть фильтры поиска
                        if (!empty($_GET['country_id']) || !empty($_GET['start_date']) || 
                            !empty($_GET['end_date']) || !empty($_GET['max_price'])) {
                            
                            $filters = array_filter([
                                'country_id' => $_GET['country_id'] ?? null,
                                'start_date' => $_GET['start_date'] ?? null,
                                'end_date' => $_GET['end_date'] ?? null,
                                'max_price' => $_GET['max_price'] ?? null,
                                'is_active' => $activeOnly ? 1 : null
                            ]);
                            
                            $tours = $tourModel->search($filters);
                        } else {
                            $tours = $tourModel->getAll($page, $limit, $activeOnly);
                        }
                        sendResponse($tours);
                    }
                    break;
                    
                case 'POST':
                    $required = ['title', 'country_id', 'start_date', 'end_date', 'price'];
                    foreach ($required as $field) {
                        if (empty($inputData[$field])) {
                            sendError("Требуется поле: $field");
                        }
                    }
                    
                    if ($tourModel->create($inputData)) {
                        sendResponse(['message' => 'Тур создан'], 201);
                    } else {
                        sendError('Ошибка при создании тура');
                    }
                    break;
                    
                case 'PUT':
                    if (!$id) sendError('Требуется ID тура');
                    if ($tourModel->update($id, $inputData)) {
                        sendResponse(['message' => 'Тур обновлен']);
                    } else {
                        sendError('Ошибка при обновлении тура');
                    }
                    break;
                    
                case 'DELETE':
                    if (!$id) sendError('Требуется ID тура');
                    if ($tourModel->delete($id)) {
                        sendResponse(['message' => 'Тур удален']);
                    } else {
                        sendError('Ошибка при удалении тура');
                    }
                    break;
                    
                default:
                    sendError('Метод не поддерживается', 405);
            }
            break;
            
        case 'bookings':
            $bookingModel = new Booking();
            
            switch ($requestMethod) {
                case 'GET':
                    if ($id) {
                        $booking = $bookingModel->getById($id);
                        if ($booking) {
                            sendResponse($booking);
                        } else {
                            sendError('Бронирование не найдено', 404);
                        }
                    } else {
                        $page = $_GET['page'] ?? 1;
                        $limit = $_GET['limit'] ?? 10;
                        $status = $_GET['status'] ?? null;
                        $bookings = $bookingModel->getAll($page, $limit, $status);
                        sendResponse($bookings);
                    }
                    break;
                    
                case 'POST':
                    $required = ['client_id', 'tour_id'];
                    foreach ($required as $field) {
                        if (empty($inputData[$field])) {
                            sendError("Требуется поле: $field");
                        }
                    }
                    
                    try {
                        $bookingId = $bookingModel->create($inputData);
                        if ($bookingId) {
                            sendResponse([
                                'message' => 'Бронирование создано',
                                'booking_id' => $bookingId
                            ], 201);
                        } else {
                            sendError('Ошибка при создании бронирования');
                        }
                    } catch (Exception $e) {
                        sendError($e->getMessage());
                    }
                    break;
                    
                case 'PUT':
                    if (!$id) sendError('Требуется ID бронирования');
                    
                    if (isset($inputData['status'])) {
                        // Обновление статуса
                        if ($bookingModel->updateStatus($id, $inputData['status'])) {
                            sendResponse(['message' => 'Статус бронирования обновлен']);
                        } else {
                            sendError('Ошибка при обновлении статуса');
                        }
                    } else {
                        sendError('Требуется поле status для обновления');
                    }
                    break;
                    
                case 'DELETE':
                    if (!$id) sendError('Требуется ID бронирования');
                    if ($bookingModel->delete($id)) {
                        sendResponse(['message' => 'Бронирование удалено']);
                    } else {
                        sendError('Ошибка при удалении бронирования');
                    }
                    break;
                    
                default:
                    sendError('Метод не поддерживается', 405);
            }
            break;
            
        case 'statistics':
            if ($requestMethod == 'GET') {
                $bookingModel = new Booking();
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                
                $stats = $bookingModel->getStatistics($startDate, $endDate);
                sendResponse($stats);
            } else {
                sendError('Метод не поддерживается', 405);
            }
            break;
            
        case 'health':
            if ($requestMethod == 'GET') {
                sendResponse([
                    'status' => 'ok',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'service' => 'Комфорт-Отдых API'
                ]);
            }
            break;
            
        default:
            sendError('Ресурс не найден', 404);
    }
    
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
?>