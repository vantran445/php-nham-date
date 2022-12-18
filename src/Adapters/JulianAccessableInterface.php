<?php namespace Vantran\PhpNhamDate\Adapters;

interface JulianAccessableInterface
{
    /**
     * Trả về số ngày Julian
     *
     * @param boolean $withDecimal có bao gồm phần thập phân (xác định giờ, phút giây) hay không, mặc định có.
     * 
     * @return float
     */
    public function getJdn(bool $withDecimal = true): float;

    /**
     * Trả về phần thập phân của ngày Julian
     *
     * @return float
     */
    public function getJdnDecimal(): float;
}