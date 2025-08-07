<?php

namespace App\Application\Service;

class DateService
{
    /**
     * Парсит дату из различных форматов и округляет до минуты
     */
    public function parseDate(string $dateString): \DateTimeImmutable
    {
        // Убираем лишние пробелы
        $dateString = trim($dateString);
        
        // Поддерживаемые форматы
        $formats = [
            'Y-m-d\TH:i:s',     // 2024-08-06T21:22:00
            'Y-m-d H:i:s',      // 2024-08-06 21:22:00
            'Y-m-d',             // 2024-08-06
            'd.m.Y H:i:s',       // 06.08.2024 21:22:00
            'd.m.Y',             // 06.08.2024
        ];
        
        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $dateString);
            if ($date !== false) {
                // Округляем до минуты (убираем секунды)
                return $date->setTime($date->format('H'), $date->format('i'), 0);
            }
        }
        
        throw new \InvalidArgumentException("Неверный формат даты: {$dateString}");
    }

    /**
     * Валидирует дату (не в будущем, не слишком старая)
     */
    public function validateDate(\DateTimeImmutable $date): void
    {
        $now = new \DateTimeImmutable();
        
        // Округляем текущее время до минуты для корректного сравнения
        $nowRounded = $now->setTime($now->format('H'), $now->format('i'), 0);
        
        // Проверяем, что дата не в будущем
        if ($date > $nowRounded) {
            throw new \InvalidArgumentException('Дата не может быть в будущем');
        }
        
        // Проверяем, что дата не слишком старая (например, не старше 10 лет)
        $tenYearsAgo = $nowRounded->modify('-10 years');
        if ($date < $tenYearsAgo) {
            throw new \InvalidArgumentException('Дата слишком старая. Поддерживаются даты не старше 10 лет');
        }
    }
}
