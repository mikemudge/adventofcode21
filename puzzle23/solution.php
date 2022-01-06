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

$rooms = ['a','b','c','d'];
$hallways = ['left2', 'left', 'ab', 'bc', 'cd', 'right', 'right2'];
$locations = ['left2', 'left', 'a', 'ab', 'b', 'bc', 'c', 'cd', 'd', 'right', 'right2'];
$indexes = array_flip($locations);

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
    $a = str_pad(join("", $state['a']), 4, ".", STR_PAD_RIGHT);
    $b = str_pad(join("", $state['b']), 4, ".", STR_PAD_RIGHT);
    $c = str_pad(join("", $state['c']), 4, ".", STR_PAD_RIGHT);
    $d = str_pad(join("", $state['d']), 4, ".", STR_PAD_RIGHT);
    return $left . $ab . $bc . $cd . $right . $a . $b . $c . $d;
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

function hueristicCost(array $state): int {
    global $costs;
    global $rooms;
    global $indexes;
    global $hallways;

    $cost = 0;
    foreach ($rooms as $r) {
        // See how many are in the right place.
        $expected = strtoupper($r);
        $correct = 0;
        foreach($state[$r] as $occupier) {
            if ($occupier != $expected) {
                break;
            }
            $correct++;
        }
        // Number of amphipods which need to move from the hallway into the room.
        $moving = 4 - $correct;
        $steps = $moving * ($moving + 1) / 2;
        $cost += $steps * $costs[$expected];

        // Also calculate the steps and cost to move the amphipods out of the room.
        // starting after the correct ones (which don't move)
        for($i = $correct; $i < count($state[$r]); $i++) {
            // This is steps to exit the room to the hall.
            $steps = 5 - $i;
            // This is the amphipod type (A,B,C or D)
            $x = $state[$r][$i];
            $destRoom = strtolower($x);
            // This is how many steps along the hall to outside their room.
            $hallSteps = abs($indexes[$destRoom] - $indexes[$r]);
            $cost += ($steps + $hallSteps) * $costs[$x];
        }
    }

    // Now we just need to calculate the movement for amphipod's in the hallway.
    foreach ($hallways as $h) {
        if (empty($state[$h])) {
            continue;
        }
        $x = $state[$h];
        $destRoom = strtolower($x);
        $steps = abs($indexes[$h] - $indexes[$destRoom]);
        $cost += $steps * $costs[$x];
    }
    return $cost;
}

function getNeighbours(array $state, ReversePQ $pq, $verbose = false) {
    global $indexes;
    global $rooms;
    global $hallways;
    global $costs;
    foreach ($rooms as $room) {
        if (empty($state[$room])) {
            continue;
        }
        // Get the top amphipod in the room
        $amphipod = $state[$room][count($state[$room]) - 1];
        foreach($hallways as $dest) {
            if (possibleHallMove($room, $state, $dest)) {
                // Move along the hallway to outside the room.
                $steps = abs($indexes[$room] - $indexes[$dest]);
                // Then move into the room
                // cost is 4, 3, 2, 1 based on 1, 2, 3, 4 pods in the room.
                $steps += 5 - count($state[$room]);
                $cost = $steps * $costs[$amphipod];
                if ($verbose) {
                    echo("$amphipod could move from $room to $dest for " . $state['cost'] . "+" . $cost . "\n");
                }
                // move
                $state1 = $state;
                // Move the pod from the room to the hall location.
                $amphipod = array_pop($state1[$room]);
                $state1[$dest] = $amphipod;
                $state1['cost'] += $cost;
                queue($state1, $pq, $verbose);
            }
        }
    }
    foreach($hallways as $hall) {
        if (empty($state[$hall])) {
            continue;
        }
        $amphipod = $state[$hall];
        // Only one possible room this could move into.
        $destRoom = strtolower($amphipod);
        if (possibleRoomMove($hall, $amphipod, $state, $destRoom)) {
            // Move to the hallway.
            // cost is 4, 3, 2, 1 based on 0, 1, 2, 3 pods in the room.
            $steps = 4 - count($state[$destRoom]);
            // then move along the hallway to the new location.
            $steps += abs($indexes[$destRoom] - $indexes[$hall]);
            $cost = $steps * $costs[$amphipod];
            if ($verbose) {
                echo("$amphipod could move from $hall to $destRoom for " . $state['cost'] . "+" . $cost . "\n");
            }

            // move.
            $state1 = $state;
            $state1[$hall] = null;
            $state1[$destRoom][] = $amphipod;
            $state1['cost'] += $cost;
            queue($state1, $pq, $verbose);
        }
    }
}

function isHallClear(array $state, string $a, string $b): bool {
    global $indexes;
    global $locations;
    $left = min($indexes[$a], $indexes[$b]);
    $right = max($indexes[$a], $indexes[$b]);
    for ($i = $left + 1; $i < $right; $i++) {
        $locName = $locations[$i];
        if (in_array($locName, ['a','b','c','d'])) {
            // The space outside a room must be empty.
            continue;
        }
        if (!empty($state[$locName])) {
            // Someones in the way.
            return false;
        }
    }

    // Check for blockers between a and b
    return true;
}

function possibleRoomMove(string $hall, mixed $amphipod, array $state, string $destRoom): bool {
    if (!isHallClear($state, $hall, $destRoom)) {
        // Can't use the hallway to get there.
        return false;
    }
    foreach ($state[$destRoom] as $o) {
        if ($o != $amphipod) {
            // A stranger is at your home so you can't go there.
            return false;
        }
    }
    // no one is home or only your friends are home.
    return true;
}

function possibleHallMove(string $room, array $state, string $dest): bool {
    if (!isHallClear($state, $dest, $room)) {
        // Can't use the hallway to get there.
        return false;
    }
    // Make sure the destination is also clear.
    return is_null($state[$dest]);
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
];

$pq = new ReversePQ();
$pq->insert($start, 0);

echo("hCost = " . hueristicCost($start) . "\n");
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
    echo("Iteration $iteration getting next state, size = " . $pq->count() . " cost = ". $state['cost'] . "\n");
    if ($key == ".......AAAABBBBCCCCDDDD") {
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
// 47768 is too high
