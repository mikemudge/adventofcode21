<?php

class AluValue {
    private $value;

    public function __construct(int $value) {
        $this->value = $value;
    }

    public function isValue($val) {
        return $this->value === $val;
    }

    public function reduce() {
        return $this;
    }

    public function __toString() {
        return "$this->value";
    }

    public function getValue() {
        return $this->value;
    }

    public function valuePossiblyBetween(int $low, int $high): bool {
        return $this->value >= $low && $this->value <= $high;
    }
}

class AluInput {

    public function isValue($val) {
        return false;
    }

    public function getValue($regs, $input): int {
        return $input;
    }

    public function reduce() {
        return $this;
    }

    public function __toString() {
        return "input()";
    }
}

class AluVar {
    private $var;

    public function __construct(string $var) {
        $this->var = $var;
    }

    public function isValue($val) {
        // As a variable we don't know what this will be
        return false;
    }

    public function valuePossiblyBetween(int $low, int $high): bool {
        // The value could be anything, so it could be between these.
        return true;
    }

    public function getValue($regs, $input): int {
        return $regs[$this->var];
    }

    public function reduce() {
        return $this;
    }

    public function __toString() {
        return "$this->var";
    }
}

class AluOperator {
    private $lhs;
    private $rhs;
    // Supports add, mul, div and mod as +, *, / and %
    private string $opVisual;

    public function __construct(string $op, $lhs, $rhs) {
        $this->opVisual = [
            'mul' => '*',
            'add' => '+',
            'div' => '/',
            'mod' => '%'
        ][$op];
        $this->lhs = $lhs;
        $this->rhs = $rhs;
    }

    public function isValue($val) {
        return false;
    }

    public function valuePossiblyBetween(int $low, int $high): bool {
        // TODO check value
        if ($this->opVisual == '+') {
            if ($this->rhs->getValue() > $high) {
                // can we prove lhs will be > 0?
                return false;
            }
        }
        return true;
    }

    public function getValue($regs, $input): int {
        $l = $this->lhs->getValue($regs, $input);
        $r = $this->rhs->getValue($regs, $input);
        if ($this->opVisual == '*') {
            return $l * $r;
        } elseif ($this->opVisual == '/') {
            $res = $l / $r;
            if ($res < 0) {
                // Always move to 0.
                return ceil($res);
            }
            return floor($res);
        } elseif ($this->opVisual == '%') {
            return $l % $r;
        } elseif ($this->opVisual == '+') {
            return $l + $r;
        }
        throw new RuntimeException("Invalid opVisual");
    }

    public function __toString() {
        return "($this->lhs $this->opVisual $this->rhs)";
    }

    public function reduce() {
        if ($this->opVisual == '*') {
            if ($this->lhs->isValue(0) || $this->rhs->isValue(0)) {
                return new AluValue(0);
            }
            if ($this->lhs->isValue(1)) {
                return $this->rhs->reduce();
            }
            if ($this->rhs->isValue(1)) {
                return $this->lhs->reduce();
            }
        } elseif ($this->opVisual == '/') {
            // 0 / anything is 0
            if ($this->lhs->isValue(0)) {
                return new AluValue(0);
            }
            if ($this->rhs->isValue(1)) {
                // X / 1 is just X
                return $this->lhs->reduce();
            }
        } elseif ($this->opVisual == '%') {
            // 0 % anything is 0
            if ($this->lhs->isValue(0)) {
                return new AluValue(0);
            }
        } elseif ($this->opVisual == '+') {
            if ($this->lhs->isValue(0)) {
                return $this->rhs->reduce();
            } elseif ($this->rhs->isValue(0)) {
                return $this->lhs->reduce();
            }
        }
        // If this can't be reduced then just return itself.
        return $this;
    }
}

class AluCondition {
    private $lhs;
    private $rhs;

    public function __construct($lhs, $rhs) {
        $this->lhs = $lhs;
        $this->rhs = $rhs;
    }

    public function isValue($val) {
        return false;
    }

    public function getValue($regs, $input): int {
        $l = $this->lhs->getValue($regs, $input);
        $r = $this->rhs->getValue($regs, $input);
        return $l === $r ? 1 : 0;
    }

    public function reduce() {
        // TODO might be able to tell if left == right or left != right
        if ($this->lhs instanceof AluValue) {
            $v = $this->lhs->getValue();
            if ($this->rhs->isValue($v)) {
                // This will always be true
                return new AluValue(1);
            }
        }
        if ($this->rhs instanceof AluValue) {
            $v = $this->rhs->getValue();
            if ($this->lhs->isValue($v)) {
                // This will always be true
                return new AluValue(1);
            }
        }
        // If right is input() and left is <1 or >9 then this knows that the result will be 0
        if ($this->rhs instanceof AluInput) {
            // Is it possible for lhs to be 1-9?
            if (!$this->lhs->valuePossiblyBetween(1, 9)) {
                return new AluValue(0);
            }
        }
        return $this;
    }

    public function __toString() {
        return "($this->lhs == $this->rhs)";
    }
}

function process_instructions(array $instructions, array $regs, int $input) {
    foreach($instructions as $instruction) {
        $var = $instruction['var'];
        $val = $instruction['val'];
        $res = $val->getValue($regs, $input);
        $regs[$var] = $res;
        echo ("$var = $val ($res)\n");
    }
    return $regs;
}

$part1 = 0;
$part2 = 0;

$cwd = dirname(__FILE__);
$input = file_get_contents("$cwd/input");

$lines = explode("\n", $input);

$stacks = [];
$linesRepresented = [];
foreach ($lines as $lineIdx => $line) {
    $parts = explode(" ", $line);
    $ins = array_shift($parts);
    $var = $parts[0];
    if ($ins == 'inp') {
        if ($lineIdx > 0) {
            // Don't save on the first set as there is nothing useful there.
            $instructions[] = [
                'lines' => $linesRepresented,
                'var' => $lastvar,
                'val' => $lastval
            ];
            $stacks[] = $instructions;
        }
        $instructions = [];
        $lastvar = $var;
        $lastval = new AluInput();
        continue;
    }
    $val = $parts[1];
    if (in_array($val, ['w','x','y','z'])) {
        $rhs = new AluVar($val);
    } else {
        // Should be an int
        $rhs = new AluValue(intval($val));
    }
    // var = var op val
    if ($var == $lastvar) {
        // We can reduce this because we are overriding the var immediately.
        $lhs = $lastval;
    } else {
        // Save the previous instruction
        $instructions[] = [
            'lines' => $linesRepresented,
            'var' => $lastvar,
            'val' => $lastval
        ];
        $linesRepresented = [];
        $lhs = new AluVar($var);
    }
    if ($ins == 'eql') {
        $result = new AluCondition($lhs, $rhs);
    } else {
        $result = new AluOperator($ins, $lhs, $rhs);
    }
    $result = $result->reduce();

    $linesRepresented[] = $line;
    $lastvar = $var;
    $lastval = $result;

    if (count($stacks) == 0) {
        echo($line . PHP_EOL);
        echo("$var = $val\n");
    }
}
// Save the last set.
$instructions[] = [
    'lines' => $linesRepresented,
    'var' => $lastvar,
    'val' => $lastval
];
$stacks[] = $instructions;

function printInstructions(mixed $instructions) {
    foreach($instructions as $instruction) {
        $var = $instruction['var'];
        $val = $instruction['val'];
//        foreach($instruction['lines'] as $line) {
//            echo("$line\n");
//        }
        echo("$var = $val\n");
    }
}

foreach ($stacks as $i => $instructions) {
    echo("Input set $i\n");
    printInstructions($instructions);
}

$regs = [
    'z' => 0
];


// 0 adds d0 + 14
// 1 adds d1 + 8
// 2 adds d2 + 5
// 3 adds d3 + 4 or (d2 + 5 == d3 causes a /26 to happen to z)
// 4 adds d4 + 10
// 5 adds d5 + 13 or (d4 - 3 == d5 causes a /26)
// 6 adds d6 + 16
// 7 adds d7 + 5 or (d6 + 7 == d7 causes a /26)
// 8 adds d8 + 6
// 9 adds d9 + 13
// 10 adds d10 + 6 or (d9 - 1 == d10 causes a /26)
// 11 adds d11 + 7 or (d8 + 3 == d11 causes a /26)
// 12 adds d12 + 13 or (d1 + 6 == d12 causes a /26)
// 13 adds d13 + 3 or (d0 == d13 causes a /26)
echo("Processing sample model number\n");
$inputs = [
    9, 3, // d0, d1
    4, 9, // d2, d3
    9, 6, // d4, d5
    2, 9, // d6, d7
    6,    // d8
    9, 8, // d9, d10
    9,    // d11
    9, 9];
// 93499629698999 is the largest model number possible
$inputs = [
    1, 1, // d0, d1
    1, 6, // d2, d3
    4, 1, // d4, d5
    1, 8, // d6, d7
    1,    // d8
    2, 1, // d9, d10
    4,    // d11
    7, 1]; // d12, d13

foreach ($inputs as $i => $input) {
    $result = process_instructions($stacks[$i], $regs, $input);
    echo("$i => " . json_encode($result) . PHP_EOL);
    $regs = $result;
}

echo("Part 1: " . $part1 . PHP_EOL);

echo("Part 2: " . $part2 . PHP_EOL);
// 47768 is too high
