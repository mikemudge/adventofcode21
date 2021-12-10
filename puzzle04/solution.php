<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$numbers = explode(",", $lines[0]);

include_once ("classes/BingoBoard.php");

$boards = [];
for ($i = 2; $i < count($lines); $i+=6) {
    $board = new BingoBoard();
    $boards[] = $board;
    for ($ii = 0; $ii < 5; $ii++) {
        $board->addLine(preg_split("/\W+/", $lines[$i + $ii]));
    }
}
echo (count($boards) . " boards in the game" . PHP_EOL);

$winner = null;
foreach ($numbers as $num) {
    echo($boards[0]);
    echo("Number: $num" . PHP_EOL);
    foreach ($boards as $b) {
        if (!$b->stillPlaying()) {
            continue;
        }
        /** @var $b BingoBoard */
        $b->checkOffNumber($num);
    }
    foreach ($boards as $k => $b) {
        /** @var $b BingoBoard */
        if ($b->stillPlaying() && $b->checkWin()) {
            if ($winner == null) {
                echo("Assigning winner\n");
                $winnernum = $num;
                echo($winner);
                $winner = $b;
            }
            $lastnum = $num;
            $lastwinner = $b;
            $b->finish();
        }
    }
    if (empty($boards)) {
        break;
    }
}
echo ($winner);

$sum = $winner->getUncheckedSum();
echo($sum . PHP_EOL);

echo("Part 1: " . $sum * intval($winnernum) . PHP_EOL);
// 4394 is too low

echo ($lastwinner);
$sum = $lastwinner->getUncheckedSum();
echo($sum . PHP_EOL);

echo("Part 2: " . $sum * intval($lastnum) . PHP_EOL);

//echo($com->getValue() . ", " . $lcom->getValue() . PHP_EOL);
//echo("Part 2: " . $com->getValue() * $lcom->getValue() . PHP_EOL);
