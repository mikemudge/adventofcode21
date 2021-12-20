<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

// Sample
//$lines = [
//    'NNCB',
//    '',
//    'CH -> B',
//    'HH -> N',
//    'CB -> H',
//    'NH -> C',
//    'HB -> C',
//    'HC -> B',
//    'HN -> C',
//    'NN -> C',
//    'BH -> H',
//    'NC -> B',
//    'NB -> B',
//    'BN -> B',
//    'BB -> N',
//    'BC -> B',
//    'CC -> N',
//    'CN -> C',
//];

function recurse($char1, $char2, $lookup, $height, &$cache) {
    $key = $char1 . $char2;
    $insert = $lookup[$key];
    if ($height == 1) {
        return [
            $insert => 1
        ];
    }
    $cacheKey = "$char1$char2$height";
    if (isset($cache[$cacheKey])) {
        // We already calculated this pair at this height, so use the same result.
        return $cache[$cacheKey];
    }

    $result = recurse($char1, $insert, $lookup, $height - 1, $cache);
    $result2 = recurse($insert, $char2, $lookup, $height - 1, $cache);
    foreach ($result2 as $x => $cnt) {
        if (empty($result[$x])) {
            $result[$x] = 0;
        }
        $result[$x] += $cnt;
    }
    // Each layer adds its inserted characters to the result.
    if (!isset($result[$insert])) {
        $result[$insert] = 0;
    }
    $result[$insert] += 1;
    // Cache will store char1, char2, height => letterMap.
    $cache[$cacheKey] = $result;
    return $result;
}

function solve($input, $lookup, $height) {
    $cache = [];
    $result = [
        $input[0] => 1
    ];
    for ($i = 1; $i < strlen($input); $i++) {
        echo($input[$i - 1] . "," . $input[$i] . PHP_EOL);
        $result2 = recurse($input[$i - 1], $input[$i], $lookup, $height, $cache);
        foreach ($result2 as $x => $cnt) {
            if (empty($result[$x])) {
                $result[$x] = 0;
            }
            $result[$x] += $cnt;
        }
        if (!isset($result[$input[$i]])) {
            $result[$input[$i]] = 0;
        }
        $result[$input[$i]] += 1;
    }
    return $result;
}

$input = $lines[0];

$part1 = 0;
$part2 = 0;

$lookup = [];
for ($i = 2; $i < count($lines); $i++) {
    [$key, $value] = explode(" -> ", $lines[$i]);
    $lookup[$key] = $value;
}

print_r($lookup);

$result = solve($input, $lookup, 10);

print_r($result);

sort($result);
$part1 = $result[count($result) - 1] - $result[0];
echo("Part 1: " . $part1 . PHP_EOL);


$result = solve($input, $lookup, 40);

print_r($result);

sort($result);
$part2 = $result[count($result) - 1] - $result[0];
echo("Part 2: " . $part2 . PHP_EOL);
