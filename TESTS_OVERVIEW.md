# Обзор тестов в проекте Symfony API

## 📋 Общая информация

Проект имеет комплексную систему тестирования, которая покрывает все слои приложения согласно архитектуре Domain-Driven Design (DDD). Всего в проекте **12 тестовых файлов** с общим объемом более **2000 строк кода**.

## 🏗️ Структура тестов

```
tests/
├── Domain/                    # Тесты доменного слоя
│   ├── ValueObject/          # Value Objects (2 файла)
│   ├── Service/              # Domain Services (1 файл)
│   └── Factory/              # Entity Factories (1 файл)
├── Application/              # Тесты слоя приложения
│   ├── Handler/              # Application Handlers (1 файл)
│   └── Command/              # Console Commands (1 файл)
├── Infrastructure/           # Тесты инфраструктурного слоя
│   ├── Repository/           # Repository тесты (1 файл)
│   └── External/             # External API тесты (1 файл)
├── Presentation/             # Тесты презентационного слоя
│   └── Controller/           # API Controllers (1 файл)
├── Integration/              # Интеграционные тесты (1 файл)
└── Controller/               # Дополнительные контроллеры (1 файл)
```

## 🧪 Типы тестов

### 1. Unit тесты (Модульные тесты)

#### Domain/ValueObject/
**CurrencyTest.php** (128 строк)
- ✅ Создание валюты с валидным кодом
- ✅ Конвертация в верхний регистр
- ✅ Валидация пустого кода валюты
- ✅ Проверка неподдерживаемых валют
- ✅ Сравнение валют (equals)
- ✅ Получение списка поддерживаемых валют
- ✅ Data Provider для всех поддерживаемых валют
- ✅ Создание из строки (fromString)

**ExchangeRateTest.php** (151 строка)
- ✅ Создание курса валют
- ✅ Валидация значений курса
- ✅ Работа с временными метками
- ✅ Сравнение курсов валют
- ✅ Проверка граничных значений

#### Domain/Service/
**CurrencyPairServiceTest.php** (204 строки)
- ✅ Добавление новых пар валют
- ✅ Поиск существующих пар валют
- ✅ Валидация бизнес-правил
- ✅ Проверка уникальности пар
- ✅ Обработка дублирующихся пар

#### Domain/Factory/
**ExchangeRateEntityFactoryTest.php** (113 строк)
- ✅ Создание сущностей курсов валют
- ✅ Валидация входных данных
- ✅ Создание с различными параметрами

### 2. Application тесты (Тесты слоя приложения)

#### Application/Handler/
**GetExchangeRateHandlerTest.php** (149 строк)
- ✅ Получение текущих курсов валют
- ✅ Получение исторических курсов
- ✅ Обработка ошибок валидации
- ✅ Работа с различными валютами
- ✅ Проверка форматов ответов

#### Application/Command/
**AddCurrencyPairConsoleCommandTest.php** (115 строк)
- ✅ Успешное добавление пары валют
- ✅ Валидация входных данных
- ✅ Обработка ошибок валидации
- ✅ Проверка вывода команд
- ✅ Тестирование интерактивного режима

### 3. Infrastructure тесты (Тесты инфраструктурного слоя)

#### Infrastructure/Repository/
**ExchangeRateHistoryRepositoryTest.php** (170 строк)
- ✅ Сохранение и получение данных
- ✅ Поиск по критериям
- ✅ Работа с базой данных
- ✅ Фильтрация по датам
- ✅ Пагинация результатов

#### Infrastructure/External/
**FreeCurrencyApiServiceTest.php** (174 строки)
- ✅ Получение курсов валют из внешнего API
- ✅ Обработка ошибок API
- ✅ Проверка доступности API
- ✅ Валидация ответов API
- ✅ Обработка отсутствующего API ключа
- ✅ Тестирование различных сценариев ошибок

### 4. Presentation тесты (Тесты презентационного слоя)

#### Presentation/Controller/
**ExchangeRateControllerTest.php** (170 строк)
- ✅ Получение курсов валют через HTTP
- ✅ Обработка параметров запроса
- ✅ Валидация валют
- ✅ Возврат статистики
- ✅ Обработка ошибок валидации
- ✅ Проверка HTTP статусов
- ✅ Валидация JSON ответов

#### Controller/
**TestControllerTest.php** (126 строк)
- ✅ Базовые HTTP операции
- ✅ JSON ответы
- ✅ Проверка структуры ответов
- ✅ Тестирование различных эндпоинтов

### 5. Integration тесты (Интеграционные тесты)

#### Integration/
**ApplicationTest.php** (131 строка)
- ✅ Тестирование взаимодействия между компонентами
- ✅ End-to-end сценарии
- ✅ Проверка полного цикла обработки запросов
- ✅ Интеграция всех слоев приложения

## ⚙️ Конфигурация тестов

### PHPUnit конфигурация
- **phpunit.xml.dist** - основная конфигурация с настройками для разных типов тестов
- **phpunit.xml** - локальная конфигурация

### Test Suites
```xml
<testsuites>
    <testsuite name="Unit Tests">
        <directory>tests/Domain/ValueObject</directory>
        <directory>tests/Domain/Service</directory>
        <directory>tests/Application/Handler</directory>
    </testsuite>
    
    <testsuite name="Integration Tests">
        <directory>tests/Infrastructure/Repository</directory>
        <directory>tests/Infrastructure/External</directory>
        <directory>tests/Integration</directory>
    </testsuite>
    
    <testsuite name="API Tests">
        <directory>tests/Presentation/Controller</directory>
    </testsuite>
    
    <testsuite name="Command Tests">
        <directory>tests/Application/Command</directory>
    </testsuite>
</testsuites>
```

### Покрытие кода
```xml
<coverage processUncoveredFiles="true">
    <include>
        <directory suffix=".php">src/Domain</directory>
        <directory suffix=".php">src/Application</directory>
        <directory suffix=".php">src/Infrastructure</directory>
        <directory suffix=".php">src/Presentation</directory>
    </include>
</coverage>
```

## 🚀 Запуск тестов

### Все тесты
```bash
# Стандартный способ
php vendor/bin/phpunit

# Или через composer (опционально)
composer test
```

### По категориям
```bash
# Unit тесты
php vendor/bin/phpunit --testsuite="Unit Tests"

# Integration тесты
php vendor/bin/phpunit --testsuite="Integration Tests"

# API тесты
php vendor/bin/phpunit --testsuite="API Tests"

# Command тесты
php vendor/bin/phpunit --testsuite="Command Tests"
```

### Конкретные тесты
```bash
# Тест конкретного класса
php vendor/bin/phpunit tests/Domain/ValueObject/CurrencyTest.php

# Тест конкретного метода
php vendor/bin/phpunit --filter testMethodName tests/Domain/ValueObject/CurrencyTest.php
```

### С покрытием кода
```bash
# HTML отчет о покрытии
php vendor/bin/phpunit --coverage-html coverage/

# Текстовый отчет
php vendor/bin/phpunit --coverage-text

# Clover XML для CI
php vendor/bin/phpunit --coverage-clover coverage.xml
```

### Альтернативные команды через composer
```bash
# Все тесты
composer test

# По категориям
composer test:unit
composer test:integration
composer test:api
composer test:command

# С покрытием кода
composer test:coverage
```



## 🎯 Особенности тестирования

### 1. Использование Mock объектов
```php
// Создание mock для интерфейса
$repository = $this->createMock(CurrencyPairRepositoryInterface::class);

// Настройка поведения mock
$repository
    ->expects($this->once())
    ->method('findById')
    ->with(1)
    ->willReturn($currencyPair);
```

### 2. Data Providers
```php
/**
 * @test
 * @dataProvider supportedCurrenciesProvider
 */
public function it_accepts_all_supported_currencies(string $currencyCode): void
{
    $currency = new Currency($currencyCode);
    $this->assertEquals(strtoupper($currencyCode), $currency->getCode());
}

public function supportedCurrenciesProvider(): array
{
    return [
        ['USD'],
        ['EUR'],
        ['GBP'],
        ['JPY'],
    ];
}
```

### 3. Проверка исключений
```php
public function it_throws_exception_for_invalid_currency(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Неподдерживаемая валюта: XYZ');
    
    new Currency('XYZ');
}
```

### 4. Тестирование HTTP ответов
```php
public function it_returns_exchange_rate_for_valid_currencies(): void
{
    $result = $this->controller->getExchangeRate('USD', 'EUR', $request);
    
    $this->assertInstanceOf(JsonResponse::class, $result);
    $this->assertEquals(200, $result->getStatusCode());
    
    $data = json_decode($result->getContent(), true);
    $this->assertTrue($data['success']);
}
```

## 📊 Статистика покрытия

### Цели покрытия
- **Domain слой**: 100% покрытие Value Objects и Domain Services
- **Application слой**: Handlers и Commands
- **Infrastructure слой**: Repository и External API
- **Presentation слой**: Controllers

### Критические компоненты
- ✅ Value Objects - 100% покрытие
- ✅ Domain Services - 100% покрытие
- ✅ Application Handlers - полное покрытие
- ✅ Repository - полное покрытие
- ✅ External API - полное покрытие
- ✅ Controllers - полное покрытие

## 🛠️ Настройка тестового окружения

### База данных для тестов
```yaml
# config/packages/test/doctrine.yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
        driver: 'pdo_sqlite'
        memory: true
```

### Переменные окружения для тестов
```env
# .env.test
APP_ENV=test
DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"
MESSENGER_TRANSPORT_DSN=in-memory://
```

## 🔧 Лучшие практики

### 1. Именование тестов
```php
// Хорошо
public function it_creates_currency_with_valid_code(): void
public function it_throws_exception_for_invalid_currency(): void

// Плохо
public function testCurrency(): void
public function testException(): void
```

### 2. Структура теста (AAA Pattern)
```php
public function it_does_something(): void
{
    // Arrange - подготовка данных
    $currency = new Currency('USD');
    
    // Act - выполнение действия
    $result = $currency->getCode();
    
    // Assert - проверка результата
    $this->assertEquals('USD', $result);
}
```

### 3. Изоляция тестов
```php
protected function setUp(): void
{
    $this->repository = $this->createMock(RepositoryInterface::class);
    $this->service = new Service($this->repository);
}

protected function tearDown(): void
{
    // Очистка после каждого теста
}
```

## 🐛 Отладка тестов

### Включение отладки
```bash
# Подробный вывод
php vendor/bin/phpunit --verbose

# Остановка на первом провале
php vendor/bin/phpunit --stop-on-failure

# Фильтр по имени теста
php vendor/bin/phpunit --filter testMethodName

# Или через composer
composer test -- --verbose
composer test -- --stop-on-failure
```

### Логирование в тестах
```php
// Включение логирования для отладки
$this->logger->debug('Test debug info', ['data' => $result]);
```

## 📈 Continuous Integration

### GitHub Actions
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php vendor/bin/phpunit
```

## 🎯 Заключение

Система тестирования обеспечивает:

- **Надежность**: Все критические компоненты покрыты тестами
- **Качество**: Код соответствует стандартам и лучшим практикам
- **Поддержку**: Легко добавлять новые тесты и поддерживать существующие
- **Документацию**: Тесты служат живой документацией кода

### Рекомендации
1. Регулярно запускайте тесты
2. Поддерживайте высокое покрытие кода
3. Добавляйте тесты для новых функций
4. Используйте тесты для рефакторинга
5. Интегрируйте тесты в CI/CD pipeline

Регулярно запускайте тесты и поддерживайте высокое покрытие кода для обеспечения качества приложения.
