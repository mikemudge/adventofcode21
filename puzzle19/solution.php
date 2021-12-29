<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

// sample
//$lines = [
//    "--- scanner 0 ---",
//    "404,-588,-901",
//    "528,-643,409",
//    "-838,591,734",
//    "390,-675,-793",
//    "-537,-823,-458",
//    "-485,-357,347",
//    "-345,-311,381",
//    "-661,-816,-575",
//    "-876,649,763",
//    "-618,-824,-621",
//    "553,345,-567",
//    "474,580,667",
//    "-447,-329,318",
//    "-584,868,-557",
//    "544,-627,-890",
//    "564,392,-477",
//    "455,729,728",
//    "-892,524,684",
//    "-689,845,-530",
//    "423,-701,434",
//    "7,-33,-71",
//    "630,319,-379",
//    "443,580,662",
//    "-789,900,-551",
//    "459,-707,401",
//    "",
//    "--- scanner 1 ---",
//    "686,422,578",
//    "605,423,415",
//    "515,917,-361",
//    "-336,658,858",
//    "95,138,22",
//    "-476,619,847",
//    "-340,-569,-846",
//    "567,-361,727",
//    "-460,603,-452",
//    "669,-402,600",
//    "729,430,532",
//    "-500,-761,534",
//    "-322,571,750",
//    "-466,-666,-811",
//    "-429,-592,574",
//    "-355,545,-477",
//    "703,-491,-529",
//    "-328,-685,520",
//    "413,935,-424",
//    "-391,539,-444",
//    "586,-435,557",
//    "-364,-763,-893",
//    "807,-499,-711",
//    "755,-354,-619",
//    "553,889,-390",
//    "",
//    "--- scanner 2 ---",
//    "649,640,665",
//    "682,-795,504",
//    "-784,533,-524",
//    "-644,584,-595",
//    "-588,-843,648",
//    "-30,6,44",
//    "-674,560,763",
//    "500,723,-460",
//    "609,671,-379",
//    "-555,-800,653",
//    "-675,-892,-343",
//    "697,-426,-610",
//    "578,704,681",
//    "493,664,-388",
//    "-671,-858,530",
//    "-667,343,800",
//    "571,-461,-707",
//    "-138,-166,112",
//    "-889,563,-600",
//    "646,-828,498",
//    "640,759,510",
//    "-630,509,768",
//    "-681,-892,-333",
//    "673,-379,-804",
//    "-742,-814,-386",
//    "577,-820,562",
//    "",
//    "--- scanner 3 ---",
//    "-589,542,597",
//    "605,-692,669",
//    "-500,565,-823",
//    "-660,373,557",
//    "-458,-679,-417",
//    "-488,449,543",
//    "-626,468,-788",
//    "338,-750,-386",
//    "528,-832,-391",
//    "562,-778,733",
//    "-938,-730,414",
//    "543,643,-506",
//    "-524,371,-870",
//    "407,773,750",
//    "-104,29,83",
//    "378,-903,-323",
//    "-778,-728,485",
//    "426,699,580",
//    "-438,-605,-362",
//    "-469,-447,-387",
//    "509,732,623",
//    "647,635,-688",
//    "-868,-804,481",
//    "614,-800,639",
//    "595,780,-596",
//    "",
//    "--- scanner 4 ---",
//    "727,592,562",
//    "-293,-554,779",
//    "441,611,-461",
//    "-714,465,-776",
//    "-743,427,-804",
//    "-660,-479,-426",
//    "832,-632,460",
//    "927,-485,-438",
//    "408,393,-506",
//    "466,436,-512",
//    "110,16,151",
//    "-258,-428,682",
//    "-393,719,612",
//    "-211,-452,876",
//    "808,-476,-593",
//    "-575,615,604",
//    "-485,667,467",
//    "-680,325,-822",
//    "-627,-443,-432",
//    "872,-547,-609",
//    "833,512,582",
//    "807,604,487",
//    "839,-516,451",
//    "891,-625,532",
//    "-652,-548,-490",
//    "30,-46,-14",
//];

function checkBeaconAt(array $result, int $x, int $y, int $z): bool {
    foreach($result as $beacon) {
        if ($beacon[0] == $x && $beacon[1] == $y && $beacon[2] == $z) {
            return true;
        }
    }
    return false;
}

function checkScanner(array $result, array $scanner, array $rotation, array $orientation, $beacon, $loc): ?array {
    // Where is the scanner if it can see this beacon at $x, $y, $z?
    $sx = $loc[0] - $beacon[$rotation[0]] * $orientation[0];
    $sy = $loc[1] - $beacon[$rotation[1]] * $orientation[1];
    $sz = $loc[2] - $beacon[$rotation[2]] * $orientation[2];

    $verbose = false;
    if ($sx == 68 && $sy ==-1246 && $sz == -43) {
        $verbose = true;
    };

    // See if enough other beacons align.
    $matches = 0;
    $beacons = $scanner['beacons'];
    foreach ($beacons as $i => $b) {
        $x = $sx + $b[$rotation[0]] * $orientation[0];
        $y = $sy + $b[$rotation[1]] * $orientation[1];
        $z = $sz + $b[$rotation[2]] * $orientation[2];
        // There should be a beacon at x, y, z if this scanner location is correct.
        $found = checkBeaconAt($result, $x, $y, $z);
        if ($found) {
            $matches++;
        }
        // No beacon found.
        // could be outside of the scanned area?
        // if its in scan range of any other scanner then it must be found.
        // TODO check if x, y, z is scanned by any scanner so far.
    }
    // matches will be 1 most of the time.
    if ($matches > 1) {
//        echo("using real beacon at " . join(",", $loc) . "\n");
//        echo("Scanner " . $scanner['id'] . " possibly at $sx, $sy, $sz found $matches matches\n");
    }
    if ($matches >= 12) {
        // Found enough matches that we can say this scanner can go here.
        return [$sx, $sy, $sz];
    }
    // Not enough matches
    return null;
}

function findOverlap(array $result, mixed $scanner) {
    $rotations = [
        [0, 1, 2],
        [0, 2, 1],
        [1, 0, 2],
        [1, 2, 0],
        [2, 0, 1],
        [2, 1, 0],
    ];
    $orientations = [
        [1, 1, 1],
        [1, 1, -1],
        [1, -1, 1],
        [1, -1, -1],
        [-1, 1, 1],
        [-1, 1, -1],
        [-1, -1, 1],
        [-1, -1, -1]
    ];
    $beacons = $scanner['beacons'];
    foreach ($beacons as $beacon) {
        foreach($result as $loc) {
            // Here we assume that beacon is a match for loc (where a real beacon is).
            foreach ($orientations as $orientation) {
                foreach ($rotations as $rotation) {
                    $match = checkScanner($result, $scanner, $rotation, $orientation, $beacon, $loc);
                    if ($match) {
                        return [$match, $rotation, $orientation];
                    }
                }
            }
        }
    }
    return null;
}


$scanners = [];
$current = [];
for ($i=1;$i<count($lines); $i++) {
    $line = $lines[$i];
    if (empty($line)) {
        echo("New scanner " . count($scanners) . "\n");
        $scanners[] = [
            'id' => count($scanners),
            'beacons' => $current
        ];
        $current = [];
        // Skip over the line like "--- scanner X ---"
        $i++;
        continue;
    }
    $beacon = array_map("intval", explode(",", $line));
    $current[] = $beacon;
    echo(join(",", $beacon) . PHP_EOL);
}
// Add the last one.
echo("New scanner " . count($scanners) . "\n");
$scanners[] = [
    'id' => count($scanners),
    'beacons' => $current
];


$knownBeacons = [];
// Assume scanner 1 is at 0,0,0 and orientated +x,+y,+z.
$scanner = array_shift($scanners);
foreach ($scanner['beacons'] as $beacon) {
    $knownBeacons[] = $beacon;
}
$knownScanners[] = [0, 0, 0];

$iter = 0;
$last_count = 0;
while (!empty($scanners)) {
    foreach ($scanners as $scannerIdx => $scanner) {
        echo("Check scanner " . $scanner['id'] . " with " . count($scanner['beacons']) . " beacons\n");
        $beacons = $scanner['beacons'];
        // Try every scanner relative beacon as every known beacon.
        $overlap = findOverlap($knownBeacons, $scanner);
        if ($overlap) {
            // This is good enough to include into our actual map.
            [$match, $rotation, $orientation] = $overlap;
            echo("Scanner " . $scanner['id'] . " overlaps at " . join(",", $match) . " with direction " . join(",", $rotation) . " orientation " . join(",", $orientation) . "\n");

            [$sx, $sy, $sz] = $match;
            $knownScanners[] = $match;
            $matches = 0;
            $beacons = $scanner['beacons'];
            foreach ($beacons as $i => $b) {
                $x = $sx + $b[$rotation[0]] * $orientation[0];
                $y = $sy + $b[$rotation[1]] * $orientation[1];
                $z = $sz + $b[$rotation[2]] * $orientation[2];
                $found = checkBeaconAt($knownBeacons, $x, $y, $z);
                if (!$found) {
                    // Add a beacon here.
                    $knownBeacons[] = [$x, $y, $z];
                }
            }
            // Remove this scanner from the list.
            unset($scanners[$scannerIdx]);
        }
    }
    $iter++;
    $c = count($scanners);
    echo("Iteration $iter remaining scanners: $c\n");
    if ($c == $last_count) {
        echo("Got stuck.");
        // No new scanners found.
        break;
    }
    $last_count = $c;
}

$part1 = count($knownBeacons);

print_r($knownScanners);

foreach ($knownScanners as $s) {
    foreach ($knownScanners as $s2) {
        $dis = abs($s[0] - $s2[0]) +  abs($s[1] - $s2[1]) + abs($s[2] - $s2[2]);
        if ($dis > $part2) {
            $part2 = $dis;
        }
    }
}
echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
