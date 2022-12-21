<?php namespace Vantran\PhpNhamDate\Adapters\Interfaces;

use stdClass;

interface JulianAccessable
{
    /**
     * Xác định chuyển ngày tháng đầu ra ở dạng mảng
     */
    const ARRAY_DATE_TIME = 'ARRAY';

    /**
     * Xác định chuyển đổi ngày tháng đầu ra ở dạng đối tượng (stdClass)
     */
    CONST OBJECT_DATE_TIME = 'OBJECT';

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

    /**
     * Trả về dữ liệu ngày tháng ở dạng mảng hoặc stdClass theo thiết lập giờ địa phương (chuyển đổi ngược ngày Julius 
     * về ngày tháng năm (dương lịch))
     *
     * @param string $output
     * @return array|stdClass
     */
    public function getBaseLocalDateTime(string $output = self::OBJECT_DATE_TIME): array|stdClass;
}