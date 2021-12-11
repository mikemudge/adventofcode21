<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$numbers = explode(",", $lines[0]);
$part1 = 0;
$part2 = 0;

include_once ("classes/Grid.php");

$grid = new Grid(100, 100);

foreach ($lines as $y => $line) {
    $vals = str_split($line);
    foreach ($vals as $x => $val) {
        $grid->setPos($x, $y, intval($val));
    }
}

for ($y = 0; $y < 100; $y++) {
    for ($x = 0; $x < 100; $x++) {
        $height = $grid->getPos($x, $y);
        $adjacent = [
            $grid->getPos($x + 1, $y),
            $grid->getPos($x, $y + 1),
            $grid->getPos($x - 1, $y),
            $grid->getPos($x, $y - 1),
        ];
        $lowest = true;
        foreach ($adjacent as $a) {
            if ($a !== null && $a <= $height) {
                // $a is less or equal to this location so its not lowest.
                $lowest = false;
                break;
            }
        }
        if ($lowest) {
            echo("Found low spot at $x,$y with $height and adjacent" . implode($adjacent) . "\n");
            $part1 += $height + 1;
        }
    }
}

echo($grid);

echo("Part 1: " . $part1 . PHP_EOL);
// 1092 is too high.

function recurse($x, $y, $basinId, $basinGrid, $grid) {
    if ($grid->getPos($x, $y) === null) {
        // Outside the grid.
        return 0;
    }
    if ($grid->getPos($x, $y) == 9) {
        // height 9 locations are not part of basins.
        return 0;
    }
    if ($basinGrid->getPos($x, $y)) {
        // Already has a basin_id set.
        if ($basinGrid->getPos($x, $y) != $basinId) {
            // This is a concern.
            echo("Found a " . $basinGrid->getPos($x, $y) . " instead of $basinId basin at $x,$y\n");
        }
        return 0;
    }
    $basinGrid->setPos($x, $y, $basinId);
    // This location was updated.
    $size = 1;
    // Update all the neighbours as well.
    $size += recurse($x + 1, $y, $basinId, $basinGrid, $grid);
    $size += recurse($x, $y + 1, $basinId, $basinGrid, $grid);
    $size += recurse($x - 1, $y, $basinId, $basinGrid, $grid);
    $size += recurse($x, $y - 1, $basinId, $basinGrid, $grid);
    return $size;
}

// Need to find all non 9's which connect to each low point.
$basinIdx = 0;
$basinIds = str_split("123456789abcdefghijklmnopqrstuvwxyz");
$basinIds = array_merge($basinIds, $basinIds);
$basinIds = array_merge($basinIds, $basinIds);
$basinIds = array_merge($basinIds, $basinIds);
$basinSizes = [];
$basinGrid = new Grid(100, 100);
for ($y = 0; $y < 100; $y++) {
    for ($x = 0; $x < 100; $x++) {
        // Assign the next basin and propagate as far as possible.
        if ($basinGrid->getPos($x, $y)) {
            // Already has a basin
            continue;
        }
        $size = recurse($x, $y, $basinIds[$basinIdx], $basinGrid, $grid);
        // $size 0 means the location isn't a basin.
        if ($size > 0) {
            echo("Found a basin of size $size\n");
            $basinSizes[] = $size;
            $basinIdx++;
        }
    }
}

echo($basinGrid);

echo("Found $basinIdx basins\n");

rsort($basinSizes);
echo("3 largest are " . $basinSizes[0] . ", " . $basinSizes[1] . ", " . $basinSizes[2] . PHP_EOL);
$part2 = $basinSizes[0] * $basinSizes[1] * $basinSizes[2];
echo("Part 2: " . $part2 . PHP_EOL);
