<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

// sample
//$input = file_get_contents("$cwd/sample");

include_once ("classes/Cube.php");

$lines = explode("\n", $input);
$cubes = [];
// Adding a cube like this makes it easy for us to calculate part1.
$cubes[] = $part1Cube = new Cube([[-50,50],[-50,50],[-50,50]], false, "part1");
$cubeId = 1;
foreach ($lines as $line) {
    [$ins, $loc] = explode(" ", $line);
    [$xs, $ys, $zs] = explode(",", $loc);
    $xx = explode("=", $xs);
    $yy = explode("=", $ys);
    $zz = explode("=", $zs);
    $dimensions = [
        array_map('intval', explode("..", $xx[1])),
        array_map('intval', explode("..", $yy[1])),
        array_map('intval', explode("..", $zz[1]))
    ];
    $cubes[] = new Cube($dimensions, $ins == 'on', $cubeId++);
}

print_r($cubes);

$onCount = 0;
for($i = 0; $i < count($cubes); $i++) {
    $c = $cubes[$i];

    echo("Adding $c\n");

    // Iterate over each cube we have (so far) to see if they need to update their state.
    for($ii = 0; $ii < $i; $ii++) {
        $c2 = $cubes[$ii];

        $intersectionCube = $c->intersection($c2);
        // Null means no overlap.
        if (!$intersectionCube) {
            continue;
        }
        // c2 is no longer responsible for the region inside this intersection.
        // c will be responsible for it instead.
        $c2->addSubCube($intersectionCube);
        echo("$c2 lost responsibility " . $c2->getResponsibility(). "\n");
    }
}

for($i = 0; $i < count($cubes); $i++) {
    if ($cubes[$i]->getData()) {
        // If I'm on then all of my responsibility counts as on.
        $part1 += $cubes[$i]->getResponsibility();
    }
}

echo("Part 1: " . $part1 . PHP_EOL);
// 594246 is too low (not inclusive of 50).

echo("Part 2: " . $part2 . PHP_EOL);
