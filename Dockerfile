FROM php:8.4-fpm

# Системные зависимости
RUN apt-get update && apt-get install -y \
    git unzip zip curl cron supervisor \
    libpq-dev libzip-dev librabbitmq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install amqp \
    && docker-php-ext-enable amqp

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка рабочей директории
WORKDIR /var/www/html

# Копируем конфиги и скрипт
COPY ./docker/cron/cronfile /etc/cron.d/cronfile
COPY ./docker/cron/run_schedule.sh /usr/local/bin/run_schedule.sh
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Делаем скрипт исполняемым
RUN chmod +x /usr/local/bin/run_schedule.sh

CMD ["php-fpm"] 