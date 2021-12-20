<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

// Sample
$lines = [
    'NNCB',
    '',
    'CH -> B',
    'HH -> N',
    'CB -> H',
    'NH -> C',
    'HB -> C',
    'HC -> B',
    'HN -> C',
    'NN -> C',
    'BH -> H',
    'NC -> B',
    'NB -> B',
    'BN -> B',
    'BB -> N',
    'BC -> B',
    'CC -> N',
    'CN -> C',
];

function iterate($value, $lookup) {
    $nextvalue = [];
    $nextvalue[] = $value[0];
    $countper = [
        $value[0] => 1,
    ];
    for ($i = 1; $i < count($value); $i++) {
        $key = $value[$i - 1] . $value[$i];
        $insert = $lookup[$key];
        $nextvalue[] = $insert;
        $nextvalue[] = $value[$i];
        if (!isset($countper[$insert])) {
            $countper[$insert] = 0;
        }
        if (!isset($countper[$value[$i]])) {
            $countper[$value[$i]] = 0;
        }
        $countper[$insert]++;
        $countper[$value[$i]]++;
    }
    return [$nextvalue, $countper];
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

$results =[
    str_split($input)
];
$counts = [[]];
for ($iter = 1; $iter <= 10; $iter++) {
    [$results[$iter], $counts[$iter]] = iterate($results[$iter - 1], $lookup);
}

for ($i = 0; $i <= 10; $i++) {
    echo("Step $i\n");
    echo("len " . count($results[$i]) . "\n");
    if ($i < 4) {
        echo(join("", $results[$i]) . "\n");
    }
}

$round10counts = $counts[10];
print_r($round10counts);

sort($round10counts);
$part1 = $round10counts[count($round10counts) - 1] - $round10counts[0];
echo("Part 1: " . $part1 . PHP_EOL);

function recurse($char1, $char2, $lookup, $height, $cache) {
    $key = $char1 . $char2;
    $insert = $lookup[$key];
    if ($height == 1) {
        return [
            $insert => 1
        ];
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
    return $result;
}
$cache = [];
$result = [
    $input[0] => 1
];
for ($i = 1; $i < strlen($input); $i++) {
    echo($input[$i - 1] . "," . $input[$i] . PHP_EOL);
    $result2 = recurse($input[$i - 1], $input[$i], $lookup, 10, $cache);
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

print_r($result);

sort($result);
$part1 = $result[count($result) - 1] - $result[0];
echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
