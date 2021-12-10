<?php

class Grid {
    private array $data;
    private int $height;
    private int $width;

    public function __construct($width, $height) {
        $this->width = $width;
        $this->height = $height;
        $this->data = [];
        for ($y = 0; $y < $this->height; $y++) {
            $this->data[] = array_fill(0, $width, 0);
        }
    }

    public function getPos($x, $y) {
        if ($x < 0 || $x >= $this->width) {
            error_log("OOB x $x");
            return null;
        }
        if ($y < 0 || $y >= $this->height) {
            error_log("OOB y $y");
            return null;
        }
        return $this->data[$y][$x];
    }

    public function setPos($x, $y, $value) {
        if ($x < 0 || $x >= $this->width) {
            error_log("OOB set x $x");
            return null;
        }
        if ($y < 0 || $y >= $this->height) {
            error_log("OOB set y $y");
            return null;
        }
        $this->data[$y][$x] = $value;
    }

    public function __toString(): string {
        $result = "";
        foreach ($this->data as $y => $row) {
            foreach ($row as $x => $v) {
                $result .= $v;
            }
            $result .= "\n";
        }
        return $result;
    }
}