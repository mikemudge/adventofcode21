<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$numbers = explode(",", $lines[0]);
$part1 = 0;
$part2 = 0;

// Sample
//$lines = [
//    '5483143223',
//    '2745854711',
//    '5264556173',
//    '6141336146',
//    '6357385478',
//    '4167524645',
//    '2176841721',
//    '6882881134',
//    '4846848554',
//    '5283751526',
//];

include_once ("classes/Grid.php");

$grid = new Grid(10, 10);

foreach($lines as $y => $line) {
    $vals = str_split($line);
    foreach($vals as $x => $val) {
        $grid->setPos($x, $y, $val);
    }
}

function incRecurse($x, $y, $grid) {
    $flashes = 0;
    if (!$grid->getPos($x, $y)) {
        // Handles null (edges)
        // Or 0's which already flashed this iteration.
        return 0;
    }
    $grid->setPos($x, $y, $grid->getPos($x, $y) + 1);
    if ($grid->getPos($x, $y) > 9) {
        // Flash and update neighbours.
        $flashes = 1;
        // reset to 0.
        $grid->setPos($x, $y, 0);
        // Go to all neighbours including diagonals.
        $flashes += incRecurse($x + 1, $y + 1, $grid);
        $flashes += incRecurse($x + 1, $y, $grid);
        $flashes += incRecurse($x + 1, $y - 1, $grid);
        $flashes += incRecurse($x - 1, $y + 1, $grid);
        $flashes += incRecurse($x - 1, $y, $grid);
        $flashes += incRecurse($x - 1, $y - 1, $grid);
        $flashes += incRecurse($x, $y + 1, $grid);
        $flashes += incRecurse($x, $y - 1, $grid);
    }
    return $flashes;
}

for ($iter = 0; $iter < PHP_INT_MAX; $iter++) {

    if ($iter < 10 || $iter % 10 == 0) {
        echo("After step $iter:\n");
        echo($grid);
    }

    // Increment
    for ($y = 0; $y < 10; $y++) {
        for ($x = 0; $x < 10; $x++) {
            $grid->setPos($x, $y, $grid->getPos($x, $y) + 1);
        }
    }

    // Check and flash.
    $flashes = 0;
    for ($y = 0; $y < 10; $y++) {
        for ($x = 0; $x < 10; $x++) {
            if ($grid->getPos($x, $y) > 9) {
                $flashes += incRecurse($x, $y, $grid);
            }
        }
    }
    if ($flashes == 100) {
        // All octopus flashed at the same time.
        $part2 = $iter + 1;
        break;
    }
    $flashesPerIter[$iter] = $flashes;
}

$part1 = array_sum(array_slice($flashesPerIter, 0, 100));

echo("Part 1: " . $part1 . PHP_EOL);
echo("Part 2: " . $part2 . PHP_EOL);
