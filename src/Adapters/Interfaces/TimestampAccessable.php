<?php namespace Vantran\PhpNhamDate\Adapters\Interfaces;

interface TimestampAccessable
{
    /**
     * Trả về tem thời gian Unix
     *
     * @return integer|float
     */
    public function getTimestamp(): int|float;
}