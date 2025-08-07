<?php

/**
 * Веб-версия тестового скрипта для Symfony API
 * Доступ: http://localhost:8080/test_api_web.php
 */

header('Content-Type: text/html; charset=utf-8');

class ApiTester
{
    private string $baseUrl = 'http://nginx';
    private array $results = [];

    public function runTests(): string
    {
        $output = "<h1>🧪 Тестирование Symfony API</h1>\n";

        $output .= $this->testHomePage();
        $output .= $this->testTestEndpoint();
        $output .= $this->testGetUsers();
        $output .= $this->testCreateUser();
        $output .= $this->testSystemInfo();
        $output .= $this->testHealthCheck();

        $output .= $this->printResults();
        return $output;
    }

    private function testHomePage(): string
    {
        $output = "<h3>📋 Тестируем главную страницу...</h3>\n";
        $response = $this->makeRequest('GET', '/');
        
        if ($response && isset($response['status']) && $response['status'] === 'success' && 
            isset($response['data']['version'])) {
            $output .= "<p style='color: green;'>✅ Главная страница работает</p>\n";
            $this->results['home'] = 'PASS';
        } else {
            $output .= "<p style='color: red;'>❌ Главная страница не работает</p>\n";
            if (isset($response['error']) && !empty($response['error'])) {
                $output .= "<p>Ошибка: " . htmlspecialchars($response['error']) . "</p>\n";
            }
            if ($response) {
                $output .= "<p>Ответ: " . htmlspecialchars(json_encode($response)) . "</p>\n";
            }
            $this->results['home'] = 'FAIL';
        }
        return $output;
    }

    private function testTestEndpoint(): string
    {
        $output = "<h3>📋 Тестируем /api/test...</h3>\n";
        $response = $this->makeRequest('GET', '/api/test');
        
        if ($response && isset($response['status']) && $response['status'] === 'success' && 
            isset($response['data']['id'])) {
            $output .= "<p style='color: green;'>✅ Тестовый эндпоинт работает</p>\n";
            $this->results['test'] = 'PASS';
        } else {
            $output .= "<p style='color: red;'>❌ Тестовый эндпоинт не работает</p>\n";
            if (isset($response['error']) && !empty($response['error'])) {
                $output .= "<p>Ошибка: " . htmlspecialchars($response['error']) . "</p>\n";
            }
            $this->results['test'] = 'FAIL';
        }
        return $output;
    }

    private function testGetUsers(): string
    {
        $output = "<h3>📋 Тестируем получение пользователей...</h3>\n";
        $response = $this->makeRequest('GET', '/api/users');
        
        if ($response && isset($response['status']) && $response['status'] === 'success' && 
            isset($response['data']['users']) && count($response['data']['users']) > 0) {
            $output .= "<p style='color: green;'>✅ Получение пользователей работает</p>\n";
            $this->results['users'] = 'PASS';
        } else {
            $output .= "<p style='color: red;'>❌ Получение пользователей не работает</p>\n";
            if (isset($response['error']) && !empty($response['error'])) {
                $output .= "<p>Ошибка: " . htmlspecialchars($response['error']) . "</p>\n";
            }
            $this->results['users'] = 'FAIL';
        }
        return $output;
    }

    private function testCreateUser(): string
    {
        $output = "<h3>📋 Тестируем создание пользователя...</h3>\n";
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'role' => 'user'
        ];
        
        $response = $this->makeRequest('POST', '/api/users', $userData);
        
        if ($response && isset($response['status']) && $response['status'] === 'success' && 
            isset($response['data']['user'])) {
            $output .= "<p style='color: green;'>✅ Создание пользователя работает</p>\n";
            $this->results['create_user'] = 'PASS';
        } else {
            $output .= "<p style='color: red;'>❌ Создание пользователя не работает</p>\n";
            if (isset($response['error']) && !empty($response['error'])) {
                $output .= "<p>Ошибка: " . htmlspecialchars($response['error']) . "</p>\n";
            }
            $this->results['create_user'] = 'FAIL';
        }
        return $output;
    }

    private function testSystemInfo(): string
    {
        $output = "<h3>📋 Тестируем информацию о системе...</h3>\n";
        $response = $this->makeRequest('GET', '/api/system/info');
        
        if ($response && isset($response['status']) && $response['status'] === 'success' && 
            isset($response['data']['system'])) {
            $output .= "<p style='color: green;'>✅ Информация о системе работает</p>\n";
            $this->results['system_info'] = 'PASS';
        } else {
            $output .= "<p style='color: red;'>❌ Информация о системе не работает</p>\n";
            if (isset($response['error']) && !empty($response['error'])) {
                $output .= "<p>Ошибка: " . htmlspecialchars($response['error']) . "</p>\n";
            }
            $this->results['system_info'] = 'FAIL';
        }
        return $output;
    }

    private function testHealthCheck(): string
    {
        $output = "<h3>📋 Тестируем проверку здоровья API...</h3>\n";
        $response = $this->makeRequest('GET', '/api/health');
        
        if ($response && isset($response['status']) && $response['status'] === 'success' && 
            isset($response['data']['health_status']) && $response['data']['health_status'] === 'healthy') {
            $output .= "<p style='color: green;'>✅ Проверка здоровья API работает</p>\n";
            $this->results['health'] = 'PASS';
        } else {
            $output .= "<p style='color: red;'>❌ Проверка здоровья API не работает</p>\n";
            if (isset($response['error']) && !empty($response['error'])) {
                $output .= "<p>Ошибка: " . htmlspecialchars($response['error']) . "</p>\n";
            }
            $this->results['health'] = 'FAIL';
        }
        return $output;
    }

    private function makeRequest(string $method, string $endpoint, ?array $data = null): ?array
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen(json_encode($data))
                ]);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($response === false) {
            return null;
        }
        
        $decoded = json_decode($response, true);
        return $decoded ?: null;
    }

    private function printResults(): string
    {
        $output = "<h2>📊 РЕЗУЛЬТАТЫ ТЕСТИРОВАНИЯ</h2>\n";
        
        $passed = 0;
        $total = count($this->results);
        
        foreach ($this->results as $test => $result) {
            $status = $result === 'PASS' ? '✅' : '❌';
            $color = $result === 'PASS' ? 'green' : 'red';
            $output .= "<p style='color: $color;'>$status $test: $result</p>\n";
            if ($result === 'PASS') $passed++;
        }
        
        $output .= "<hr>\n";
        $output .= "<p><strong>Итого: $passed/$total тестов прошли успешно</strong></p>\n";
        
        if ($passed === $total) {
            $output .= "<p style='color: green; font-weight: bold;'>🎉 Все тесты прошли успешно! API работает корректно.</p>\n";
        } else {
            $output .= "<p style='color: orange; font-weight: bold;'>⚠️ Некоторые тесты не прошли. Проверьте настройки API.</p>\n";
        }
        
        return $output;
    }
}

// Запуск тестов
$tester = new ApiTester();
echo $tester->runTests(); 