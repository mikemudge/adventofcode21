<?php

class Graph {
    private array $nodes = [];

    public function ensureNode($value) {
        if (!isset($this->nodes[$value])) {
            $this->nodes[$value] = new GraphNode($value);
        }
        return $this->nodes[$value];
    }

    public function sort() {
        ksort($this->nodes);
        foreach ($this->nodes as $n) {
            $n->sort();
        }
    }
}

class GraphNode {
    private array $children;
    private mixed $value;

    public function __construct($value) {
        $this->value = $value;
        $this->children = [];
    }

    public function addEdgeToNode(GraphNode $node) {
        $this->children[$node->getValue()] = $node;
    }

    public function sort() {
        ksort($this->children);
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function getValue() {
        return $this->value;
    }
}
