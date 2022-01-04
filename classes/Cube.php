<?php

class Cube {
    /**
     * @var array[]
     */
    private array $dimensions;
    private mixed $data;
    private array $subcubes;
    /**
     * @var int
     */
    private int $responsibility;

    public function __construct(array $dimensions, mixed $data, $id) {
        $this->dimensions = $dimensions;
        $this->id = $id;
        $this->data = $data;
        $this->subcubes = [];
        // Add the entire volume as this cubes responsibility
        $this->responsibility = $this->getVolume();
    }

    public function getDimension(int $d) {
        return $this->dimensions[$d];
    }

    public function getData(): mixed {
        return $this->data;
    }

    public function intersection(Cube $c): ?Cube {
        $dims = [];
        for ($d=0;$d < 3;$d++) {
            [$min1, $max1] = $this->getDimension($d);
            [$min2, $max2] = $c->getDimension($d);
            if ($max2 < $min1 || $max1 < $min2) {
                // no overlap
                return null;
            }
            $dims[] = [max($min1, $min2), min($max1, $max2)];
        }
        return new Cube($dims, null, "sc-" . $this->id);
    }

    public function addSubCube(Cube $c) {
        if ($this->responsibility == 0) {
            // I already have no responsibility so I have nothing to delegate.
            return;
        }
//        echo("Remove $c from $this\n");
        foreach ($this->subcubes as $c2) {
            // Check overlapping subcubes.
            $intersect = $c2->intersection($c);
            if ($intersect) {
                // $c is taking over responsibility for this intersection.
                // Remove it from $c2;
                $c2->addSubCube($intersect);
            }
        }
        $this->subcubes[] = $c;

        // before change.
        $x = $this->responsibility;
        // My responsibility is my volume subtract what my sub cubes are responsible for.
        $this->responsibility = $this->getVolume();
        foreach ($this->subcubes as $c2) {
            $this->responsibility -= $c2->getResponsibility();
        }
//        echo("$this lost responsibility $x -> $this->responsibility\n");
    }

    public function __toString() {
        $dd = [];
        foreach ($this->dimensions as $d) {
            $dd[] = $d[0] . ".." . $d[1];
        }
        return "Cube $this->id " . ($this->data ? "on " : "off ") . join(", ", $dd) . "($this->responsibility)";
    }

    public function getVolume() {
        $v = 1;
        foreach ($this->dimensions as $d) {
            $v *= (1 + $d[1] - $d[0]);
        }
        return $v;
    }

    public function getResponsibility() {
        return $this->responsibility;
    }
}