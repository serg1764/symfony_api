<?php

return [
    // Основной каркас Symfony: ядро фреймворка, маршрутизация, события, DI-контейнер и прочее.
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],

    // Интеграция Doctrine ORM и DBAL: работа с БД через сущности, репозитории, консольные команды.
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],

    // Поддержка Doctrine Migrations: создание и выполнение миграций для изменения структуры БД.
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],

    // Генератор кода для разработки (make:controller, make:entity и др.); используется только в dev-окружении.
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],

    // Логирование через Monolog: запись ошибок, событий, запросов в файлы, внешние сервисы и др.
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],

    // Интеграция шаблонизатора Twig: рендеринг HTML-шаблонов, поддержка фильтров и тегов Twig.
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
];