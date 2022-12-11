<?php namespace Vantran\PhpNhamDate\Adapters\SunLongitude;

use DateTimeInterface;
use Vantran\PhpNhamDate\Adapters\Factories\JulianAdapter;

class DateTimeToSunLongitude extends BaseSunlongitudeAdapter
{
    public function __construct(?DateTimeInterface $datetime) {
        $jdAdapter = JulianAdapter::make($datetime);
        $offset = $datetime->getOffset();

        parent::__construct($jdAdapter->getJdn(), $offset);
    }
}