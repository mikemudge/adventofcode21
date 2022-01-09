<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

//$input = file_get_contents("$cwd/sample");

$lines = explode("\n", $input);

function updateCucumbers(Grid $grid) {
    $moved = false;
    $newgrid = new Grid($grid->getWidth(), $grid->getHeight(), '.');
    for ($y = 0; $y < $grid->getHeight(); $y++) {
        for ($x = 0; $x < $grid->getWidth(); $x++) {
            $v = $grid->getPos($x, $y);
            if ($v == ">") {
                // Check right
                $x1 = ($x + 1) % $grid->getWidth();
                if ($grid->getPos($x1, $y) == '.') {
                    $newgrid->setPos($x1, $y, ">");
                    $newgrid->setPos($x, $y, ".");
                    $moved = true;
                } else {
                    $newgrid->setPos($x, $y, ">");
                }
            } elseif ($v != ".") {
                $newgrid->setPos($x, $y, $v);
            }
        }
    }

    $grid = $newgrid;
    $newgrid = new Grid($grid->getWidth(), $grid->getHeight(), '.');
    // Now move the south facing herd
    for ($y = 0; $y < $grid->getHeight(); $y++) {
        for ($x = 0; $x < $grid->getWidth(); $x++) {
            $v = $grid->getPos($x, $y);
            if ($v == "v") {
                // Check down
                $y1 = ($y + 1) % $grid->getHeight();
                if ($grid->getPos($x, $y1) == '.') {
                    $newgrid->setPos($x, $y1, "v");
                    $newgrid->setPos($x, $y, ".");
                    $moved = true;
                } else {
                    $newgrid->setPos($x, $y, "v");
                }
            } elseif ($v != ".") {
                $newgrid->setPos($x, $y, $v);
            }
        }
    }
    return [$newgrid, $moved];
}

include_once ("classes/Grid.php");

$grid = new Grid(strlen($lines[0]), count($lines), '.');

foreach ($lines as $y=>$line) {
    foreach(str_split($line) as $x=>$c) {
        $grid->setPos($x, $y, $c);
    }
}

$moved = true;

while($moved) {
    echo("After $part1 iterations\n");
    echo($grid);
    [$grid, $moved] = updateCucumbers($grid);
    $part1++;
}

echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
