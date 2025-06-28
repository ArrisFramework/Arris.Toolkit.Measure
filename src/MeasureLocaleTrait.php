<?php

namespace Arris\Toolkit;

trait MeasureLocaleTrait
{
    public static string $LANGUAGE = 'en';
    public static array $LOCALE = [
        'en' => [
            'test'          => 'Test',
            'result'        => 'Result',
            'time'          => 'Time',
            'memory_used'   => 'Memory used',
            'peak_memory'   => 'Peak memory',
            'max_time'      => 'Max time',
            'timeline'      => 'Execution Timeline',
            'no_measurements' => 'No measurements to display',
            'units' => [
                'μs'        => 'μs',
                'ms'        => 'ms',
                'sec'       => 'sec',
                'bytes'     => 'bytes',
                'kb'        => 'KB',
                'mb'        => 'MB',
                'gb'        => 'GB'
            ]
        ],
        'ru' => [
            'test'          => 'Тест',
            'result'        => 'Результат',
            'time'          => 'Время',
            'memory_used'   => 'Использовано памяти',
            'peak_memory'   => 'Пиковая память',
            'max_time'      => 'Макс. время',
            'timeline'      => 'Временная шкала выполнения',
            'no_measurements' => 'Нет данных для отображения',
            'units' => [
                'μs'        => 'мкс',
                'ms'        => 'мс',
                'sec'       => 'сек',
                'bytes'     => 'байт',
                'kb'        => 'Кб',
                'mb'        => 'Мб',
                'gb'        => 'Гб'
            ]
        ]
    ];

    /**
     * Установка языка вывода
     */
    public static function setLanguage(string $lang): void
    {
        self::$LANGUAGE = in_array($lang, ['en', 'ru']) ? $lang : 'en';
    }

    /**
     * Получение локализованной строки
     */
    protected static function _t(string $key): string
    {
        return self::$LOCALE[self::$LANGUAGE][$key] ?? $key;
    }

    /**
     * Получение локализованной единицы измерения
     */
    protected static function _u(string $unit): string
    {
        return self::$LOCALE[self::$LANGUAGE]['units'][$unit] ?? $unit;
    }

}