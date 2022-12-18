<?php namespace Vantran\PhpNhamDate\Adapters;

interface TimestampAccessableInterface
{
    /**
     * Trả về tem thời gian Unix
     *
     * @return integer|float
     */
    public function getTimestamp(): int|float;
}