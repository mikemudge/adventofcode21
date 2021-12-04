<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$x1 = 0;
$y1 = 0;
$x2 = 0;
$y2 = 0;
$aim = 0;
foreach($lines as $line) {
    [$dir, $dis] = explode(" ", $line);


    if ($dir == "forward") {
        $x1 += $dis;
        $x2 += $dis;
        $y2 += $dis * $aim;
    }
    // Y position is depth from surface, so down is more, and up is less.
    if ($dir == "down") {
        $y1 += $dis;
        $aim += $dis;
    }
    if ($dir == "up") {
        $y1 -= $dis;
        $aim -= $dis;
    }
}

echo("$x1, $y1" . PHP_EOL);
echo("Part 1: " . $x1 * $y1 . PHP_EOL);

echo("$x2, $y2" . PHP_EOL);
echo("Part 2: " . $x2 * $y2 . PHP_EOL);

