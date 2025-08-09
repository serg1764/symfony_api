# Currency Exchange Rate Tracker API

Приложение для трекинга валютных/обменных курсов валют без UI.

## 🚀 Функциональность

- ✅ Хранение обменных курсов (например USD -> EUR, EUR -> USD и т.д.)
- ✅ История значений обменных курсов (исторические данные)
- ✅ Добавление/удаление пар валют для трекинга через консольную команду
- ✅ Автоматическое обновление курсов каждую минуту
- ✅ JSON API эндпоинт для получения обменного курса
- ✅ Поддержка внешнего API (FreeCurrencyAPI)

- ✅ Веб-интерфейс для тестирования API (обновлен для текущего API)

## Архитектура

Проект построен с использованием:
- **DDD (Domain-Driven Design)** - четкое разделение на слои
- **SOLID принципы** - каждый класс имеет одну ответственность
- **Repository Pattern** - абстракция доступа к данным
- **Command/Query Separation** - разделение команд и запросов
- **Dependency Injection** - слабая связанность компонентов
- **Event-Driven Architecture** - события для слабой связанности
- **Message Queue Architecture** - две очереди для надежности и идемпотентности

### Архитектура очередей

Система использует две очереди для повышения надежности:

1. **async_fetch** - получение данных из внешнего API
   - Обрабатывает `FetchRateMessage`
   - Вызывает внешний API (FreeCurrencyAPI)
   - Отправляет `SaveRateMessage` во вторую очередь

2. **async_save** - сохранение данных в БД
   - Обрабатывает `SaveRateMessage`
   - Сохраняет курсы в отдельные таблицы для каждой пары
   - Обеспечивает идемпотентность операций

**Преимущества:**
- Разделение ответственности
- Идемпотентность (повторные попытки не создают дубли)
- Масштабируемость (можно масштабировать каждую очередь независимо)
- Надежность (ошибки в API не влияют на сохранение)
- Автоматические повторные попытки (до 3 раз с экспоненциальной задержкой)
- Сохранение неудачных сообщений в БД для анализа и восстановления

### Архитектура таблиц

Каждая валютная пара имеет свою отдельную таблицу:

- **`exchange_rate_usd_eur`** - история курсов USD/EUR
- **`exchange_rate_eur_usd`** - история курсов EUR/USD
- **`currency_pairs`** - список отслеживаемых пар
- **`doctrine_migration_versions`** - версии миграций Doctrine
- **`messenger_messages`** - неудачные сообщения для анализа и восстановления

**Примечание:** Очереди сообщений (`async_fetch` и `async_save`) используют RabbitMQ, а не базу данных.

**Преимущества разделения таблиц:**
- Лучшая производительность запросов
- Изоляция данных по парам валют
- Простота масштабирования
- Возможность независимой очистки старых данных

## Структура проекта

```
src/
├── Domain/                    # Доменный слой
│   ├── Entity/               # Сущности
│   │   ├── ExchangeRate/     # Сущности для каждой валютной пары
│   │   └── ExchangeRateRecord.php # Базовая сущность
│   ├── Factory/              # Фабрики
│   ├── Repository/           # Интерфейсы репозиториев
│   ├── Service/              # Доменные сервисы
│   ├── ValueObject/          # Value Objects
│   └── Event/                # Доменные события
├── Application/              # Прикладной слой
│   ├── Command/              # Консольные команды
│   ├── Handler/              # Обработчики команд/запросов
│   ├── Message/              # Сообщения для очередей
│   ├── EventSubscriber/      # Подписчики событий
│   └── DTO/                  # Data Transfer Objects
├── Infrastructure/           # Инфраструктурный слой
│   ├── Repository/           # Реализации репозиториев
│   ├── Service/              # Сервисы инфраструктуры
│   └── External/             # Внешние API
├── Presentation/             # Презентационный слой
│   ├── Controller/           # API контроллеры
│   └── Exception/            # Обработчики исключений
└── Controller/               # Тестовые контроллеры
    └── TestController.php    # Контроллер для тестирования API
```

## Установка и запуск

### 1. Клонирование и настройка

```bash
git clone git@github.com:serg1764/symfony_api.git
cd symfony_api
```

### 2. Запуск с Docker

```bash
# Запуск всех сервисов
docker-compose up -d

# Применение миграций
docker-compose exec php php bin/console doctrine:migrations:migrate
```

### 3. Настройка переменных окружения

**Важно:** Для работы с реальными данными необходимо получить API ключ от FreeCurrencyAPI:
1. Зарегистрируйтесь на https://freecurrencyapi.com/
2. Получите бесплатный API ключ
3. Добавьте ключ в `.env.local`

Создайте файл `.env.local`:

```env
# Database
DATABASE_URL="postgresql://symfony:symfony@postgres:5432/exchange?serverVersion=16&charset=utf8"

# Messenger Transport DSNs (две очереди)
MESSENGER_TRANSPORT_DSN_FETCH=amqp://guest:guest@rabbitmq:5672/%2f/fetch
MESSENGER_TRANSPORT_DSN_SAVE=amqp://guest:guest@rabbitmq:5672/%2f/save

# App
APP_ENV=dev
APP_SECRET=your-secret-key
APP_DEBUG=true

# External API (обязательно для продакшена)
FREECURRENCY_API_KEY=your-api-key

# API Service (FreeCurrencyApiService для продакшена, MockExchangeRateApiService для тестов)
EXCHANGE_RATE_API_SERVICE=App\Infrastructure\External\FreeCurrencyApiService
```

## Использование

### Консольные команды

#### Добавление пары валют для отслеживания

```bash
# Добавить пару USD/EUR
php bin/console app:currency-pair:add USD EUR

# Добавить пару EUR/USD
php bin/console app:currency-pair:add EUR USD

# Добавить пару GBP/USD
php bin/console app:currency-pair:add GBP USD

# Добавить пару USD/GBP
php bin/console app:currency-pair:add USD GBP
```

#### Планирование получения курсов валют

```bash
# Запланировать получение курсов для всех активных пар
php bin/console app:schedule-fetch-rates
```

#### Генерация сущности для новой валютной пары

```bash
# Создать сущность для пары USD/GBP
php bin/console app:generate-currency-pair-entity USD GBP
# Создать сущность для пары EUR/GBP
php bin/console app:generate-currency-pair-entity EUR GBP
```

Что делает команда (по шагам):
- **Валидирует валюты**: проверяет, что коды входят в поддерживаемые через `App\Domain\ValueObject\Currency`.
- **Формирует имена**: код пары/класс/таблица, например для `USD GBP` → пара `USDGBP`, класс `USDGBP`, таблица `exchange_rate_usd_gbp`.
- **Генерирует файл сущности**: создает `src/Domain/Entity/ExchangeRate/USDGBP.php`, класс наследует `ExchangeRateRecord`, добавлены атрибуты `#[ORM\Table(name: '...')]` и индекс по `timestamp`.
- **Создает директорию при необходимости** и **не перезаписывает** существующий файл.
- **Выводит дальнейшие шаги**:
  - добавить пару в `ExchangeRateEntityFactory::ENTITY_MAP`;
  - создать миграцию для новой таблицы;
  - обновить тесты.

Примечания:
- Для уже существующих пар команда выведет предупреждение и ничего не изменит.
- Если передана неподдерживаемая валюта, команда завершится ошибкой.

#### Обновление курсов валют

```bash
# Обновить все курсы моментально
php bin/console app:exchange-rates:update

# Обновить курсы и очистить старые записи - удалит все старше N дней
php bin/console app:exchange-rates:update --cleanup --days=30
```

#### Управление неудачными сообщениями

```bash
# Показать все неудачные сообщения
php bin/console messenger:failed:show

# Повторить обработку всех неудачных сообщений
php bin/console messenger:failed:retry

# Повторить обработку конкретного сообщения (по ID)
php bin/console messenger:failed:retry {id}

# Удалить все неудачные сообщения
php bin/console messenger:failed:remove

# Удалить конкретное неудачное сообщение (по ID)
php bin/console messenger:failed:remove {id}
```

#### Управление очередями сообщений

```bash
# Показать статистику очередей
php bin/console messenger:stats

# Запустить consumer для очереди async_fetch
php bin/console messenger:consume async_fetch

# Запустить consumer для очереди async_save
php bin/console messenger:consume async_save

# Остановить все workers
php bin/console messenger:stop-workers
```

### API Эндпоинты

#### Обменные курсы

```http
GET /api/exchange-rates/{baseCurrency}/{quoteCurrency}
```

**Примеры:**
```bash
# Текущий курс USD/EUR
curl http://localhost:8080/api/exchange-rates/USD/EUR

# Курс USD/EUR на определенную дату
curl "http://localhost:8080/api/exchange-rates/USD/EUR?date=2024-08-04T10:00:00"
```

**Ответ:**
```json
{
    "success": true,
    "data": {
        "base_currency": "USD",
        "quote_currency": "EUR",
        "rate": 0.85,
        "timestamp": "2024-08-04 10:00:00",
        "source": "historical"
    },
    "message": "Обменный курс получен успешно"
}
```

#### Список поддерживаемых валют

```http
GET /api/exchange-rates/currencies
```

**Ответ:**
```json
{
    "success": true,
    "data": {
        "currencies": ["USD", "EUR", "GBP", "JPY", "CAD", "AUD", "CHF", "CNY", "RUB", "INR"],
        "total": 20
    },
    "message": "Список поддерживаемых валют"
}
```

#### Статистика по курсам

```http
GET /api/exchange-rates/{baseCurrency}/{quoteCurrency}/statistics
```

**Примеры:**
```bash
# Статистика по курсу USD/EUR
curl http://localhost:8080/api/exchange-rates/USD/EUR/statistics
```

**Ответ:**
```json
{
    "success": true,
    "data": {
        "base_currency": "USD",
        "quote_currency": "EUR",
        "statistics": {
            "min_rate": 0.82,
            "max_rate": 0.89,
            "avg_rate": 0.85,
            "total_records": 1440,
            "period": "last_24_hours"
        }
    },
    "message": "Статистика получена успешно"
}
```



### Веб-интерфейс для тестирования

Откройте в браузере: **http://localhost:8080/index.html**

Современный веб-интерфейс с вкладками для тестирования всех эндпоинтов API:

#### Вкладки интерфейса:
- **Обменные курсы** - получение текущих и исторических курсов валют
- **Валюты** - список поддерживаемых валют
- **Статистика** - статистическая информация по курсам

#### Возможности:
- ✅ Интерактивные формы для ввода параметров
- ✅ Цветовая индикация статусов ответов
- ✅ Поддержка всех HTTP методов (GET, POST)
- ✅ Красивый и современный дизайн
- ✅ Адаптивная верстка для мобильных устройств
- ✅ Автоматическое отображение JSON ответов

## Автоматизация

### Cron задачи

- **Каждую минуту**: Обновление обменных курсов
- **Ежедневно в 2:00**: Очистка старых записей (старше 30 дней) - закомментировано

**Примечание:** !!!ВНИМАНИЕ!!! файл `docker/cron/cronfile` должен быть сохранён в Unix-формате строк LF (без CRLF).

### Поддерживаемые валюты

USD, EUR, GBP, JPY, CAD, AUD, CHF, CNY, RUB, INR, BRL, MXN, KRW, SGD, HKD, NZD, SEK, NOK, DKK, PLN

## Тестирование

Проект включает в себя различные типы тестов:

### Unit тесты
- **Value Objects**: Тестирование валидации и бизнес-логики
- **Domain Services**: Тестирование бизнес-логики домена
- **Application Handlers**: Тестирование обработчиков команд и запросов

### Integration тесты
- **Repository**: Тестирование взаимодействия с базой данных
- **External API**: Тестирование внешних API интеграций

### API тесты
- **Controllers**: Тестирование HTTP эндпоинтов
- **Response formats**: Проверка форматов ответов

### Консольные команды
- **Commands**: Тестирование CLI команд
- **Error handling**: Проверка обработки ошибок

### Запуск тестов

```bash
# Все тесты
php vendor/bin/phpunit

# Только Unit тесты
php vendor/bin/phpunit --testsuite="Unit Tests"

# Только API тесты
php vendor/bin/phpunit --testsuite="API Tests"

# С покрытием кода
php vendor/bin/phpunit --coverage-html coverage/
```

### Примеры тестов

#### Value Object тесты
```php
// tests/Domain/ValueObject/CurrencyTest.php
public function it_creates_currency_with_valid_code(): void
{
    $currency = new Currency('USD');
    $this->assertEquals('USD', $currency->getCode());
}
```

#### API тесты
```php
// tests/Presentation/Controller/ExchangeRateControllerTest.php
public function it_returns_exchange_rate_for_valid_currencies(): void
{
    $result = $this->controller->getExchangeRate('USD', 'EUR', $request);
    $this->assertEquals(200, $result->getStatusCode());
}
```

#### Консольные команды
```php
// tests/Application/Command/AddCurrencyPairConsoleCommandTest.php
public function it_adds_currency_pair_successfully(): void
{
    $this->commandTester->execute([
        'base-currency' => 'USD',
        'quote-currency' => 'EUR',
    ]);
    $this->assertEquals(0, $this->commandTester->getStatusCode());
}
```

## Мониторинг и логирование

### Очереди и воркеры
- **RabbitMQ Management UI**: http://localhost:15672 (guest/guest)
  - Очередь `fetch`: обработка запросов к API
  - Очередь `save`: сохранение данных в БД
- **Worker Logs**: `docker-compose logs worker`
- **Cron Logs**: `docker-compose logs cron`

### Надежность очередей
- **Повторные попытки**: до 3 раз с задержками 1с, 2с, 4с
- **Неудачные сообщения**: автоматически сохраняются в `messenger_messages`
- **Мониторинг**: `php bin/console messenger:failed:show`
- **Восстановление**: `php bin/console messenger:failed:retry`

### База данных
- **PostgreSQL**: localhost:5433
- **Таблицы**: 
  - `currency_pairs` - валютные пары
  - `exchange_rate_usd_eur` - история USD/EUR
  - `exchange_rate_eur_usd` - история EUR/USD
  - `doctrine_migration_versions` - версии миграций Doctrine

### Приложение
- Логи приложения: `var/log/dev.log`
- Логи cron: `var/log/cron.log`
- Логи Docker: `docker-compose logs -f`

## Производительность

- Индексы на таблицах для быстрого поиска
- Кэширование запросов к внешнему API
- Асинхронная обработка команд через Messenger
- Очистка старых записей для оптимизации БД

## Безопасность

- Валидация входных данных
- Логирование всех операций
- Обработка исключений
- Безопасные HTTP заголовки

## Разработка

### Добавление новой валютной пары

1. Сгенерировать сущность: `php bin/console app:generate-currency-pair-entity USD GBP`
2. Добавить в `ExchangeRateEntityFactory::ENTITY_MAP`
3. Создать миграцию для новой таблицы
4. Обновить тесты

### Добавление новой валюты

1. Добавить код валюты в `Currency::SUPPORTED_CURRENCIES`
2. Добавить курс в `FreeCurrencyApiService` (если нужно)
3. Обновить тесты

### Добавление нового API провайдера

1. Создать новый класс, реализующий `ExchangeRateApiInterface`
2. Зарегистрировать в `services.yaml`
3. Обновить конфигурацию

## Дополнительные файлы

- **TESTS_OVERVIEW.md** - Документация по тестированию

## Лицензия

MIT License
