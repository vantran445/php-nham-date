<?php namespace Vantran\PhpNhamDate\Adapters\SunLongitude;

use Vantran\PhpNhamDate\Adapters\Factories\JulianAdapter;

class DateTimePrimitiveToSunLongitude extends BaseSunlongitudeAdapter
{
    public function __construct(
        protected int $offset,
        protected int $Y,
        protected int $m,
        protected int $d,
        protected int $H = 0,
        protected int $i = 0,
        protected int $s = 0
    ) {
        $jdn = JulianAdapter::make(
            $this->Y, 
            $this->m, 
            $this->d,
            $this->H,
            $this->i,
            $this->s
        )->getJdn();
        
        parent::__construct($jdn, $this->offset);
    }
}