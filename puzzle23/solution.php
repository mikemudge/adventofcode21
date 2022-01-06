<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

// sample
$input = file_get_contents("$cwd/sample");

$lines = explode("\n", $input);

$costs = [
    'A' => 1,
    'B' => 10,
    'C' => 100,
    'D' => 1000
];

$adjacents = [
    'left2' => ['left'],
    'left' => ['a', 'ab', 'left2'],
    'a' => ['left', 'ab'],
    'ab' => ['left', 'a', 'b', 'bc'],
    'b' => ['ab', 'bc'],
    'bc' => ['ab', 'b', 'c', 'cd'],
    'c' => ['bc', 'cd'],
    'cd' => ['bc', 'c', 'd', 'right'],
    'd' => ['cd', 'right'],
    'right' => ['d', 'cd', 'right2'],
    'right2' => ['right']
];

$lookupIndex = [
    'left2',
    'left',
    'ab',
    'bc',
    'cd',
    'right',
    'right2'
];

class ReversePQ extends SplPriorityQueue
{
    public function compare($priority1, $priority2): int {
        if ($priority1 === $priority2) return 0;
        return $priority1 > $priority2 ? -1 : 1;
    }
}

function print_state($state) {
    $left = ($state['left2'] ?: '.') . ($state['left'] ?: '.');
    $right = ($state['right'] ?: '.') . ($state['right2'] ?: '.');

    $ab = $state['ab'] ?: '.';
    $bc = $state['bc'] ?: '.';
    $cd = $state['cd'] ?: '.';
    $a = str_pad(join("", $state['a']), 4, ".", STR_PAD_RIGHT);
    $b = str_pad(join("", $state['b']), 4, ".", STR_PAD_RIGHT);
    $c = str_pad(join("", $state['c']), 4, ".", STR_PAD_RIGHT);
    $d = str_pad(join("", $state['d']), 4, ".", STR_PAD_RIGHT);
    echo("#############\n");
    echo("#$left.$ab.$bc.$cd.$right#\n");
    for ($i = 3; $i >= 0; $i--) {
        echo("###" . $a[$i] . "#" . $b[$i] . "#" . $c[$i] . "#" . $d[$i] . "###\n");
    }
    echo("#############\n");
}

function stateCacheKey($state) {
    $left = ($state['left2'] ?: '.') . ($state['left'] ?: '.');
    $right = ($state['right'] ?: '.') . ($state['right2'] ?: '.');
    $ab = $state['ab'] ?: '.';
    $bc = $state['bc'] ?: '.';
    $cd = $state['cd'] ?: '.';
    $cur = $state['cur'] ?: 'X';
    $homer = $state['homer'] ?: 'X';
    $a = str_pad(join("", $state['a']), 4, ".", STR_PAD_RIGHT);
    $b = str_pad(join("", $state['b']), 4, ".", STR_PAD_RIGHT);
    $c = str_pad(join("", $state['c']), 4, ".", STR_PAD_RIGHT);
    $d = str_pad(join("", $state['d']), 4, ".", STR_PAD_RIGHT);
    return $left . $ab . $bc . $cd . $right . $a . $b . $c . $d . $cur . $homer;
}

function queue(array $state1, ReversePQ $pq, $verbose = false) {
    if ($verbose) {
        print_state($state1);
        $key = stateCacheKey($state1);
        echo("key = $key\n");
    }
    // TODO hueristic could help performance?
    $p = $state1['cost'] + hueristicCost($state1);
    $pq->insert($state1, $p);
}

function hueristicCost(array $state) {
//    return 0;
    // This doesn't seem to help.
    global $costs;
    $cost = 0;
    foreach (['a','b','c','d'] as $l)
    foreach ($state[$l] as $i=>$pod) {
        $letter_diff = abs(ord($l) - ord(strtolower($pod)));
        if ($letter_diff > 0) {
            $steps = 2 * $letter_diff + (5 - $i);
            $cost1 = $costs[$pod] * $steps;
//            echo("$l, $pod, $letter_diff, $steps, $cost1\n");
            $cost += $cost1;
        } else {
//            echo("$l, $pod, in the right place\n");
        }
    }
    return $cost;
}

function getNeighbours(array $state, ReversePQ $pq, $verbose = false) {
    global $adjacents;
    global $lookupIndex;
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
            if (possibleMove($k, $x, $state, $d)) {
                $cost = costMove($k, $x, $state, $d);
                if ($verbose) {
                    echo("$x could move from $k to $d for " . $state['cost'] . "+" . $cost . "\n");
                }
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

                $cur = array_search($d, $lookupIndex);
                if ($cur !== false) {
                    // If this is the homer, we just move the location.
                    if ($state1['homer']) {
                        $state1['homer'] = $cur;
                    }
                    if ($state1['cur']) {
                        if ($k != $lookupIndex[$state1['cur']]) {
                            // It was not the cur which was moved.
                            // Therefore this must be the new homer.
                            $state1['homer'] = $cur;
                        };
                    }
                    // Set cur to this as it was the last to move.
                    $state1['cur'] = $cur;
                } else {
                    // If someone just went home both these are reset.
                    $state1['cur'] = null;
                    $state1['homer'] = null;
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
        $steps = 5 - $s;
        $cost += $steps * $costs[$x];
    } elseif (in_array($k, ['left2', 'right2'])) {
        // No cost, moving to the left/right will cost 1.
    } else {
        // Must be ab, bc or cd.
        $cost += $costs[$x];
    }
    if (in_array($d, ['a','b','c','d'])) {
        $s = count($state[$d]);
        $steps = 4 - $s;
        $cost += $steps * $costs[$x];
    } elseif (in_array($d, ['left2', 'right2'])) {
        // No cost, moving from the left/right will cost 1.
    } else {
        // Must be ab, bc or cd.
        $cost += $costs[$x];
    }
    return $cost;
}

function possibleMove(string $k, string $x, array $state, string $d): bool {
    global $lookupIndex;
    if ($state['homer']) {
        $allowedMove = $lookupIndex[$state['homer']];
        if ($allowedMove != $k) {
            return false;
        }
        // Only this pod can move until it gets home.
    }
    if (in_array($d, ['a','b','c','d'])) {
        if (strtolower($x) != $d) {
            // Can't move to a home you don't live in.
            return false;
        }
        // The pod does live there.
        $occupiers = count($state[$d]);
        if ($occupiers == 4) {
            // Home is full.
            return false;
        }
        if ($occupiers > 0) {
            foreach ($state[$d] as $o) {
                if ($o != $x) {
                    // A stranger is at your home.
                    return false;
                }
            }
        }
        // no one is home or only your friends are home.
        return true;
    } else {
        // Must be ab, bc or cd.
        // Can go there if no one is there.
        return is_null($state[$d]);
    }
}

$start = [
    'left' => null,
    'left2' => null,
    'right' => null,
    'right2' => null,
    'a' => [$lines[5][3], $lines[4][3], $lines[3][3], $lines[2][3]],
    'b' => [$lines[5][5], $lines[4][5], $lines[3][5], $lines[2][5]],
    'c' => [$lines[5][7], $lines[4][7], $lines[3][7], $lines[2][7]],
    'd' => [$lines[5][9], $lines[4][9], $lines[3][9], $lines[2][9]],
    'ab' => null,
    'bc' => null,
    'cd' => null,
    'cost' => 0,
    'cur' => null,
    'homer' => null,
];

$pq = new ReversePQ();
$pq->insert($start, 0);

hueristicCost($start);
$verbose = false;
$visited = [];
$iteration = 0;
$winner = null;
while(!$pq->isEmpty()) {
    $state = $pq->extract();

    if ($state['a'] == ['A', 'A', 'A', 'A'] && $state['b'] == ['B', 'B', 'B', 'B'] &&
        $state['c'] == ['C', 'C', 'C', 'C'] && $state['d'] == ['D', 'D', 'D', 'D']) {
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
    if ($key == "AA...ADAD..BBBBCCCCDD..XX") {
        $verbose = true;
//        print_r($state);
    }
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
