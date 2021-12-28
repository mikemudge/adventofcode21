<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

// sample
//$lines = [
//    "[[[0,[5,8]],[[1,7],[9,6]]],[[4,[1,2]],[[1,4],2]]]",
//    "[[[5,[2,8]],4],[5,[[9,9],0]]]",
//    "[6,[[[6,2],[5,6]],[[7,6],[4,7]]]]",
//    "[[[6,[0,7]],[0,9]],[4,[9,[9,0]]]]",
//    "[[[7,[6,4]],[3,[1,3]]],[[[5,5],1],9]]",
//    "[[6,[[7,3],[3,2]]],[[[3,8],[5,7]],4]]",
//    "[[[[5,4],[7,7]],8],[[8,3],8]]",
//    "[[9,3],[[9,9],[6,[4,9]]]]",
//    "[[2,[[7,7],7]],[[5,8],[[9,3],[0,2]]]]",
//    "[[[[5,2],5],[8,[3,7]]],[[5,[7,5]],[4,4]]]"
//];

include_once "classes/SnailNum.php";

function parseSnail(string $snailStr, int $i) {
    if ($snailStr[$i] == '[') {
        // Nested value
        [$left, $i] = parseSnail($snailStr, $i + 1);
        if ($snailStr[$i] != ",") {
            throw new RuntimeException("No comma");
        }
        [$right, $i] = parseSnail($snailStr, $i + 1);
        if ($snailStr[$i] != "]") {
            throw new RuntimeException("No end ]");
        }
        $snailNum = new SnailNum(null, $left, $right);
        return [$snailNum, $i + 1];
    } else {
        $snailNum = new SnailNum(intval($snailStr[$i]));
        return [$snailNum, $i + 1];
    }
}

function addSnail(SnailNum $a, SnailNum $b) {
    // TODO reduce this thing.
    $result = new SnailNum(null, $a, $b);
    $reduce = true;
    while($reduce) {
        $left = null;
        $adding = [];
        $exploded = explodeNum($result, 0, $left, $adding);
        if ($exploded) {
//            echo("Reduced to $result\n");
            continue;
        }
        // No explode happened.
        $reduce = splitNum($result);
    }
    return $result;
}

function splitNum(SnailNum $current): bool {
    if ($current->isLeaf()) {
        if ($current->value > 9) {
            $current->split();
            return true;
        }
        return false;
    } else {
        $result = splitNum($current->left);
        if ($result) {
            return true;
        }
        return splitNum($current->right);
    }
}

function explodeNum(SnailNum $current, int $depth, ?SnailNum &$lastLeaf, &$addingNext): bool {
    if ($current->isLeaf()) {
        if ($addingNext) {
            $current->value += $addingNext[0];
            unset($addingNext[0]);
            return true;
        }
        $lastLeaf = $current;
        return false;
    } else {
        // Ignore reduce nodes if we already have an adding value.
        if (empty($addingNext) && $depth == 4) {
            if ($lastLeaf) {
                $lastLeaf->value += $current->left->value;
            }
            $addingNext[0] = $current->right->value;
            $current->zero();
            return true;
        }
        $result = explodeNum($current->left, $depth + 1, $lastLeaf, $addingNext);
        if ($result && empty($addingNext)) {
            return true;
        }
        return explodeNum($current->right, $depth + 1, $lastLeaf, $addingNext);
    }
}

[$sum, $unused] = parseSnail($lines[0], 0);
for ($i = 1; $i < count($lines); $i++) {
    [$n, $unused] = parseSnail($lines[$i], 0);
    echo(" + $n\n");
    $sum = addSnail($sum, $n);
    echo("$sum\n");
}

$part1 = $sum->getMagnitude();

foreach ($lines as $line1) {
    foreach ($lines as $line2) {
        if ($line1 === $line2) {
            continue;
        }
        [$sn1, $unused] = parseSnail($line1, 0);
        [$sn2, $unused] = parseSnail($line2, 0);
        $sum = addSnail($sn1, $sn2);
        $magnitude = $sum->getMagnitude();
        if ($magnitude > $part2) {
            echo("$line1 + $line2\n = $magnitude\n");
            $part2 = $magnitude;
        }
        // Add in the other order as snailfish math is non commutative
        [$sn1, $unused] = parseSnail($line2, 0);
        [$sn2, $unused] = parseSnail($line1, 0);
        $sum = addSnail($sn2, $sn1);
        $magnitude = $sum->getMagnitude();
        if ($magnitude > $part2) {
            echo("$line2 + $line1\n = $magnitude\n");
            $part2 = $magnitude;
        }
    }
}
echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
