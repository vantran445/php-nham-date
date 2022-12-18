<?php namespace Vantran\PhpNhamDate\Adapters;

interface JulianAccessableInterface
{
    /**
     * Trả về số ngày Julian theo giờ địa phương
     *
     * @param boolean $withDecimal có bao gồm phần thập phân (xác định giờ, phút giây) hay không, mặc định có.
     * 
     * @return float
     */
    public function getJdn(bool $withDecimal = true): float;

    /**
     * Trả về số ngày Julian theo giờ UTC
     *
     * @param boolean $withDecimal
     * @return float
     */
    public function getLocalJdn(bool $withDecimal = true): float;
}