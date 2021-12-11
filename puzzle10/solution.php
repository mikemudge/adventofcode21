<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$numbers = explode(",", $lines[0]);
$part1 = 0;
$part2 = 0;

// Sample
//$lines = ["[<>({}){}[([])<>]]"];

// Corrupted
//$lines = ["<([]){()}[{}])"];
class CorruptedException extends RuntimeException {
    public $points;
}

$points = [
    ")" => 1,
    "]" => 2,
    "}" => 3,
    ">" => 4
];
$scores = [];
foreach($lines as $i => $line) {
    $expected = [];
    try {
        recurse(0, $line, null, $expected);

        echo("Line incomplete, expecting: " . implode($expected) . PHP_EOL);
        $score = 0;
        foreach($expected as $e) {
            $score *= 5;
            $score += $points[$e];
        }
        $scores[] = $score;
    } catch (CorruptedException $e) {
//        echo("This line was corrupted $i: " . $e->getMessage() . "\n");
        $part1 += $e->points;
    }
}

function recurse($idx, $line, $expect, &$expected) {
    if ($idx >= strlen($line)) {
        // If $expect is set then this line is incomplete?
        if ($expect) {
            $expected[] = $expect;
        }
        return $idx;
    }
    $c = $line[$idx];
    if ($c == $expect) {
        return $idx + 1;
    } else if ($c == "[") {
        $idx = recurse($idx + 1, $line, "]", $expected);
    } else if ($c == "{") {
        $idx = recurse($idx + 1, $line, "}", $expected);
    } else if ($c == "(") {
        $idx = recurse($idx + 1, $line, ")", $expected);
    } else if ($c == "<") {
        $idx = recurse($idx + 1, $line, ">", $expected);
    } else {
        // This line is corrupt?
        $e = new CorruptedException("Expected $expect but got $c at $idx");
        $e->points = [
            ")" => 3,
            "]" => 57,
            "}" => 1197,
            ">" => 25137][$c];
        throw $e;
    }
    // We got somewhere? but there might be more to come?
    $idx = recurse($idx, $line, $expect, $expected);
    return $idx;
}


echo("Part 1: " . $part1 . PHP_EOL);
// 1092 is too high.

sort($scores);
$part2 = $scores[(count($scores) - 1) / 2];
echo("Part 2: " . $part2 . PHP_EOL);
//19910932121 is too high.