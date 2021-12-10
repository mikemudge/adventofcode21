<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$numbers = explode(",", $lines[0]);
$part1 = 0;
$part2 = 0;

include_once ("classes/Grid.php");

$grid = new Grid(1000, 1000);

foreach ($lines as $line) {
    [$p1, $p2] = explode(" -> ", $line);
    [$x1, $y1] = array_map('intval', explode(",", $p1));
    [$x2, $y2] = array_map('intval', explode(",", $p2));
    echo("$x1,$y1 -> $x2,$y2" . PHP_EOL);

    if ($x1 == $x2) {
        // vertical line.
        $x = $x1;
        for($y = min($y1, $y2); $y <= max($y1, $y2); $y++) {
            $grid->setPos($x, $y, $grid->getPos($x, $y) + 1);
        }
    } else if ($y1 == $y2) {
        // horizontal line.
        $y = $y1;
        for($x = min($x1, $x2); $x <= max($x1, $x2); $x++) {
            $grid->setPos($x, $y, $grid->getPos($x, $y) + 1);
        }
    } else {
        $dx = ($x1 > $x2) ? -1 : 1;
        $dy = ($y1 > $y2) ? -1 : 1;
        $len = abs($x1 - $x2);
        $diags[] = [$x1, $y1, $dx, $dy, $len];
    }
}

for ($y = 0; $y < 1000; $y++) {
    for ($x = 0; $x < 1000; $x++) {
        if ($grid->getPos($x, $y) > 1) {
            $part1++;
        }
    }
}

echo("Part 1: " . $part1 . PHP_EOL);
// 7408 is too low.

foreach ($diags as $diag) {
    [$x1, $y1, $dx, $dy, $len] = $diag;
    for ($i = 0; $i <= $len; $i++) {
        $y = $y1 + $i * $dy;
        $x = $x1 + $i * $dx;
        $grid->setPos($x, $y, $grid->getPos($x, $y) + 1);
    }
}

for ($y = 0; $y < 1000; $y++) {
    for ($x = 0; $x < 1000; $x++) {
        if ($grid->getPos($x, $y) > 1) {
            $part2++;
        }
    }
}

echo("Part 2: " . $part2 . PHP_EOL);
// 21646 is too high.