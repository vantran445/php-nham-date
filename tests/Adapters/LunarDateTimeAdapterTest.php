<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
use DateTimeImmutable;
use Vantran\PhpNhamDate\Adapters\BaseAdapter;
use Vantran\PhpNhamDate\Adapters\LunarDateTimeAdapter;
use Vantran\PhpNhamDate\Tests\Providers\Addition11thNewMoon;
use Vantran\PhpNhamDate\Tests\Providers\AdditionLunarLeapMonth;
use Vantran\PhpNhamDate\Tests\Providers\AdditionNewMoon;

class LunarDateTimeAdapterTest extends AdapterTestCase
{
    use Addition11thNewMoon;
    use AdditionLunarLeapMonth;
    use AdditionNewMoon;

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