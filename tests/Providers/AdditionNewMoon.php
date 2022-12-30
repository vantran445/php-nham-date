<?php namespace Vantran\PhpNhamDate\Tests\Providers;

/**
 * Dữ liệu điểm sóc (ngày 01 hàng tháng âm lịch)
 */
trait AdditionNewMoon
{
    /**
     * Điểm Sóc của 13 tháng trong năm 2020 tại Việt Nam (GMT+7)
     *
     * @return array
     */
    public function additionNewMoon2020Provider(): array
    {
        return [
            ['solar' => '2020-01-25', 'lunar_month' => '1'],
            ['solar' => '2020-02-23', 'lunar_month' => '2'],
            ['solar' => '2020-03-24', 'lunar_month' => '3'],
            ['solar' => '2020-04-23', 'lunar_month' => '4'],
            ['solar' => '2020-05-23', 'lunar_month' => '4+'],
            ['solar' => '2020-06-21', 'lunar_month' => '5'],
            ['solar' => '2020-07-21', 'lunar_month' => '6'],
            ['solar' => '2020-08-19', 'lunar_month' => '7'],
            ['solar' => '2020-09-17', 'lunar_month' => '8'],
            ['solar' => '2020-10-17', 'lunar_month' => '9'],
            ['solar' => '2020-11-15', 'lunar_month' => '10'],
            ['solar' => '2020-12-14', 'lunar_month' => '11'],
            ['solar' => '2021-01-13', 'lunar_month' => '12'],
        ];
    }
}