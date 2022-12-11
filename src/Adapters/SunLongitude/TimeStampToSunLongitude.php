<?php namespace Vantran\PhpNhamDate\Adapters\SunLongitude;

use Vantran\PhpNhamDate\Adapters\Factories\JulianAdapter;

class TimeStampToSunLongitude extends BaseSunlongitudeAdapter
{
    public function __construct(
        protected float $timestamp,
        protected int $offset
    ) {
        $jdn = JulianAdapter::make($timestamp, $offset)->getJdn();
        parent::__construct($jdn, $offset);
    }
}