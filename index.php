<?php
header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Комфорт-отдых - Туристическая компания</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 50px;
            color: white;
        }
        
        .header h1 {
            font-size: 3.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-bottom: 40px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #4a5568;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            font-size: 2rem;
        }
        
        .api-endpoints {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .endpoint-card {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .endpoint-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .method {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9rem;
            margin-right: 10px;
        }
        
        .method.get { background: #48bb78; color: white; }
        .method.post { background: #4299e1; color: white; }
        .method.put { background: #ed8936; color: white; }
        .method.delete { background: #f56565; color: white; }
        
        .path {
            font-family: 'Courier New', monospace;
            background: #2d3748;
            color: #68d391;
            padding: 8px 12px;
            border-radius: 5px;
            display: block;
            margin: 10px 0;
            word-break: break-all;
        }
        
        .description {
            color: #4a5568;
            font-size: 0.95rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s;
            border: 2px solid white;
        }
        
        .btn:hover {
            background: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background: #48bb78;
            color: white;
            border-color: #48bb78;
        }
        
        .btn-primary:hover {
            background: transparent;
            color: #48bb78;
        }
        
        .status {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f0fff4;
            border-radius: 10px;
            border-left: 4px solid #48bb78;
        }
        
        .status h3 {
            color: #2f855a;
            margin-bottom: 10px;
        }
        
        .examples {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
            border-left: 4px solid #4299e1;
        }
        
        .examples h3 {
            color: #2b6cb0;
            margin-bottom: 15px;
        }
        
        .code-block {
            background: #1a202c;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin: 15px 0;
        }
        
        .footer {
            text-align: center;
            color: white;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }
            
            .content {
                padding: 20px;
            }
            
            .api-endpoints {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Комфорт-отдых</h1>
            <p>Система управления туристической компанией</p>
        </div>
        
        <div class="content">
            <div class="section">
                <h2>О системе</h2>
                <p>Добро пожаловать в информационную систему туристической компании "Комфорт-отдых". 
                Данная система предназначена для управления странами, клиентами, турами и бронированиями.</p>
                
                <div class="status">
                    <h3>Статус системы</h3>
                    <p>Система работает корректно. API доступно по адресу: <strong>/api/</strong></p>
                </div>
            </div>
            
            <div class="section">
                <h2>Доступные API эндпоинты</h2>
                
                <div class="api-endpoints">
                    <!-- Страны -->
                    <div class="endpoint-card">
                        <span class="method get">GET</span>
                        <span class="method post">POST</span>
                        <span class="path">/api/countries</span>
                        <p class="description">Управление странами (получение списка, создание)</p>
                    </div>
                    
                    <div class="endpoint-card">
                        <span class="method get">GET</span>
                        <span class="method put">PUT</span>
                        <span class="method delete">DELETE</span>
                        <span class="path">/api/countries/{id}</span>
                        <p class="description">Управление конкретной страной</p>
                    </div>
                    
                    <!-- Клиенты -->
                    <div class="endpoint-card">
                        <span class="method get">GET</span>
                        <span class="method post">POST</span>
                        <span class="path">/api/clients</span>
                        <p class="description">Управление клиентами</p>
                    </div>
                    
                    <div class="endpoint-card">
                        <span class="method get">GET</span>
                        <span class="method put">PUT</span>
                        <span class="method delete">DELETE</span>
                        <span class="path">/api/clients/{id}</span>
                        <p class="description">Управление конкретным клиентом</p>
                    </div>
                    
                    <!-- Туры -->
                    <div class="endpoint-card">
                        <span class="method get">GET</span>
                        <span class="method post">POST</span>
                        <span class="path">/api/tours</span>
                        <p class="description">Управление турами</p>
                    </div>
                    
                    <div class="endpoint-card">
                        <span class="method get">GET</span>
                        <span class="method put">PUT</span>
                        <span class="method delete">DELETE</span>
                        <span class="path">/api/tours/{id}</span>
                        <p class="description">Управление конкретным туром</p>
                    </div>
                    
                    <!-- Бронирования -->
                    <div class="endpoint-card">
                        <span class="method get">GET</span>
                        <span class="method post">POST</span>
                        <span class="path">/api/bookings</span>
                        <p class="description">Управление бронированиями</p>
                    </div>
                    
                    <div class="endpoint-card">
                        <span class="method get">GET</span>
                        <span class="method put">PUT</span>
                        <span class="method delete">DELETE</span>
                        <span class="path">/api/bookings/{id}</span>
                        <p class="description">Управление конкретным бронированием</p>
                    </div>
                    
                    <!-- Дополнительные -->
                    <div class="endpoint-card">
                        <span class="method get">GET</span>
                        <span class="path">/api/statistics</span>
                        <p class="description">Получение статистики</p>
                    </div>
                    
                    <div class="endpoint-card">
                        <span class="method get">GET</span>
                        <span class="path">/api/health</span>
                        <p class="description">Проверка работоспособности системы</p>
                    </div>
                </div>
            </div>
            
            <div class="examples">
                <h3>Примеры использования API</h3>
                
                <h4>1. Получить все страны:</h4>
                <div class="code-block">
GET /api/countries<br>
<br>
Пример ответа:<br>
[<br>
  {<br>
    "id": 1,<br>
    "name": "Турция",<br>
    "code": "TR",<br>
    "description": "Страна на стыке Европы и Азии",<br>
    "visa_required": 0,<br>
    "created_at": "2024-01-15 10:30:00"<br>
  }<br>
]
                </div>
                
                <h4>2. Создать нового клиента:</h4>
                <div class="code-block">
POST /api/clients<br>
Content-Type: application/json<br>
<br>
{<br>
  "first_name": "Иван",<br>
  "last_name": "Петров",<br>
  "passport_number": "1234567890",<br>
  "phone": "+79991234567",<br>
  "email": "ivan@example.com",<br>
  "date_of_birth": "1990-05-15"<br>
}
                </div>
                
                <h4>3. Создать бронирование:</h4>
                <div class="code-block">
POST /api/bookings<br>
Content-Type: application/json<br>
<br>
{<br>
  "client_id": 1,<br>
  "tour_id": 1,<br>
  "participants_count": 2,<br>
  "notes": "Два взрослых"<br>
}
                </div>
            </div>
            
            <div class="section">
                <h2>Быстрые действия</h2>
                <div class="action-buttons">
                    <a href="/api/countries" class="btn" target="_blank">Просмотреть страны</a>
                    <a href="/api/clients" class="btn" target="_blank">Просмотреть клиентов</a>
                    <a href="/api/tours?active=1" class="btn" target="_blank">Активные туры</a>
                    <a href="/api/health" class="btn" target="_blank">Проверить систему</a>
                </div>
                
                <div class="action-buttons" style="margin-top: 20px;">
                    <a href="/api-docs.html" class="btn btn-primary">Подробная документация</a>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Туристическая компания "Комфорт-отдых" &copy; 2024</p>
            <p>Версия системы: 1.0.0</p>
        </div>
    </div>
    
    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/api/health')
                .then(response => response.json())
                .then(data => {
                    const statusElement = document.querySelector('.status p');
                    if (data.status === 'ok') {
                        statusElement.innerHTML = '✅ Система работает корректно. API доступно по адресу: <strong>/api/</strong>';
                    }
                })
                .catch(error => {
                    console.log('API проверка не удалась:', error);
                });
        });
    </script>
</body>
</html>