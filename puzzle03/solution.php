<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$b0 = array_fill(0, strlen($lines[0]), 0);
$b1 = array_fill(0, strlen($lines[0]), 0);
foreach($lines as $line) {
    $chars = str_split($line);
    foreach ($chars as $i => $c) {
        if ($c == "0") {
            $b0[$i]++;
        } else if ($c == "1") {
            $b1[$i]++;
        } else {
            error_log("Unknown char $c");
        }
    }
}
print_r($b0);
print_r($b1);

$epsilonRate = "";
$gammaRate = "";
foreach ($b0 as $b) {
    if ($b > 500) {
        // 0 was more common in this index.
        $epsilonRate .= "1";
        $gammaRate .= "0";
    } else {
        $epsilonRate .= "0";
        $gammaRate .= "1";
    }
}

$epsilonRate = bindec($epsilonRate);
$gammaRate = bindec($gammaRate);

echo("$epsilonRate, $gammaRate" . PHP_EOL);
echo("Part 1: " . $epsilonRate * $gammaRate . PHP_EOL);

include_once ("classes/Node.php");

$root = new Node();
foreach($lines as $line) {
    $chars = str_split($line);
    $root->insertPath(0, $chars);
}
echo("Node $root" . PHP_EOL);

$com = $root;
while($com->getValue() == null) {
    echo("Node $com" . PHP_EOL);
    if ($com->getLeftChildren() > $com->getRightChildren()) {
        $com = $com->getLeft();
    } else {
        $com = $com->getRight();
    }
}

$lcom = $root;
while($lcom->getValue() == null) {
    echo("Node $lcom" . PHP_EOL);
    if ($lcom->getRightChildren() >= $lcom->getLeftChildren()) {
        if ($lcom->getLeft() != null) {
            $lcom = $lcom->getLeft();
        } else {
            $lcom = $lcom->getRight();
        }
    } else {
        if ($lcom->getRight() != null) {
            $lcom = $lcom->getRight();
        } else {
            $lcom = $lcom->getLeft();
        }
    }
}

echo($com->getValue() . ", " . $lcom->getValue() . PHP_EOL);
echo("Part 2: " . $com->getValue() * $lcom->getValue() . PHP_EOL);

// 2642904 was too high.
// 2135254