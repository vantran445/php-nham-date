<?php namespace Vantran\PhpNhamDate\Tests\Adapters\LunarDateTime;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;
use Vantran\PhpNhamDate\Adapters\LunarDateTime\BaseLunarDateTimeAdapter;
use Vantran\PhpNhamDate\Adapters\LunarDateTimeAdapter;

/**
 * Kiểm tra ngày dương lịch tương ứng với điểm khởi đầu tháng 11 âm lịch.
 * 
 * @author Văn Trần <caovan.info@gmail.com>
 */
class NewMoon11thMonthTest extends TestCase
{
    /**
     * Phần bù múi giờ GMT+7 so với UTC
     *
     * @var integer
     */
    protected $offset = 25200;

    /**
     * Mảng dữ liệu điểm bắt đầu 01 tháng 11 âm lịch của một số năm. Dữ liệu được
     * khớp từ một số phần mềm âm lịch sẵn có bằng các ngôn ngữ khác PHP. Múi giờ
     * được xác định là Asia/Ho_Chi_Minh
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

        return array_map(fn($date) => explode('-', $date), $data);
    }

    /**
     * Kiểm tra khớp điểm khởi ngày 01/11 âm lịch
     *
     * @covers BaseLunarDateTimeAdapter
     * @dataProvider addition11thNewMonData
     * @return void
     */
    public function testNewMoonOf11thMonth($Y, $m, $d)
    {
        $lunar = new LunarDateTimeAdapter($Y, $m, $d, $this->offset); // Theo giờ địa phương
        $newMoon11th = $lunar->getNewMoon11thMonth($Y);

        $datetime = new DateTimeImmutable("$Y-$m-{$d}T00:00:00+0700");
        $newMoonDateTime = $datetime->setTimestamp($newMoon11th->getTimestamp());

        /**
         * Thời điểm khởi trăng mới phải lớn hơn hoặc bằng với dữ liệu so sánh, nhưng không được vượt quá 1 ngày. Lưu ý
         * so sánh timestamp tức so sánh UTC với nhau mà bỏ qua giờ địa phương.
         */
        $this->assertTrue(
            $newMoon11th->getTimestamp() >= $datetime->getTimestamp() &&
            $newMoon11th->getTimestamp() - $datetime->getTimestamp() < 86400,
            sprintf(
                "Something went wrong:
                Expected date: %s
                Output date: %s
                ",
                $datetime->format('c'),
                $newMoonDateTime->format('c')
            )
    );
    }
}