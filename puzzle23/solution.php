<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

// sample
//$input = file_get_contents("$cwd/sample");

$lines = explode("\n", $input);

$costs = [
    'A' => 1,
    'B' => 10,
    'C' => 100,
    'D' => 1000
];

$adjacents = [
    'left' => ['a', 'ab'],
    'a' => ['left', 'ab'],
    'ab' => ['left', 'a', 'b', 'bc'],
    'b' => ['ab', 'bc'],
    'bc' => ['ab', 'b', 'c', 'cd'],
    'c' => ['bc', 'cd'],
    'cd' => ['bc', 'c', 'd', 'right'],
    'd' => ['cd', 'right'],
    'right' => ['d', 'cd']
];

class ReversePQ extends SplPriorityQueue
{
    public function compare($priority1, $priority2): int {
        if ($priority1 === $priority2) return 0;
        return $priority1 > $priority2 ? -1 : 1;
    }
}

function print_state($state) {
    $left = str_pad(join("", $state['left']), 2, ".", STR_PAD_LEFT);
    $right = str_pad(join("", $state['right']), 2, ".", STR_PAD_RIGHT);
    // TODO right might need reversing?

    $ab = $state['ab'] ?: '.';
    $bc = $state['bc'] ?: '.';
    $cd = $state['cd'] ?: '.';
    $a1 = $state['a'][1] ?? '.';
    $a2 = $state['a'][0] ?? '.';
    $b1 = $state['b'][1] ?? '.';
    $b2 = $state['b'][0] ?? '.';
    $c1 = $state['c'][1] ?? '.';
    $c2 = $state['c'][0] ?? '.';
    $d1 = $state['d'][1] ?? '.';
    $d2 = $state['d'][0] ?? '.';
    echo("#############\n");
    echo("#$left.$ab.$bc.$cd.$right#\n");
    echo("###$a1#$b1#$c1#$d1###\n");
    echo("###$a2#$b2#$c2#$d2###\n");
    echo("#############\n");
}

function stateCacheKey($state) {
    $left = str_pad(join("", $state['left']), 2, ".", STR_PAD_LEFT);
    $right = str_pad(join("", $state['right']), 2, ".", STR_PAD_RIGHT);
    $ab = $state['ab'] ?: '.';
    $bc = $state['bc'] ?: '.';
    $cd = $state['cd'] ?: '.';
    $a1 = $state['a'][1] ?? '.';
    $a2 = $state['a'][0] ?? '.';
    $b1 = $state['b'][1] ?? '.';
    $b2 = $state['b'][0] ?? '.';
    $c1 = $state['c'][1] ?? '.';
    $c2 = $state['c'][0] ?? '.';
    $d1 = $state['d'][1] ?? '.';
    $d2 = $state['d'][0] ?? '.';
    return $left . $ab . $bc . $cd . $right . $a1 . $a2 . $b1 . $b2 . $c1 . $c2 . $d1 . $d2;
}

function queue(array $state1, ReversePQ $pq, $verbose = false) {
    if ($verbose) {
        print_state($state1);
        $key = stateCacheKey($state1);
        echo("key = $key\n");
    }
    // TODO hueristic could help performance?
    $p = $state1['cost'] + 0;
    $pq->insert($state1, $p);
}

function getNeighbours(array $state, ReversePQ $pq, $verbose = false) {
    global $adjacents;
    foreach($adjacents as $k => $destinations) {
        $loc = $state[$k];
        if (empty($loc)) {
            // Nothing at this location to move
            continue;
        }
        if (is_array($loc)) {
            $x = $loc[count($loc) - 1];
            if (!$x) {
                print_r($state);
                throw new RuntimeException("Expecting $k to be not empty");
            }
        } else {
            $x = $loc;
        }

        foreach($destinations as $d) {
            if (possibleMove($x, $state, $d)) {
                // TODO calculate cost of this move.
                $cost = costMove($k, $x, $state, $d);
                echo("$x could move from $k to $d for " . $state['cost'] . "+" . $cost . "\n");
                $state1 = $state;
                // Remove the amphipod from the loc.
                if (is_array($state1[$k])) {
                    $amphipod = array_pop($state1[$k]);
                } else {
                    $amphipod = $state1[$k];
                    $state1[$k] = null;
                }
                // And set it to the destination
                if (is_array($state1[$d])) {
                    $state1[$d][] = $amphipod;
                } else {
                    $state1[$d] = $amphipod;
                }
                $state1['cost'] += $cost;
                queue($state1, $pq, $verbose);
            }
        }
    }
}

function costMove(string $k, string $x, array $state, string $d): int {
    global $costs;
    $cost = 0;
    if (in_array($k, ['a','b','c','d'])) {
        $s = count($state[$k]);
        if ($s == 2) {
            $cost += $costs[$x];
        } else {
            // Need to move 2 steps to get out of home.
            $cost += 2 * $costs[$x];
        }
    } elseif (in_array($k, ['left', 'right'])) {
        $occupiers = count($state[$k]);
        if ($occupiers == 2) {
            // Need to move the other occupier out 1.
            $x2 = $state[$k][0];
            $cost += $costs[$x2];
        }
        $cost += $costs[$x];
    } else {
        // Must be ab, bc or cd.
        $cost += $costs[$x];
    }
    if (in_array($d, ['a','b','c','d'])) {
        $s = count($state[$d]);
        if ($s == 1) {
            $cost += $costs[$x];
        } else {
            // Need to move 2 steps to get out of home.
            $cost += 2 * $costs[$x];
        }
    } elseif (in_array($d, ['left', 'right'])) {
        $occupiers = count($state[$d]);
        if ($occupiers == 1) {
            // Need to move the other occupier in 1.
            $x2 = $state[$d][0];
            $cost += $costs[$x2];
        }
        $cost += $costs[$x];
    } else {
        // Must be ab, bc or cd.
        $cost += $costs[$x];
    }
    return $cost;
}

function possibleMove(string $x, array $state, string $d): bool {
    if (in_array($d, ['a','b','c','d'])) {
        if (strtolower($x) != $d) {
            // Can't move to a home you don't live in.
            return false;
        }
        // The pod does live there.
        $occupiers = count($state[$d]);
        if ($occupiers == 2) {
            // Home is full.
            return false;
        }
        if ($occupiers == 1 && $state[$d][0] != $x) {
            // A stranger is at your home.
            return false;
        }
        // no one is home or only your friend is home.
        return true;
    } elseif (in_array($d, ['left', 'right'])) {
        $occupiers = count($state[$d]);
        // Can go here if there is space.
        return $occupiers < 2;
    } else {
        // Must be ab, bc or cd.
        // Can go there if no one is there.
        return is_null($state[$d]);
    }
}

$start = [
    'left' => [],
    'right' => [],
    'a' => [$lines[3][3], $lines[2][3]],
    'b' => [$lines[3][5], $lines[2][5]],
    'c' => [$lines[3][7], $lines[2][7]],
    'd' => [$lines[3][9], $lines[2][9]],
    'ab' => null,
    'bc' => null,
    'cd' => null,
    'cost' => 0,
];

$pq = new ReversePQ();
$pq->insert($start, 0);

$verbose = false;
$visited = [];
$iteration = 0;
$winner = null;
while(!$pq->isEmpty()) {
    $state = $pq->extract();

    if ($state['a'] == ['A', 'A'] && $state['b'] == ['B', 'B'] &&
        $state['c'] == ['C', 'C'] && $state['d'] == ['D', 'D']) {
        // winner found.
        $winner = $state;
        break;
    }

    // Don't revisit states which have been seen before.
    $key = stateCacheKey($state);
    if (array_key_exists($key, $visited)) {
        continue;
    }
    $visited[$key] = true;

    // Only increase for new states.
    $iteration++;
    echo("Iteration $iteration getting next state, size = " . $pq->count() . "\n");
//    if ($key == "...D....ABBCCDA") {
//        $verbose = true;
//    }
//    if ($state['cost'] == 20 && $state['bc'] == 'B') {
//        $verbose = true;
//    }
    // calculate neighbours and insert.
    if ($verbose) {
        echo("Cost: " . $state['cost'] . "\n");
        print_state($state);
        echo("key = $key\n");
    }

    getNeighbours($state, $pq, $verbose);
    if ($verbose) {
        break;
    }
}

if ($winner) {
    echo("Found winner\n");
    echo("Cost: " . $winner['cost'] . "\n");
    $part1 = $winner['cost'];
    print_state($winner);
} else {
    echo("No winner found\n");
}

echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
