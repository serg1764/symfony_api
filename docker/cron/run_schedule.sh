#!/bin/sh

# Устанавливаем переменные окружения вручную
# Просто для теста данный файл не участвует в процессе
export APP_ENV=dev
export DATABASE_URL="postgresql://symfony:symfony@postgres:5432/exchange?serverVersion=16&charset=utf8"
export MESSENGER_TRANSPORT_DSN_FETCH="amqp://guest:guest@rabbitmq:5672/%2f/fetch"
export MESSENGER_TRANSPORT_DSN_SAVE="amqp://guest:guest@rabbitmq:5672/%2f/save"
export FREECURRENCY_API_KEY="your-api-key"

# Группируем лог в один блок, чтобы избежать SC2129
{
  echo "🕐 Running schedule-fetch at $(date)"
  echo "🌍 APP_ENV=$APP_ENV"
  echo "🔑 DATABASE_URL=$DATABASE_URL"
  echo "📥 MESSENGER_TRANSPORT_DSN_FETCH=$MESSENGER_TRANSPORT_DSN_FETCH"
  echo "📤 MESSENGER_TRANSPORT_DSN_SAVE=$MESSENGER_TRANSPORT_DSN_SAVE"
  echo "💸 FREECURRENCY_API_KEY=$FREECURRENCY_API_KEY"
} >> /var/log/cron.log

# Выполняем Symfony-команду, отдельно логируем её вывод
/usr/local/bin/php /var/www/html/bin/console app:schedule-fetch-rates >> /var/log/cron.log 2>&1
