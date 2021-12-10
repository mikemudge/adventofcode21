<?php

class Node {
    private ?Node $left = null;
    private ?Node $right = null;
    private int $leftChildren = 0;
    private int $rightChildren = 0;
    private mixed $value = null;

    public function insertPath(int $i, array $chars) {
        if ($i == count($chars)) {
            $this->value = bindec(join($chars));
            return;
        }
        if ($chars[$i] == "0") {
            $this->leftChildren++;
            $this->ensureLeft()->insertPath($i + 1, $chars);
        } else if ($chars[$i] == "1") {
            $this->rightChildren++;
            $this->ensureRight()->insertPath($i + 1, $chars);
        }
    }

    private function ensureLeft() {
        if ($this->left == null) {
            $this->left = new Node();
        }
        return $this->left;
    }

    private function ensureRight() {
        if ($this->right == null) {
            $this->right = new Node();
        }
        return $this->right;
    }

    public function __toString() {
        return "Left: $this->leftChildren, Right: $this->rightChildren";
    }

    public function getLeftChildren(): int {
        return $this->leftChildren;
    }

    public function getRightChildren(): int {
        return $this->rightChildren;
    }

    public function getLeft(): ?Node {
        return $this->left;
    }

    public function getRight(): ?Node {
        return $this->right;
    }

    public function getValue() {
        return $this->value;
    }
}
