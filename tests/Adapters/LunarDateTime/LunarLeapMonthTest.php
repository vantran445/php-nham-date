<?php namespace Vantran\PhpNhamDate\Tests\Adapters\LunarDateTime;

use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\LunarDateTimeAdapter;

class LunarLeapMonthTest extends TestCase
{
    /**
     * Phần bù múi giờ GMT+7 so với UTC
     *
     * @var integer
     */
    protected $offset = 25200;

    public function additionLeapMonthsProvider(): array
    {
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

    /**
     * Kiểm tra tính toán tháng nhuận âm lịch
     *
     * @covers LunarDateTimeAdapter
     * @dataProvider additionLeapMonthsProvider
     * @param integer $year
     * @param integer $leapMonth
     * @return void
     */
    public function testLeapMonths(int $year, int $leapMonth)
    {
        $lunar = new LunarDateTimeAdapter($year, 5, 3);
        $lm = $lunar->getLeapMonth();

        $this->assertEquals($leapMonth, $lm);
        
        //$this->assertEquals(true, true);
    }
}