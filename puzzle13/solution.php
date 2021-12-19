<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$numbers = explode(",", $lines[0]);
$part1 = 0;
$part2 = 0;

// Sample
//$lines = [
//    '6,10',
//    '0,14',
//    '9,10',
//    '0,3',
//    '10,4',
//    '4,11',
//    '6,0',
//    '6,12',
//    '4,1',
//    '0,13',
//    '10,12',
//    '3,4',
//    '3,0',
//    '8,4',
//    '1,10',
//    '2,14',
//    '8,10',
//    '9,0',
//    '',
//    'fold along y=7',
//    'fold along x=5',
//];

function foldY(int $pivot, Grid $grid) {
    $h = max($pivot, $grid->getHeight() - $pivot) - 1;
    $newgrid = new Grid($grid->getWidth(), $h, '.');

    for ($y = 0; $y < $grid->getHeight(); $y++) {
        for ($x = 0; $x < $grid->getWidth(); $x++) {
            if ($grid->getPos($x, $y) == '#') {
                if ($y < $pivot) {
                    $newgrid->setPos($x, $y, '#');
                } else {
                    $newgrid->setPos($x, 2 * $pivot - $y, '#');
                }
            }
        }
    }
    return $newgrid;
}

function foldX(int $pivot, Grid $grid) {
    $w = max($pivot, $grid->getWidth() - $pivot) - 1;
    $newgrid = new Grid($w, $grid->getHeight(), '.');

    for ($y = 0; $y < $grid->getHeight(); $y++) {
        for ($x = 0; $x < $grid->getWidth(); $x++) {
            if ($grid->getPos($x, $y) == '#') {
                if ($x < $pivot) {
                    $newgrid->setPos($x, $y, '#');
                } else {
                    $newgrid->setPos(2 * $pivot - $x, $y, '#');
                }
            }
        }
    }
    return $newgrid;
}

include_once ("classes/Grid.php");

$w = 0;
$h = 0;
foreach($lines as $i => $line) {
    if (empty($line)) {
        $foldsIdx = $i;
        break;
    }
    [$x, $y] = explode(",", $line);
    $w = max($w, intval($x));
    $h = max($h, intval($y));
}

$grid = new Grid($w + 1, $h + 1, ".");

for($i=0; $i < $foldsIdx; $i++) {
    $line = $lines[$i];
    [$x, $y] = explode(",", $line);
    $x = intval($x);
    $y = intval($y);
    $grid->setPos($x, $y, "#");
}

//echo($grid);

for ($i = $foldsIdx + 1; $i<count($lines); $i++) {
    $fold = $lines[$i];
    [$axis, $pivot] = explode("=", substr($fold, strlen("fold along ")));
    $pivot = intval($pivot);

    echo($pivot * 2 + 1 . "\n");
    echo($grid->getWidth() . "," . $grid->getHeight() ."\n");
    if ($axis == "y") {
        $grid = foldY($pivot, $grid);
    } else {
        $grid = foldX($pivot, $grid);
    }
    if ($i == $foldsIdx + 1) {
        $part1 = $grid->countOccurrences("#");
    }
}

echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
echo($grid);
//####..##...##..#..#.###..####.#..#.####.
//#....#..#.#..#.#..#.#..#....#.#..#.#....
//###..#..#.#....#..#.#..#...#..####.###..
//#....####.#.##.#..#.###...#...#..#.#....
//#....#..#.#..#.#..#.#.#..#....#..#.#....
//#....#..#..###..##..#..#.####.#..#.####.
// FAGURZHE