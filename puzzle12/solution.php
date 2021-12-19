<?php

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$numbers = explode(",", $lines[0]);
$part1 = 0;
$part2 = 0;

// Sample
//$lines = [
//    'start-A',
//    'start-b',
//    'A-c',
//    'A-b',
//    'b-d',
//    'A-end',
//    'b-end',
//];

include_once ("classes/Graph.php");

$graph = new Graph();

foreach($lines as $y => $line) {
    [$n1, $n2] = explode("-", $line);
    $node1 = $graph->ensureNode($n1);
    $node2 = $graph->ensureNode($n2);
    $node1->addEdgeToNode($node2);
    $node2->addEdgeToNode($node1);
}

$graph->sort();

function recurse(GraphNode $node, array $visited, array $path) {
    $sum = 0;
    $v = $node->getValue();
    $path[] = $v;
    if ($v == "end") {
        echo(implode(",", $path) . PHP_EOL);
        return 1;
    }
    if (!isset($visited[$v])) {
        $visited[$v] = 0;
    }
    $limit = $visited['limit'];
    if ($v == "start") {
        if ($visited[$v] > 0) {
            // Can't visit start again.
            return 0;
        }
    } else {
        if (strtolower($v) == $v) {
            // This is a small node.
            if ($visited[$v] >= $limit) {
                // limited visits to small nodes.
                return 0;
            }
            if ($visited[$v] > 0) {
                // If we are visiting this node for a second time, reduce limit to 1.
                $visited['limit'] = 1;
            }
        }
    }
    $visited[$v]++;
    foreach ($node->getChildren() as $c) {
        $sum += recurse($c, $visited, $path);
    }
    // After recursing reset path and visited set.
    array_pop($path);
    $visited['limit'] = $limit;
    $visited[$v]--;
    return $sum;
}
// Now we have a graph we need to count paths.
$start = $graph->ensureNode("start");

$visited = ['limit' => 1];
$path = [];
$paths = recurse($start, $visited, $path);
$part1 = $paths;

$visited = ['limit' => 2];
$path = [];
$paths = recurse($start, $visited, $path);
$part2 = $paths;

echo("Part 1: " . $part1 . PHP_EOL);
echo("Part 2: " . $part2 . PHP_EOL);
