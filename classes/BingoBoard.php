<?php

class BingoBoard {
    private array $data;
    private array $checked;
    private bool $playing;

    public function addLine(array $row) {
        $this->playing = true;
        $this->data[] = $row;
        $this->checked[] = array_fill(0, 5, false);
    }

    public function checkOffNumber(string $value) {
        foreach ($this->data as $y => $row) {
            foreach($row as $x => $v) {
                if ($v == $value) {
                    // This is checked off.
                    $this->checked[$y][$x] = true;
                }
            }
        }
    }

    public function checkWin(): bool {
        // check columns.
        for ($x = 0; $x < 5; $x++) {
            $isWin = true;
            for ($y = 0; $y < 5; $y++) {
                if (!$this->checked[$y][$x]) {
                    $isWin = false;
                }
            }
            if ($isWin) {
                return true;
            }
        }
        // check rows.
        for ($y = 0; $y < 5; $y++) {
            $isWin = true;
            for ($x = 0; $x < 5; $x++) {
                if (!$this->checked[$y][$x]) {
                    $isWin = false;
                }
            }
            if ($isWin) {
                return true;
            }
        }
        return false;
    }

    public function __toString(): string {
        $result = "";
        foreach ($this->data as $y => $row) {
            foreach ($row as $x => $v) {
                if ($this->checked[$y][$x]) {
                    $result .= "** ";
                } else {
                    $result .= sprintf("%1$02d ", $v);
                }
            }
            $result .= "\n";
        }
        return $result;
    }

    public function getUncheckedSum() {
        $sum = 0;
        foreach ($this->data as $y => $row) {
            foreach ($row as $x => $v) {
                if (!$this->checked[$y][$x]) {
                    $sum += intval($v);
                }
            }
        }
        return $sum;
    }

    public function finish() {
        $this->playing = false;
    }

    public function stillPlaying(): bool {
        return $this->playing;
    }
}