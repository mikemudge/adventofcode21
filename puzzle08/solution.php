<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$numbers = explode(",", $lines[0]);
$part1 = 0;
$part2 = 0;

foreach ($lines as $line) {
    [$patterns, $outputs] = explode(" | ", $line);
    $patterns = explode(" ", $patterns);
    $outputs = explode(" ", $outputs);
    foreach ($outputs as $o) {
        $c = strlen($o);
        // 1 uses 2 segments.
        // 4 uses 4 segments.
        // 7 uses 3 segments.
        // 8 uses 7 segments.
        if ($c == 2 || $c == 3 || $c == 4 || $c == 7) {
            $part1++;
        }
    }
    $num = array_fill(0,10, "");
    foreach ($patterns as $p) {
        $ps = str_split($p);
        sort($ps);
        $p = implode($ps);
        $c = strlen($p);
        if ($c == 2) {
            $num[1] = $p;
        } else if ($c == 3) {
            $num[7] = $p;
        } else if ($c == 4) {
            $num[4] = $p;
        } else if ($c == 7) {
            $num[8] = $p;
        } else if ($c == 6) {
            // 0,6,9 have 6.
            $p069[] = $p;
        } else {
            // 2,3,5 have 5.
            $p235[] = $p;
        }
    }

    // Now determine which is which for these possibles using 1 and 4.
    [$c1a, $c1b] = str_split($num[1]);
    $c4 = str_split($num[4]);
    foreach ($p069 as $p) {
        if (str_contains($p, $c1a) && str_contains($p, $c1b)) {
            // 9 and 0 both contain 1.
            $c4count = 0;
            foreach ($c4 as $c) {
                if (str_contains($p, $c)) {
                    $c4count++;
                }
            }
            if ($c4count == 4) {
                $num[9] = $p;
            } else {
                $num[0] = $p;
            }
        } else {
            // 6 is the only number which doesn't contain 1.
            $num[6] = $p;
        }
    }
    foreach ($p235 as $p) {
        if (str_contains($p, $c1a) && str_contains($p, $c1b)) {
            // Only 3 contains 1
            $num[3] = $p;
        } else {
            $c4count = 0;
            // 2 and 5 both contain half of 1.
            foreach ($c4 as $c) {
                if (str_contains($p, $c)) {
                    $c4count++;
                }
            }
            if ($c4count == 3) {
                $num[5] = $p;
            } else {
                $num[2] = $p;
            }
        }
    }

    print_r($num);
    $patternNums = array_flip($num);
    print_r($patternNums);
    $val = 0;
    foreach ($outputs as $o) {
        $os = str_split($o);
        sort($os);
        $o = implode($os);
        $val *= 10;
        $val += $patternNums[$o];
    }
    print($val . "\n");
    $part2 += $val;
}

echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
