<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);
$start1 = 10;
$start2 = 6;

// sample
//$start1 = 4;
//$start2 = 8;

function calculatePart1(int $start1, int $start2): int {

// Increments each time up to 100.
    $incrementing_die = 0;

    $turn = 0;
    $tot_dice_rolls = 0;
    $scores = [0, 0];
    $spaces = [$start1, $start2];
    while(true) {
        $dice_rolls = [
            ++$incrementing_die % 100 ?: 100,
            ++$incrementing_die % 100 ?: 100,
            ++$incrementing_die % 100 ?: 100
        ];
        $tot_dice_rolls += 3;
        $player = $turn % 2;
        $move = array_sum($dice_rolls);
        $spaces[$player] = ($spaces[$player] + $move) % 10 ?: 10;
        $scores[$player] += $spaces[$player];

        // Display
        $player_name = ($player == 0 ? "1": "2");
        $dice = join(",", $dice_rolls);
        $space = $spaces[$player];
        $score = $scores[$player];
        echo("Player $player_name rolls $dice and moves to space $space for a total score of $score\n");
        if ($score >= 1000) {
            break;
        }
        $turn++;
    }

    return $tot_dice_rolls * $scores[1 - $player];
}

// The total roll mapped to how many universes will create that roll.
$rollLikelihood = [
    3 => 1,
    4 => 3,
    5 => 6,
    6 => 7,
    7 => 6,
    8 => 3,
    9 => 1,
];

// Assumes that it is player 0's turn
function calculatePart2(array $state, $depth, array &$cache): array {
    $score = $state['scores'][1];
    if ($score >= 21) {
        // This is a win state for one player.
        // turn 0 will be 1,0 and turn 1 will be 0,1
        return [0, 1];
    }

    $cacheKey = join(",", $state['spaces']) . "-" . join(",", $state['scores']);
    if (isset($cache[$cacheKey])) {
//        echo("cache hit " . $cacheKey . "\n");
        return $cache[$cacheKey];
    }
    // recurse case.
    global $rollLikelihood;
    $result = [0, 0];
    foreach ($rollLikelihood as $roll => $num) {
        // We will always put player 1 into player 0 so it is their turn next.
        // No change to this player as its not their turn.

        // However player 0 does move by the roll.
        $space = ($state['spaces'][0] + $roll) % 10 ?: 10;
        $score = $state['scores'][0] + $space;
        $res = calculatePart2([
            'scores' => [$state['scores'][1], $score],
            'spaces' => [$state['spaces'][1], $space],
        ], $depth + 1, $cache);
        // Because we swapped players we need to unswap the win chances.
        // And also multiply them by the number of universes which lead to that state.
        $result[0] += $num * $res[1];
        $result[1] += $num * $res[0];
    }
//    echo("cache store " . $cacheKey . "\n");
    $cache[$cacheKey] = $result;
    return $result;
}

$part1 = calculatePart1($start1, $start2);

$cache = [];
$state = [
    'spaces' => [$start1, $start2],
    'scores' => [0, 0],
];
$universes = calculatePart2($state, 0, $cache);
print_r($universes);
$part2 = max($universes);

echo(count($cache) . "\n");

echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
