<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

//sample
//$lines = [
//    "target area: x=20..30, y=-10..-5"
//];

$t = substr($lines[0], strlen("target area: "));
[$x, $y] = explode(", ", $t);
$x = explode("=", $x);
$y = explode("=", $y);
[$x1, $x2] = array_map('intval', explode("..", $x[1]));
[$y1, $y2] = array_map('intval', explode("..", $y[1]));

echo("$x1, $y1 - $x2, $y2\n");

// At vx = 23, the probe will stop at 23*24/2 = 276
for ($ivx = 1; $ivx < 286; $ivx++) {
    $stopDis = $ivx * ($ivx + 1) / 2;
    if ($stopDis > $x1 && $stopDis < $x2) {
        // After $t the probe will stop moving horizontally and will be in X range.
        $vx = $ivx;
        echo("will be at x = $stopDis (in range) above time $ivx\n");
    };
}

$vy = 0;
// Using the idea that shooting up will come back through y = 0 at t = (vy * 2)
// At this time vy = -ivy because it decrements by 1 each step.
for ($ivy = -$y1 - 1; $ivy > -$y2; $ivy--) {
    $t = $ivy * 2 + 1;
    echo("vy = $ivy will be in y range at step $t\n");
    $vy = $ivy;
    if ($t > $vx) {
        break;
    }
}

echo("initial velocity $vx, $vy\n");
$part1 = $vy * ($vy + 1) / 2;

// Assuming x is positive.
// Problem is reflective so we can flip negative x if needed.
function tryShot($ivx, $ivy, $x1, $x2, $y1, $y2) {
    $x = 0;
    $y = 0;
    $vx = $ivx;
    $vy = $ivy;
    while(true) {
        $x += $vx;
        $y += $vy;
        if ($vx > 0) {
            $vx--;
        } else {
            // X is not changing.
            if ($x < $x1) {
                // Never going to reach the min range.
//                echo("x not reached\n");
                return false;
            }
        }
        if ($x > $x2) {
            // Overshot x
//            echo("x exceeded\n");
            return false;
        }
        $vy--;
        if ($y < $y1) {
            // Off the bottom
//            echo("y exceeded\n");
            return false;
        }
        if ($y <= $y2 && $x >= $x1) {
            return true;
        }
    }
}

// For part2 we use brute force.
// This can probably be optimized but its not really worth it as its fast enough.
for ($ivy = $y1; $ivy <= -$y1; $ivy++) {
    // Check what range of x to try.
    // TODO we could look at when $y will put us in y range first?
    for ($ivx = 0; $ivx <= $x2; $ivx++) {
        if (tryShot($ivx, $ivy, $x1, $x2, $y1, $y2)) {
            echo("$ivx,$ivy\t");
            $part2++;
        }
    }
}
echo("\n");

echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
