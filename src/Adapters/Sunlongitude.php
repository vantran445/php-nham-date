<?php

namespace Vantran\PhpNhamDate\Adapters;

use DateTime;
use DateTimeInterface;
use DateTimeZone;

class Sunlongitude
{
    protected float $sl;

    public function __construct(
        protected float $jdn,
        protected float $timezone = 0.0 // UTC

    ) {
        $this->sl = $this->convert($this->jdn, $this->timezone);
    }

    public function __set($name, $value)
    {
        if ($name === 'sl' || $name === 'jdn') {
            $this->{$name} = floatval($value);
        }
    }

    /**
     * Chuyển đổi số ngày Julian đầu vào thành góc kinh độ mặt trời
     *
     * @return float 0 - 360 độ
     */
    protected function convert($jdn, $timezone = 0): float
    {
        $T = ($jdn - 2451545.5 - $timezone / 24) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
        $dr = M_PI / 180; // degree to radian
        $L = 280.460 + 36000.770 * $T; //  degree
        $G = 357.528 + 35999.050 * $T; //  degree
        $ec = 1.915 * sin($dr *$G) + 0.020 * sin($dr *2*$G);
        $lambda = $L + $ec ;// true longitude, degree
        
        return $L =  $lambda - 360 * (floor($lambda / (360))); // Normalize to (0, 360)
    }

    public static function createFromDates(
        int $Y, 
        int $m, 
        int $d, 
        int $H = 0, 
        int $i = 0, 
        int $s = 0, 
        float $timezone = 0
    ) {
        $jdn = gregoriantojd($m, $d, $Y) + ($H + $i / 60 + $s / 3600) / 24;
        return new Sunlongitude($jdn, $timezone);
    }

    public static function createFromDate(DateTimeInterface $date): Sunlongitude
    {
        return Sunlongitude::createFromDates(
            $date->format('Y'),
            $date->format('m'),
            $date->format('d'),
            $date->format('H'),
            $date->format('i'),
            $date->format('s'),
            $date->getOffset() / 3600
        );
    }

    protected function matchJdBegin(&$jdn, &$sl, $timezone, $withHour = false, $withMinutes = false)
    {
        $breakPoint = $sl < 15
            ? 0
            : floor($sl) - $sl % 15;

        $match = function(&$jdn, &$sl, $jdMatchUnit, $breakPoint, $timezone) {
            while (true) {
                $nextJd = $jdn - $jdMatchUnit;
                $nextSl = $this->convert($nextJd, $timezone);

                if (
                    ($breakPoint === 0 && $nextSl > 359)|| 
                    ($breakPoint > 0 && $nextSl < $breakPoint)
                ) {
                    break;
                }

                $sl = $nextSl;
                $jdn = $nextJd;
            }
        };

        $match($jdn, $sl, 1, $breakPoint, $timezone);

        if ($withHour) {
            $match($jdn, $sl, 0.041666666666667, $breakPoint, $timezone);

            if ($withMinutes) {
                $match($jdn, $sl, 0.016666666666667, $breakPoint, $timezone);
            }
        }
    }

    public function getStartingPoint($withHour = false, $withMinutes = false)
    {
        $jdn = $this->jdn;
        $sl = $this->sl;

        $this->matchJdBegin($jdn, $sl, $this->timezone, $withHour, $withMinutes);
        $ins = clone($this);
        $ins->jdn = $jdn;
        $ins->sl = $sl;

        return $ins;
    }

    // public function getNext(int $point = 1): Sunlongitude 
    // {
    //     $breakPoint = floor($this->jdn - $this->jdn % 15) + ($point * 15 % 360);
    //     $jdn = $point * 15 + $this->jdn;
    //     $sl = $this->convert($jdn, $this->timezone);
    // }

    // public function getPrev(int $point = 1): Sunlongitude 
    // {

    // }

    public function getDegree($withMinutes = true): float
    {
        return $withMinutes? $this->sl : floor($this->sl);
    }
    
    public function getMinutes(): int
    {
        return $this->getDegree() - $this->getDegree(false);
    }

    public function toTimestamp(): int
    {
        return ($this->jdn - 2440587.5 - 0.5 - $this->timezone / 24) * 86400;
    }

    public function toDate(): DateTime
    {
        $sign = $this->timezone < 0? '-' : '+';
        $timezone = $sign . $this->timezone;
        $date = new DateTime('', new DateTimeZone($timezone));

        return $date->setTimestamp($this->toTimestamp());
    }

    public function getJdn(): float
    {
        return $this->jdn;
    }
}