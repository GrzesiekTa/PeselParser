<?php

class PeselParser
{
    /**
     * @var string 
     */
    private $pesel;

    /**
     * @param string $pesel
     */
    public function __construct(string $pesel)
    {
        $this->pesel = $pesel;
    }
    /**
     * get client age from pesel
     *
     * @return integer|null
     */
    public function getAge(): ?int
    {
        if ($this->extractDate() != null) {
            $today = new \DateTime;
            $age = $today->diff($this->extractDate())->y;
            return $age;
        } else {
            return null;
        }
    }

    /**
     *  get date from pesel
     *
     * @return \DateTime|null
     */
    public function extractDate(): ?\DateTime
    {
        try {
            list($year, $month, $day) = sscanf($this->pesel, '%02s%02s%02s');

            switch (substr($month, 0, 1)) {
                case 2:
                case 3:
                    $month -= 20;
                    $year += 2000;
                    break;
                case 4:
                case 5:
                    $month -= 40;
                    $year += 2100;
                case 6:
                case 7:
                    $month -= 60;
                    $year += 2200;
                    break;
                case 8:
                case 9:
                    $month -= 80;
                    $year += 1800;
                    break;
                default:
                    $year += 1900;
                    break;
            }

            return checkdate($month, $day, $year) ? new \DateTime($year . "-" . $month) : null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * check valid pesel 
     * 
     * return bool
     */
    public function checkValid(): bool
    {
        //if is too short or too long
        if (mb_strlen($this->pesel, 'UTF-8') <= 10) {
            return false;
        }
        if (mb_strlen($this->pesel, 'UTF-8') > 11) {
            return false;
        }

        //check that string is only numbers
        if (!ctype_digit($this->pesel)) {
            return false;
        }

        try {
            $w = [1, 3, 7, 9];
            $wk = 0;
            for ($i = 0; $i <= 9; $i++) {
                $wk = ($wk + $this->pesel[$i] * $w[$i % 4]) % 10;
            }
            $k = (10 - $wk) % 10;
            if (!($this->pesel[10] == $k)) {
                return false;
            }

            //if date from pesel is empty pesel is incorrect
            if ($this->extractDate() === null) {
                return false;
            }
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }
}
