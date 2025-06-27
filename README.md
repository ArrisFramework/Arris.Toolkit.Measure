# Arris.Toolkit.Measure

Measure test toolkit for tests

```php
use Arris\Tookit\Measure;

// Simple measurement
$result = Measure::measure('strtoupper', ['test'], 'String uppercase');
echo Measure::formatResults($result);

// Benchmark with multiple iterations
$stats = Measure::measureMultiple('array_sum', [range(1, 10000)], 10);
echo "Average time: " . ($stats['average_time_ns'] / 1e6) . " ms\n";

// Using with class methods
$measurement = Measure::measure(function: [$object, 'method'], args: [$arg1, $arg2], name: 'Object method test');

// Make test and generate result
echo Measure::benchmark('strtoupper', ['test'], 'String uppercase');
```

```php
// Получаем системную информацию
$systemInfo = Measure::getSystemInfo();
print_r($systemInfo);

// Создаем тестовые измерения
$measurements = [
    'DB Query' => ['time_ns' => 120000000],
    'Cache Read' => ['time_ns' => 45000000],
    'Processing' => ['time_ns' => 78000000],
];

// Выводим timeline
echo Measure::showTimeline($measurements);
```

```php
use Arris\Tookit\Measure;

echo Measure::formatMemory(500);             // "500 bytes"
echo Measure::formatMemory(2048);            // "2 KB"
echo Measure::formatMemory(5242880);         // "5 MB"
echo Measure::formatMemory(1073741824);      // "1 GB"
echo Measure::formatMemory(1555555555, 3);   // "1.449 GB"
```

