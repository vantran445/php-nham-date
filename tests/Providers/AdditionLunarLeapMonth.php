<?php namespace Vantran\PhpNhamDate\Tests\Providers;

/**
 * Dữ liệu tháng nhuận âm lịch 
 */
trait AdditionLunarLeapMonth
{
     /**
     * Dữ liệu một số tháng nhuận trong những năm gần đây
     *
     * @return array
     */
    public function additionLeapMonthProvider(): array
    {
        /**
         * Cấu trúc mảng theo index:
         * - 0: số của năm (dương lịch & âm lịch)
         * - 1: số (vị trí) của tháng nhuận âm lịch
         */
        return [
            [2025, 6],
            [2023, 2],
            [2020, 4],
            [2017, 6],
            [2014, 9],
            [2012, 4],
            [2009, 5],
            [2006, 7],
            [2004, 2],
            [2001, 4],
            [1998, 5],
            [1995, 8],
            [1993, 3],
            [1990, 5],
            [1987, 7],
            [1985, 2],
            [1982, 4],
        ];
    }
}