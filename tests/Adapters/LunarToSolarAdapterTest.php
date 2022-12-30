<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use SebastianBergmann\RecursionContext\InvalidArgumentException;
use PHPUnit\Framework\ExpectationFailedException;
use Vantran\PhpNhamDate\Adapters\LunarToSolarAdapter;
use Vantran\PhpNhamDate\Tests\Providers\Addition11thNewMoon;
use Vantran\PhpNhamDate\Tests\Providers\AdditionLunarLeapMonth;
use Vantran\PhpNhamDate\Tests\Providers\AdditionNewMoon;

/**
 * Kiểm tra bộ chuyển đổi từ ngày tháng âm lịch sang dương lịch
 * 
 * @author Văn Trần <caovan.info@gmail.com>
 * @package Vantran\PhpNhamDate\Tests\Adapters
 */
class LunarToSolarAdapterTest extends AdapterTestCase
{
    use Addition11thNewMoon;
    use AdditionLunarLeapMonth;
    use AdditionNewMoon;

    /**
     * Kiểm tra tìm điểm sóc tháng 11 âm lịch đã qua gần nhất
     * 
     * @covers LunarToSolarAdapter
     * @dataProvider addition11thNewMonData
     * 
     * @param mixed $Y 
     * @param mixed $m 
     * @param mixed $d 
     * @return void 
     * @throws InvalidArgumentException 
     * @throws ExpectationFailedException 
     */
    public function testGet11thNewMoon($Y, $m, $d)
    {
        /**
         * Giải thích các biến số $adapter như sau:
         * - $adapter1: giả định đầu vào nằm trong tháng 12 năm Y âm lịch, mục tiêu tìm về điểm Sóc của tháng 11 âm lịch
         *   ngay trước đó (01/11 năm Y)
         * 
         * - $adapter2: giả định đầu vào là điểm sóc tháng 11 năm Y âm lịch, mục tiêu là tìm điểm sóc của chính tháng 11
         *   âm lịch đó.
         * 
         * - $adapter3: giả định đầu vào là các tháng âm lịch từ 1 đến 10 của năm Y + 1, mục tiêu là tìm về điểm sóc
         *   tháng 11 của năm Y.
         */
        $adapter1 = new LunarToSolarAdapter($Y, 12, 1);
        $adapter2 = new LunarToSolarAdapter($Y, 11, 1);
        $adapter3 = new LunarToSolarAdapter($Y + 1, 1, 1);

        $datetime = "$Y-$m-$d";

        $this->assertEquals($datetime, $adapter1->get11thNewMoon()->getDateTime()->format('Y-n-j'));
        $this->assertEquals($datetime, $adapter2->get11thNewMoon()->getDateTime()->format('Y-n-j'));
        $this->assertEquals($datetime, $adapter3->get11thNewMoon()->getDateTime()->format('Y-n-j'));
    }

    /**
     * Kiểm tra tháng âm lịch đầu vào có thể là tháng nhuận hay không
     * 
     * @covers LunarToSolarAdapter
     * @dataProvider additionLeapMonthProvider
     * 
     * @param int $Y 
     * @param int $leapMonth 
     * @return void 
     */
    public function testCanMonthToBeLeap(int $Y, int $leapMonth)
    {
        $falseMonth = $leapMonth;
        while ($falseMonth === $leapMonth) {
            $falseMonth = rand(1, 12);
        }

        $trueAdapter = new LunarToSolarAdapter($Y, $leapMonth, 1, true);
        $falseAdapter = new LunarToSolarAdapter($Y, $falseMonth, 1, false);

        $this->assertTrue($trueAdapter->canMonthToBeLeap());
        $this->assertFalse($falseAdapter->canMonthToBeLeap());
    }

    /**
     * Kiểm tra lấy các điểm sóc trong năm 2020, trong năm này nhuận tháng 4 âm lịch khi sử dụng múi giờ GMT+7
     * 
     * @covers LunarToSolarAdapter
     * @dataProvider additionNewMoon2020Provider
     * 
     * @param string $solarDate 
     * @param string $lunarMonth 
     * @return void 
     */
    public function testGet2020NewMoon(string $solarDate, string $lunarMonth)
    {
        $month = (int)$lunarMonth;
        $leap = str_contains($lunarMonth, '+'); 
        $lunarAdapter = new LunarToSolarAdapter(2020, $month, 20, $leap);
        $newMoonDate = $lunarAdapter->getNewMoon()->getDateTime();

        $this->assertEquals($solarDate, $newMoonDate->format('Y-m-d'));
    }
}