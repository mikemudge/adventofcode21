<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);
$cubes = [];

foreach ($lines as $line) {
    [$ins, $loc] = explode(" ", $line);
    [$xs, $ys, $zs] = explode(",", $loc);
    $xx = explode("=", $xs);
    $yy = explode("=", $ys);
    $zz = explode("=", $zs);
    [$x1, $x2] = array_map('intval', explode("..", $xx[1]));
    [$y1, $y2] = array_map('intval', explode("..", $yy[1]));
    [$z1, $z2] = array_map('intval', explode("..", $zz[1]));
    echo("$ins x=$x1..$x2,y=$y1..$y2,z=$z1..$z2\n");
    $cubes[] = [
        'on' => $ins === "on",
        'pos' => [$x1, $y1, $z1, $x2, $y2, $z2],
    ];
    $allX[] = $x1;
    $allX[] = $x2;
    $allY[] = $y1;
    $allY[] = $y2;
    $allZ[] = $z1;
    $allZ[] = $z2;
}

$cubeState = [];
foreach ($cubes as $cube) {
    [$x1, $y1, $z1, $x2, $y2, $z2] = $cube['pos'];
    for ($x = max($x1, -50); $x <= min($x2, 50); $x++) {
        for ($y = max($y1, -50); $y <= min($y2, 50); $y++) {
            for ($z = max($z1, -50); $z <= min($z2, 50); $z++) {
                $cubeState["$x,$y,$z"] = $cube['on'];
            }
        }
    }
}

foreach($cubeState as $c) {
    if ($c) {
        $part1++;
    }
}

echo("Part 1: " . $part1 . PHP_EOL);
// 594246 is too low (not inclusive of 50).

echo("Part 2: " . $part2 . PHP_EOL);
