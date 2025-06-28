<?php

use Arris\Toolkit\Measure;

require __DIR__ . '/vendor/autoload.php';

/**
 * Рекурсивно генерирует массив с ветвлениями.
 * - $targetDepth: требуемая глубина элемента.
 * - $maxDepth: максимальная глубина (для контроля ветвления).
 * - $currentDepth: текущая глубина (для рекурсии).
 */
function generateBranchingArray(int $targetDepth, int $maxDepth, int $currentDepth = 1): array {
    if ($currentDepth >= $targetDepth) {
        // Листья — случайные числа или простые массивы
        return [rand(1, 100)]; // Вариативность в терминальных узлах
    }

    $array = [];
    $childrenCount = rand(1, 3); // Случайное количество ветвей (1-3)

    for ($i = 0; $i < $childrenCount; $i++) {
        // Случайно выбираем, углубляться ли дальше или оставить на текущем уровне
        $goDeeper = rand(0, 1);
        $childDepth = $goDeeper ? $targetDepth : rand(1, $targetDepth - 1);

        $array[] = generateBranchingArray(
            $childDepth,
            $maxDepth,
            $currentDepth + 1
        );
    }

    return $array;
}

function generateNestedArray(int $depth): array {
    if ($depth <= 1) {
        return [rand(1, 100)]; // Лист — случайное число
    }
    return [generateNestedArray($depth - 1)];
}

function generateTestArray(int $width, int $maxDepth): array {
    if ($width <= 6 || $maxDepth <= 3) {
        throw new InvalidArgumentException("Ширина (W) должна быть > 6, а глубина (D) > 3");
    }

    $array = [];
    $remainingWidth = $width;

    // Добавляем 1 элемент с максимальной глубиной D
    $array[] = generateNestedArray($maxDepth);
    $remainingWidth--;

    // Добавляем 2 элемента с глубиной D-1
    for ($i = 0; $i < 2 && $remainingWidth > 0; $i++) {
        $array[] = generateNestedArray($maxDepth - 1);
        $remainingWidth--;
    }

    // Добавляем 3 элемента с глубиной D-2
    for ($i = 0; $i < 3 && $remainingWidth > 0; $i++) {
        $array[] = generateNestedArray($maxDepth - 2);
        $remainingWidth--;
    }

    // Остальные элементы заполняем случайной глубиной от 1 до 3
    while ($remainingWidth > 0) {
        $randomDepth = rand(1, 3);
        $array[] = generateNestedArray($randomDepth);
        $remainingWidth--;
    }

    // Перемешиваем, чтобы глубина не была упорядочена
    shuffle($array);

    return $array;
}

function array_depth_bfs(array $array): int {
    if (empty($array)) {
        return 0;
    }

    $queue = [[$array, 1]];
    $max_depth = 1;

    while (!empty($queue)) {
        [$current, $depth] = array_shift($queue);
        $max_depth = max($max_depth, $depth);

        foreach ($current as $value) {
            if (is_array($value)) {
                $queue[] = [$value, $depth + 1];
            }
        }
    }

    return $max_depth;
}

function array_depth_dfs(array $array): int {
    if (empty($array)) {
        return 0;
    }

    $stack = [[$array, 1]];
    $max_depth = 1;

    while (!empty($stack)) {
        [$current, $depth] = array_pop($stack);
        $max_depth = max($max_depth, $depth);

        foreach ($current as $value) {
            if (is_array($value)) {
                $stack[] = [$value, $depth + 1];
            }
        }
    }

    return $max_depth;
}

function array_depth_recursion(array $array): int {
    $max_depth = 1;

    foreach ($array as $value) {
        if (is_array($value)) {
            $depth = array_depth_recursion($value) + 1;
            $max_depth = max($max_depth, $depth);
        }
    }

    return $max_depth;
}

Measure::setLanguage('ru');
// Запуск тестов
$result = Measure::measure("generateTestArray", [ 100000, 50000 ], "Генерация массива");
$array = $result['result'];
echo Measure::formatResults($result);

echo Measure::benchmark('array_depth_recursion', [ $array ], 'Рекурсивное определение глубины');

echo Measure::benchmark('array_depth_dfs',  [ $array ], 'Обход в глубину (DFS) с использованием стека');

echo Measure::benchmark('array_depth_bfs', [ $array ], 'Обход в ширину (BFS) с использованием очереди');

echo "Итоговые показатели:\n";
echo " - Общее время: " . round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000, 2) . " мс\n";
echo " - Пиковая память за все время: " . round(memory_get_peak_usage() / 1024, 2) . " КБ\n";
