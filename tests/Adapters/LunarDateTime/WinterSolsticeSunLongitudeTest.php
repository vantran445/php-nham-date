<?php namespace Vantran\PhpNhamDate\Tests\Adapters\LunarDateTime;

use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;
use Vantran\PhpNhamDate\Adapters\LunarDateTimeAdapter;

class WinterSolsticeSunLongitudeTest extends TestCase
{
    /**
     * Phần bù múi giờ GMT+7 so với UTC
     *
     * @var integer
     */
    protected $offset = 25200;

    /**
     * Điểm khởi Đông Chí từ 2012 đến 2022 tại Việt Nam
     *
     * @return array
     */
    public function addtionWinterSolsticePointsProvider(): array
    {
        // Đông chí thường rơi vào ngày 22 tháng 12 dương lịch, cũng có khi là ngày 21 hoặc 23.
        return [
            [2022, 12, 22],
            [2021, 12, 22],
            [2020, 12, 21],
            [2019, 12, 22],
            [2018, 12, 22],
            [2017, 12, 22],
            [2016, 12, 21],
            [2015, 12, 22],
            [2014, 12, 22],
            [2013, 12, 22],
            [2012, 12, 21],
        ];
    }

    /**
     * Kiểm tra lấy Kinh độ mặt trời tại điểm Đông Chí (270 độ). Trong việc lập Âm lịch không quan trọng giờ lập Trung
     * khí, do vậy sự chênh lệch có thể trong khoảng 1 ngày.
     *
     * @covers LunarDateTimeAdapter
     * @dataProvider addtionWinterSolsticePointsProvider
     * @param [type] $Y
     * @param [type] $m
     * @param [type] $d
     * @return void
     */
    public function testWinterSolsticeStartingPoints($Y, $m, $d)
    {
        $jdAdapter = JulianAdapter::fromDateTimePrimitive($Y, $m, $d); // Lúc 00:00 UTC
        $lunar = new LunarDateTimeAdapter($Y, $m, $d, $this->offset);
        $winterSolsticeSl = $lunar->getWinterSolsticeSunLongitude($Y);

        $inputJdn = $jdAdapter->getJdn();
        $outputJdn = $winterSolsticeSl->getJdn();

        /**
         * Trong trường hợp ngày tháng cần kiểm tra lớn hơn kết quả đầu ra, thì khoảng cách chỉ nên chênh lệch trong
         * khoảng 2 giờ (0.08 ngày), người ta sẽ làm tròn điểm lập trung khí vào ngày hôm sau.
         * 
         * Trong trường hợp ngày tháng cần kiểm tra nhỏ hơn kết quả đầu ra, thì khoảng cách chênh lệnh không được vượt
         * quá 1 ngày, bởi như vậy là giải thuật không chính xác, độ lệch quá lơn.
         */
        if ($inputJdn > $outputJdn) {
            $this->assertLessThanOrEqual(0.08, $inputJdn - $outputJdn);
        }
        else {
            $this->assertLessThanOrEqual(0.9, $outputJdn - $inputJdn);
        }
    }
}