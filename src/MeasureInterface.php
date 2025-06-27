<?php

namespace Arris\Toolkit;

interface MeasureInterface
{
    public static function cleanMemory(): void;
    public static function getMemoryUsage(): int;

    public static function measure(callable $function, array $args = [], string $name = ''): array;
    public static function measureMultiple(callable $function, array $args = [], int $iterations = 1, bool $save_iterations = false): array;

    public static function benchmark(callable $function, array $args = [], string $name = ''):string;

    public static function formatResults(array $measurement, string $separator = "\n", bool $show_results = false): string;
    public static function formatMemory(int $bytes, ?int $precision = null): string;
    public static function formatTime($time_ms):string;

    public static function getSystemInfo(): array;

    public static function showTimeline(array $measurements): string;

}