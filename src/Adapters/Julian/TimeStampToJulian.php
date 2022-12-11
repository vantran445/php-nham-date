<?php namespace Vantran\PhpNhamDate\Adapters\Julian;

class TimeStampToJulian extends BaseJulianAdapter
{
    public function __construct(
        protected float|int $timestamp,
        protected int $offset = 0
    ) {
        $this->jdn = ($this->timestamp + $offset)  / 86400 + self::JDN_EPOCH_TIME;
    }
}