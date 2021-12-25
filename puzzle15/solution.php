<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");
// Sample
//$input = file_get_contents("$cwd/sample");

$lines = explode("\n", $input);

$part1 = 0;
$part2 = 0;

class ReversePQ extends SplPriorityQueue
{
    public function compare($priority1, $priority2): int {
        if ($priority1 === $priority2) return 0;
        return $priority1 > $priority2 ? -1 : 1;
    }
}


include_once ("classes/Grid.php");

$grid = new Grid(strlen($lines[0]), count($lines));

foreach ($lines as $y => $line) {
    foreach (str_split($line) as $x => $v) {
        $grid->setPos($x, $y, intval($v));
    }
}

function astar(Grid $grid) {
    $endX = $grid->getWidth() - 1;
    $endY = $grid->getHeight() - 1;

    $visited = new Grid($grid->getWidth(), $grid->getHeight());

    echo("Find path from 0,0 to $endX, $endY" . PHP_EOL);

    $start = [0,0];
    $pq = new ReversePQ();
    $pq->insert([
        'loc' => $start,
        'cost' => 0,
    ], 0);
    while(!$pq->isEmpty()) {
        $state = $pq->extract();
        // calculate neighbours and insert.
        [$ix, $iy] = $state['loc'];

        if ($endX == $ix && $endY == $iy) {
            echo("Winner found $ix, $iy, " . $state['cost'] . PHP_EOL);
            return $state;
        }

        // This means no paths will come back here.
        $visited->setPos($ix, $iy, 1);

        // Next directions.
        $possible = [
            [$ix, $iy + 1],
            [$ix, $iy - 1],
            [$ix + 1, $iy],
            [$ix - 1, $iy]
        ];

        foreach($possible as $loc) {
            [$x, $y] = $loc;
            $stepCost = $grid->getPos($x, $y);
            if ($stepCost == null) {
                // Can't go here.
                continue;
            }
            if ($visited->getPos($x, $y)) {
                // Don't go backwards.
                continue;
            }
            // This is the G component of A-Star. "cost of the path from the start node to n"
            $cost = $state['cost'] + $stepCost;
            // This is the H component of A-Star. "estimated cost of cheapest path to the goal"
            // Just manhatten distance (assumes all node costs are 1).
            $disToGoal = abs($endX - $x) + abs($endY - $y);
            $priority = $cost + $disToGoal;
            $pq->insert([
                'loc' => [$x, $y],
                'cost' => $cost
            ], $priority);
        }
    }
    // No winner found???
    throw new RuntimeException("astar failed");
}


echo($grid);
$winner = astar($grid);
$part1 = $winner['cost'];

// Make the grid 5x5.
$grid5 = new Grid($grid->getWidth() * 5, $grid->getHeight() * 5);
for ($y = 0; $y < $grid->getHeight(); $y++) {
    for ($x = 0; $x < $grid->getWidth(); $x++) {
        $v = $grid->getPos($x, $y);
        for ($y2 = 0; $y2 < 5; $y2++) {
            for ($x2 = 0; $x2 < 5; $x2++) {
                $nv = $v + $y2 + $x2;
                if ($nv > 9) {
                    $nv -= 9;
                }
                $grid5->setPos($x + $x2 * $grid->getWidth(), $y + $y2 * $grid->getHeight(), $nv);
            }
        }
    }
}

$winner = astar($grid5);
$part2 = $winner['cost'];

echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
// 2998 is too high.
