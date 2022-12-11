<?php namespace Vantran\PhpNhamDate\Adapters\Julian;

use DateTime;
use DateTimeInterface;

class DateTimeToJulian extends DateTimePrimitiveToJulian
{
    public function __construct(?DateTimeInterface $datetime = null)
    {
        if (!$datetime) {
            $datetime = new DateTime('now');
        }

        parent::__construct(
            $datetime->format('Y'),
            $datetime->format('m'),
            $datetime->format('d'),
            $datetime->format('H'),
            $datetime->format('i'),
            $datetime->format('s')
        );
    }


}