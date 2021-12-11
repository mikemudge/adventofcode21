<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$numbers = explode(",", $lines[0]);
$part1 = 0;
$part2 = 0;

// Sample
//$lines = ["3,4,3,1,2"];

$nums = array_map('intval', explode(",", $lines[0]));

$iteration0 = array_fill(0, 8, 0);
foreach ($nums as $num) {
    $iteration0[$num]++;
}

// Initial state
print_r($iteration0);

$iteration = $iteration0;
for ($iter = 1; $iter <= 256; $iter++) {
    $next = array_fill(0, 8, 0);
    for ($i = 0; $i < 8; $i++) {
        // All fish shift down one day.
        $next[$i] = $iteration[$i+1];
    }
    // The number of new lanternfish comes from how many are at day 0.
    $next[8] = $iteration[0];
    // The fish also reset their timer to day 6.
    $next[6] += $iteration[0];

    // Update the current iteration.
    $iteration = $next;
    if ($iter == 80) {
        $part1 = array_sum($iteration);
    }
    if ($iter == 256) {
        $part2 = array_sum($iteration);
    }
}

// Final state
print_r($iteration);


echo("Part 1: " . $part1 . PHP_EOL);
// 7408 is too low.

echo("Part 2: " . $part2 . PHP_EOL);
// 21646 is too high.