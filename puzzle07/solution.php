<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$numbers = explode(",", $lines[0]);
$part1 = 0;
$part2 = 0;

// Sample
//$lines = ["16,1,2,0,4,2,7,1,2,14"];

$nums = array_map('intval', explode(",", $lines[0]));

sort($nums);
$cnt = count($nums);
echo("Count: " . $cnt . PHP_EOL);
if ($cnt % 2 == 0) {
    $median = $nums[$cnt / 2];
    echo("Median: " . $median . PHP_EOL);
} else {
    // TODO check this, could be a fraction?
    $median = ($nums[($cnt - 1) / 2] + $nums[($cnt + 1) / 2]) / 2;
    echo("Avg Median: " . $median . PHP_EOL);
}

$fuel = 0;
foreach($nums as $crab) {
    $fuel += abs($crab - $median);
}
$part1 = $fuel;

echo("Part 1: " . $part1 . PHP_EOL);

// Not sure how to find the best location, so try them all?
$small = $nums[0];
$large = $nums[$cnt - 1];
$bestfuel = null;
echo("Trying alignments from: $small -> $large" . PHP_EOL);
for ($align = $small; $align <= $large; $align++) {
    $fuel = 0;
    foreach($nums as $crab) {
        $dis = abs($crab - $align);
        $fuel += $dis * ($dis + 1) / 2;
    }
    if ($bestfuel == null || $fuel < $bestfuel) {
        $bestfuel = $fuel;
    }
}
$part2 = $bestfuel;


echo("Part 2: " . $part2 . PHP_EOL);
// 21646 is too high.