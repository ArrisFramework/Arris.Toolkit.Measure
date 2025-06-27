<?php

namespace Arris\Toolkit;

final class Measure implements MeasureInterface
{
    const SIZE_KB = 1024;
    const SIZE_MB = self::SIZE_KB * 1024;
    const SIZE_GB = self::SIZE_MB * 1024;
    const SIZE_TB = self::SIZE_GB * 1024;

    public static int $PRECISION_MEMORY = 2;
    public static int $PRECISION_TIME = 3;

    const TIME_NS_TO_MS = 1e+6;

    /**
     * Clean memory before measurement
     * @return void
     */
    public static function cleanMemory(): void
    {
        gc_collect_cycles();
        if (function_exists('gc_mem_caches')) {
            gc_mem_caches();
        }
    }

    /**
     * Get accurate memory usage
     *
     * @return int
     */
    public static function getMemoryUsage(): int
    {
        // Linux-specific more precise measurement
        if (file_exists('/proc/self/status')) {
            $status = file_get_contents('/proc/self/status');
            preg_match('/VmRSS:\s+(\d+)\s+kB/', $status, $matches);
            return isset($matches[1]) ? (int)$matches[1] * 1024 : memory_get_usage(true);
        }
        return memory_get_usage(true);
    }

    /**
     * Measure function execution
     *
     * @param callable $function Function to measure
     * @param string $name Test name
     * @param array $args Function arguments
     * @return array [result, time_ns, memory_bytes, peak_memory_bytes]
     */
    public static function measure(callable $function, array $args = [], string $name = ''): array
    {
        self::cleanMemory();
        $startTime = hrtime(true);
        $startMemory = self::getMemoryUsage();

        $result = call_user_func_array($function, $args);

        $endTime = hrtime(true);
        $endMemory = self::getMemoryUsage();

        return [
            'result'        => $result,
            'time_ns'       => $endTime - $startTime,
            'memory_bytes'  => $endMemory - $startMemory,
            'peak_memory_bytes' => memory_get_peak_usage(true),
            'name'          => $name
        ];
    }

    /**
     * Форматирует результаты измерения в строку
     *
     * Format measurement results as string
     *
     * @param array $measurement
     * @param string $separator
     * @param bool $show_results
     * @return string
     */
    public static function formatResults(array $measurement, string $separator = "\n", bool $show_results = false): string
    {
        $timeNs = $measurement['time_ns'];

        $output = [];
        if (!empty($measurement['name'])) {
            $output[] = "Test: {$measurement['name']}";
        }

        if ($show_results) {
            $output[] = " - Result: " . (is_scalar($measurement['result']) ? $measurement['result'] : gettype($measurement['result']));
        }

        $output[] = " - Time: "        . self::formatTime($timeNs / self::TIME_NS_TO_MS);
        $output[] = " - Memory used: " . self::formatMemory($measurement['memory_bytes']);
        $output[] = " - Peak memory: " . self::formatMemory($measurement['peak_memory_bytes']);
        $output[] = str_repeat("-", 50) . "\n";

        return implode("\n", $output);
    }

    /**
     * Форматирует время в микросекундах в строку
     *
     * @param $time_ms
     * @return string
     */
    public static function formatTime($time_ms):string
    {
        return match(true) {
            $time_ms < 1 => round($time_ms * 1000, self::$PRECISION_TIME) . " μs",
            $time_ms < 1000 => round($time_ms, self::$PRECISION_TIME) . " ms",
            default => round($time_ms / 1000, self::$PRECISION_TIME) . " sec"
        };
    }

    /**
     * Форматирует размер памяти в удобочитаемый вид
     *
     * @param int $bytes - размер в байтах
     * @param int|null $precision - точность округления (знаков после запятой)
     * @return string - форматированная строка с единицами измерения
     */
    public static function formatMemory(int $bytes, ?int $precision = null): string
    {
        $precision = $precision ?: self::$PRECISION_TIME;
        return match (true) {
            $bytes >= self::SIZE_GB     => round($bytes / self::SIZE_GB, $precision) . ' Gb',
            $bytes >= self::SIZE_MB     => round($bytes / self::SIZE_MB, $precision) . ' Mb',
            $bytes >= self::SIZE_KB     => round($bytes / self::SIZE_KB, $precision) . ' Kb',
            default                     =>                                                  $bytes . ' bytes'
        };
    }

    /**
     * Run multiple measurements and return statistics
     *
     * @param callable $function
     * @param array $args
     * @param int $iterations
     * @param bool $save_iterations
     * @return array
     */
    public static function measureMultiple(callable $function, array $args = [], int $iterations = 1, bool $save_iterations = false): array
    {
        $results = [];
        for ($i = 0; $i < $iterations; $i++) {
            $results[] = self::measure(function: $function, args: $args, name: "Iteration {$i}");
        }

        $times = array_column($results, 'time_ns');
        $memories = array_column($results, 'memory_bytes');

        return [
            'average_time_ns'       => array_sum($times) / count($times),
            'min_time_ns'           => min($times),
            'max_time_ns'           => max($times),
            'average_memory_bytes'  => array_sum($memories) / count($memories),
            'min_memory_bytes'      => min($memories),
            'max_memory_bytes'      => max($memories),
            'iterations'            => $save_iterations ? $results : []
        ];
    }

    public static function getSystemInfo(): array
    {
        return [
            'php_version'       => PHP_VERSION,
            'php_os'            => PHP_OS,
            'php_architecture'  => (PHP_INT_SIZE === 8) ? 'x64' : 'x86',
            'php_memory_limit'  => ini_get('memory_limit'),
            'php_max_execution_time'    => ini_get('max_execution_time'),
            'server_software'   => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
            'server_protocol'   => $_SERVER['SERVER_PROTOCOL'] ?? null,
            'cpu_count'         => (function() {
                if (str_starts_with(PHP_OS, 'WIN')) {
                    return (int)shell_exec('wmic cpu get NumberOfCores | find /v "NumberOfCores"');
                }
                return (int)shell_exec('nproc');
            })(),
            'system_load'       => (function() {
                if (function_exists('sys_getloadavg')) {
                    return sys_getloadavg();
                }
                return null;
            })(),
            'peak_memory'       => self::formatMemory(memory_get_peak_usage(true)),
            'current_memory'    => self::formatMemory(memory_get_usage(true)),
            'timestamp'         => date('Y-m-d H:i:s'),
            'timezone'          => date_default_timezone_get(),
        ];
    }

    public static function showTimeline(array $measurements): string
    {
        if (empty($measurements)) {
            return "No measurements to display";
        }

        // Находим максимальное время для масштабирования
        $maxTime = max(array_column($measurements, 'time_ns'));
        $maxLength = 50; // Ширина timeline в символах

        $output = "Execution Timeline:\n";
        $output .= str_repeat("-", $maxLength + 20) . "\n";

        foreach ($measurements as $name => $measurement) {
            $timeMs = $measurement['time_ns'] / self::TIME_NS_TO_MS;
            $barLength = $maxTime > 0 ? (int)($measurement['time_ns'] / $maxTime * $maxLength) : 0;

            $output .= sprintf(
                "%-15s %5.2f ms |%s%s|\n",
                substr($name, 0, 15),
                $timeMs,
                str_repeat("█", max(1, $barLength)),
                str_repeat(" ", $maxLength - $barLength)
            );
        }

        $output .= str_repeat("-", $maxLength + 20) . "\n";
        $output .= sprintf("Max time: %.2f ms\n", $maxTime / self::TIME_NS_TO_MS);

        return $output;
    }

    /**
     * Метод, который выполняет тестирование и возвращает результаты
     *
     * @param callable $function
     * @param array $args
     * @param string $name
     * @param $separator
     * @return string
     */
    public static function benchmark(callable $function, array $args = [], string $name = '', $separator = "\n"): string
    {
        $result = self::measure($function, $args, $name);
        return self::formatResults($result, $separator);
    }
}

# -eof- #