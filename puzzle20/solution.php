<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$enhanceAlgo = $lines[0];
$dirs = [
    [-1, -1, 256],
    [0, -1, 128],
    [1, -1, 64],
    [-1, 0, 32],
    [0, 0, 16],
    [1, 0, 8],
    [-1, 1, 4],
    [0, 1, 2],
    [1, 1, 1],
];
include_once ("classes/Grid.php");

// Assuming the grid will start from 100, 100 to 200, 200.
// Then it can grow 50 in each direction.
// We also discount 50 in from each edge as the unknown creeps in.
// Its known that the infinite space changes from . to # and back each iteration.
// After an even number of iterations the infinite is . and we can discount it.
// After an odd number of iterations the infinite is # and we can't count all of them.
// Luckily we only have even numbered iterations to check.
$grid = new Grid(300, 300, '.');

for($y = 2; $y < count($lines); $y++) {
    $vals = str_split($lines[$y]);
    foreach ($vals as $x => $val) {
        $grid->setPos($x + 100, $y + 100 - 2, $val);
    }
}

print($grid);

function iterate(Grid $grid, $iter): Grid {
    global $enhanceAlgo;
    global $dirs;
    $next = new Grid($grid->getWidth(), $grid->getHeight());
    for ($y = $iter; $y < $next->getHeight() - $iter; $y++) {
        for ($x = $iter; $x < $next->getWidth() - $iter; $x++) {
            $num = 0;
            foreach ($dirs as $dir) {
                $v = $grid->getPos($x + $dir[0], $y + $dir[1]);
                if ($v == '#') {
                    $num += $dir[2];
                }
            }
            $result = $enhanceAlgo[$num];
            $next->setPos($x, $y, $result);
        }
    }
    return $next;
}

for ($iter = 0; $iter < 50; $iter++) {
    $grid = iterate($grid, $iter);
    echo("Iteration $iter\n");
    echo($grid->subString(100, 100, 200, 200));
    if ($iter == 1) {
        $part1 = $grid->countOccurrences('#');
    }
}

$part2 = $grid->countOccurrences('#');


echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
