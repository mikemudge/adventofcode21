<?php

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

class Packet {
    private int $version;
    private int $type;
    private array $children;

    function __construct($v, $t, $val) {
        $this->version = $v;
        $this->type = $t;
        $this->value = $val;
        $this->children = [];
    }

    public function addChild($packet) {
        $this->children[] = $packet;
    }

    public function __toString() {
        if (empty($this->children)) {
            return "$this->version:$this->type:$this->value";
        }
        return "$this->version:$this->type:[" . join(",", $this->children) . "]";
    }

    public function getSumVersion(): int {
        $sum = $this->version;
        foreach ($this->children as $c) {
            $sum += $c->getSumVersion();
        }
        return $sum;
    }

    public function eval() {
        switch ($this->type) {
            case 0:
                $result = 0;
                foreach ($this->children as $p) {
                    $result += $p->eval();
                }
                return $result;
            case 1:
                $result = 1;
                foreach ($this->children as $p) {
                    $result *= $p->eval();
                }
                return $result;
            case 2:
                $result = PHP_INT_MAX;
                foreach ($this->children as $p) {
                    $result = min($result, $p->eval());
                }
                return $result;
            case 3:
                $result = PHP_INT_MIN;
                foreach ($this->children as $p) {
                    $result = max($result, $p->eval());
                }
                return $result;
            case 4:
                return $this->value;
            case 5:
                return $this->children[0]->eval() > $this->children[1]->eval() ? 1 : 0;
            case 6:
                return $this->children[0]->eval() < $this->children[1]->eval() ? 1 : 0;
            case 7:
                return $this->children[0]->eval() == $this->children[1]->eval() ? 1 : 0;
        }
        throw new RuntimeException("$this->type not in range 0-7");
    }
}

function parse_packet(string $bin, int $idx): array {
    // Now get the pieces
    $v = base_convert(substr($bin, $idx, 3), 2, 10);
    $t = base_convert(substr($bin, $idx + 3,3),  2, 10);

    echo("Parsing packet with type $t, version $v at $idx\n");
    $idx += 6;

    if ($t == 4) {
        // Get the value.
        $bits = "";
        while($bin[$idx] == "1") {
            $bits .= substr($bin, $idx + 1, 4);
            $idx += 5;
        }
        // Consume the next 4 bits.
        $bits .= substr($bin, $idx + 1, 4);
        $idx += 5;
        $packet = new Packet($v, $t, base_convert($bits, 2, 10));
    } else {
        // Check the first bit for the type.
        if ($bin[$idx] == "1") {
            $val = substr($bin, $idx + 1, 11);
            $idx += 12;
            $num_packets = base_convert($val, 2, 10);
            echo("number of packets = $val = $num_packets" . PHP_EOL);
            $packet = new Packet($v, $t, null);
            for ($p = 0; $p < $num_packets; $p++) {
                [$p2, $idx] = parse_packet($bin, $idx);
                $packet->addChild($p2);
            }
        } else {
            $val = substr($bin, $idx + 1, 15);
            $idx += 16;
            $sub_packet_length = base_convert($val, 2, 10);
            echo("bits of packets = $val = $sub_packet_length" . PHP_EOL);
            $sub_packet_length += $idx;
            $packet = new Packet($v, $t, null);
            while ($idx < $sub_packet_length) {
                [$p2, $idx] = parse_packet($bin, $idx);
                $packet->addChild($p2);
            }
        }
    }
    return [$packet, $idx];
}

$input = $lines[0];

// Sample.
//$input = "9C0141080250320F1802104A08";

echo("" . $input . PHP_EOL);

$bins = [];
foreach(str_split($input) as $c) {
    $bins[] = str_pad(base_convert($c, 16, 2), 4, "0", STR_PAD_LEFT);
}
$bin = join("", $bins);
$expected_length = 4 * strlen($input);
echo("Expected $expected_length got " . strlen($bin) . PHP_EOL);
echo("" . $bin . PHP_EOL);

/** @var $root Packet */
[$root, $idx] = parse_packet($bin, 0);

echo($root . PHP_EOL);

$part1 = $root->getSumVersion();
$part2 = $root->eval();

echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
// 2998 is too high.
