<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

print_r($lines);

$lines = array_map('intval', $lines);

$inc1 = 0;
$inc2 = 0;
for ($i = 0; $i < count($lines) - 1; $i++) {
    if ($lines[$i] < $lines[$i + 1]) {
        $inc1++;
    }
    if ($i + 3 < count($lines) && $lines[$i] < $lines[$i + 3]) {
        $inc2++;
    }
}

echo("Part 1: $inc1" . PHP_EOL);
echo("Part 2: $inc2" . PHP_EOL);
