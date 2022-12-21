<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
use DateTimeImmutable;
use Vantran\PhpNhamDate\Adapters\BaseAdapter;
use Vantran\PhpNhamDate\Adapters\LunarDateTimeAdapter;

class LunarDateTimeAdapterTest extends AdapterTestCase
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

    /**
     * Mảng dữ liệu điểm bắt đầu 01 tháng 11 âm lịch của một số năm. Dữ liệu được khớp từ một số phần mềm âm lịch sẵn có 
     * bằng các ngôn ngữ khác PHP. Múi giờ được xác định là Asia/Ho_Chi_Minh
     *
     * @return array
     */
    public function addition11thNewMonData(): array
    {
        $data = [
            '2022-11-24',
            '2021-12-04',
            '2020-12-14',
            '2019-11-26',
            '2018-12-07',
            '2017-12-18',
            '2016-11-29',
            '2015-12-11',
            '2014-12-22', 
            '2013-12-03',
            '2012-12-13'
        ];

        return array_map(function ($date) {
            $date = explode('-', $date);
            return array_map(fn($val) => (int)$val, $date);
        }, $data);
    }

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

    /**
     * Dữ liệu một số thời điểm Âm lịch tương ứng với Dương lịch
     *
     * @return array
     */
    public function additionLunarDateTimeProvider(): array
    {
        /**
         * Cấu trúc theo index như sau:
         * - 0: Chuỗi thời gian dương lịch định dạng Y-m-d (yyyy/mm/dd)
         * - 0: Mảng dữ liệu âm lịch tương ứng với index:
         *      + O: năm âm lịch
         *      + 1: tháng âm lịch
         *      + 2: ngày âm lịch
         *      + 3: tháng đó có nhuận không, không khai báo hoặc false tức không nhuận
         */
        return [
            ['2022-12-20', [2022, 11, 27]],
            ['2023-01-11', [2022, 12, 20]],
            ['2023-02-20', [2023, 2, 1]],
            ['2022-08-26', [2022, 7, 29]],
            ['2022-11-23', [2022, 10, 30]],
            ['2020-05-23', [2020, 4, 1, true]], // Nhuận
            ['2020-05-22', [2020, 4, 30]],      // Không nhuận
            ['2020-06-21', [2020, 5, 1]],
            ['2020-08-04', [2020, 6, 15]],
        ];
    }

    /**
     * Kiểm tra tìm thời điểm trăng mới
     *
     * @covers LunarDateTimeAdater
     * @dataProvider additionNewMoon2020Provider
     * @param string $newMoonDate
     * @return void
     */
    public function testGetNewMoon(string $newMoonDate)
    {
        $expectDateTime = new DateTimeImmutable($newMoonDate, BaseAdapter::getTimeZone());

        $lunar = new LunarDateTimeAdapter(
            $expectDateTime->format('Y'),
            $expectDateTime->format('n'),
            $expectDateTime->format('j'),
        );

        $newMoon = $lunar->getNewMoon();
        $this->assertEquals($expectDateTime->format('Y-m-d'), $newMoon->getDateTime()->format('Y-m-d') );
    }

    /**
     * Kiểm tra tìm điểm Sóc Đông chí - tức ngày 01/11 âm lịch. 
     *
     * @covers LunarDateTimeAdater
     * @dataProvider addition11thNewMonData
     * @param integer $Y
     * @param integer $m
     * @param integer $d
     * @return void
     */
    public function test11thNewMoon(int $Y, int $m, int $d)
    {
        /**
         * Điểm sóc tháng 11 âm lịch tính theo điểm sóc của năm trước, tức điểm sóc đã qua chứ không phải điểm sóc tương
         * ứng với năm dương lịch. Do đó cần tăng số năm lên 1 đơn vị để kiểm tra.
         */
        $lunar = new LunarDateTimeAdapter(($Y + 1), 1, 1);
        $newMoon11th = $lunar->getNewMoon11th();
        $datetime = $newMoon11th->getDateTime();

        $expected = "$Y-$m-$d";

        $this->assertEquals($expected, $datetime->format('Y-n-j'));
    }

    /**
     * Kiểm tra tính tháng nhuận âm lịch
     *
     * @covers LunarDateTimeAdater
     * @dataProvider additionLeapMonthProvider
     * @param integer $Y
     * @param integer $m số (vị trí) tháng nhuận âm lịch
     * @return void
     */
    public function testLeapMonth(int $Y, int $m)
    {
        $lunar = new LunarDateTimeAdapter($Y, 1, 1);
        $leapMonth = $lunar->getLeapMonth();

        $this->assertEquals($m, $leapMonth);
    }

    /**
     * Kiểm tra chuyển đổi dương lịch sang âm lịch
     *
     * @covers LunarDateTimeAdater
     * @dataProvider additionLunarDateTimeProvider
     *
     * @param string $date
     * @param array $lunar
     * @return void
     */
    public function testLunarDateOuput(string $date, array $lunar)
    {
        $date = new DateTime($date, BaseAdapter::getTimeZone());
        $lunarAdapter = new LunarDateTimeAdapter($date->format('Y'), $date->format('n'), $date->format('j'));

        $this->assertEquals($lunar[0], $lunarAdapter->getYear());
        $this->assertEquals($lunar[1], $lunarAdapter->getMonth());
        $this->assertEquals($lunar[2], $lunarAdapter->getDay());

        // Kiểm tra tháng nhuận
        if (isset($lunar[3]) && $lunar[3] === true) {
            $this->assertEquals($lunar[1], $lunarAdapter->getLeapMonth());
        }
    }
}