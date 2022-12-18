<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\BaseAdapter;
use Vantran\PhpNhamDate\Adapters\SunlongitudeAdapter;

class SunlongitudeAdapterTest extends TestCase
{
    /**
     * Dữ liệu 24 điểm khởi đầu của các góc KDMT trong năm 2022 theo dương lịch, mỗi điễm cách nhau ~15 độ. Cũng tương 
     * ứng với điểm khởi của 24 tiết khí trong âm lịch. Múi giờ của tập hợp dữ liệu là GMT +7 (Việt Nam).
     * 
     * Lưu ý, thời gian về điểm bắt đầu của một góc KDMT có thể chênh lệnh một vài phút tùy thuộc vào các giải thuật.
     *
     * @link https://clearskytonight.com/projects/astronomycalculator/sun/sunlongitude.html
     * @return array
     */
    public function addition2022BeginPointProvider()
    {
        return [
            ['date' => '2022-01-05 16:00:00', 'sl' => 285.000486526473],
            ['date' => '2022-01-20 09:25:00', 'sl' => 300.000448399939],
            ['date' => '2022-02-04 03:38:00', 'sl' => 315.00111845557],
            ['date' => '2022-02-18 23:30:00', 'sl' => 330.000552395466],
            ['date' => '2022-03-05 21:35:00', 'sl' => 345.002982423317],
            ['date' => '2022-03-20 22:30:00', 'sl' => 0.00605007937692163],
            ['date' => '2022-04-05 02:10:00', 'sl' => 15.0008672352589],
            ['date' => '2022-04-20 09:15:00', 'sl' => 30.0010161698917],
            ['date' => '2022-05-05 19:20:00', 'sl' => 45.0030002455117],
            ['date' => '2022-05-21 08:15:00', 'sl' => 60.0016554633466],
            ['date' => '2022-06-05 23:20:00', 'sl' => 75.0029037529872],
            ['date' => '2022-06-21 16:10:00', 'sl' => 90.0042114061488],
            ['date' => '2022-07-07 09:30:00', 'sl' => 105.001725519045],
            ['date' => '2022-07-23 03:00:00', 'sl' => 120.002437859498],
            ['date' => '2022-08-07 19:20:00', 'sl' => 135.001112185361],
            ['date' => '2022-08-23 10:10:00', 'sl' => 150.002880585943],
            ['date' => '2022-09-07 22:30:00', 'sl' => 165.005452151502],
            ['date' => '2022-09-23 08:00:00', 'sl' => 180.004273853586],
            ['date' => '2022-10-08 14:20:00', 'sl' => 195.005103567353],
            ['date' => '2022-10-23 17:30:00', 'sl' => 210.002750657168],
            ['date' => '2022-11-07 17:40:00', 'sl' => 225.003057348988],
            ['date' => '2022-11-22 15:20:00', 'sl' => 240.00667516395],
            ['date' => '2022-12-07 10:40:00', 'sl' => 255.002954228177],
            ['date' => '2022-12-22 04:40:00', 'sl' => 270.001981764075],
        ];
    }

    /**
     * Kiểm tra đầu ra
     *
     * @dataProvider addition2022BeginPointProvider
     * @covers SunlongitudeAdapter
     * @param string $date
     * @param float $degree
     * @return void
     */
    public function testOuputs(string $date, float $degree)
    {
        BaseAdapter::resetDefaultTimeZone();

        $datetime = new DateTime($date, BaseAdapter::getTimeZone());
        $slAdapter = SunlongitudeAdapter::fromDateTime($datetime);
        $diff = abs($slAdapter->getDegree() - $degree);

        $this->assertLessThanOrEqual(0.0166, $diff);
        $this->assertEquals($datetime->format('n/j/Y'), jdtogregorian($slAdapter->getLocalJdn()));
    }
}