<?php

class SnailNum {
    public ?int $value;
    public ?SnailNum $left;
    public ?SnailNum $right;

    /**
     * @param mixed $left
     * @param mixed $right
     */
    public function __construct(?int $val, ?SnailNum $left=null, ?SnailNum $right=null) {
        $this->value = $val;
        $this->left = $left;
        $this->right = $right;
    }

    public function split() {
        $a = floor($this->value / 2);
        $b = $this->value - $a;
        $this->value = null;
        $this->left = new SnailNum($a);
        $this->right = new SnailNum($b);
    }

    // Part of reduce is to set the node to 0 and a leaf.
    public function zero() {
        $this->value = 0;
        $this->left = null;
        $this->right = null;
    }

    public function isLeaf() {
        // Nodes with a value are leaf nodes.
        return $this->value !== null;
    }

    public function getMagnitude() {
        if ($this->isLeaf()) {
            return $this->value;
        }
        return 3 * $this->left->getMagnitude() + 2 * $this->right->getMagnitude();
    }

    public function __toString() {
        if ($this->isLeaf()) {
            return "$this->value";
        }
        return "[$this->left, $this->right]";
    }
}