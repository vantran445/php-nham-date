<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\SunlongitudeAdapter;

class SunlongitudeAdapterTest extends TestCase
{
    /**
     * Múi giờ mặc định của các dữ liệu trong phần test này này GMT+7
     *
     * @var DateTimeZone
     */
    protected $timezone;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->timezone = new DateTimeZone('+0700');
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Dữ liệu 24 điểm khởi đầu của các góc KDMT trong năm 2022 theo dương lịch,  mỗi điễm cách nhau ~15 độ. Cũng tương 
     * ứng với điểm khởi của 24 tiết khí trong âm lịch. Múi giờ của tập hợp dữ liệu là GMT +7 (Việt Nam).
     * 
     * Lưu ý, thời gian về điểm bắt đầu của một góc KDMT có thể chênh lệnh một vài phút.
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
     * Kiểm tra tạo bộ chuyển đổi từ đối tượng DateTime
     *
     * @covers SunlongitudeAdapter
     * @dataProvider addition2022BeginPointProvider
     * @param string $dateStr
     * @param float $slExpected
     * @return void
     */
    public function testCreateFromDate(string $dateStr, float $slExpected)
    {
        $datetime = new DateTime($dateStr, $this->timezone);
        $slAdapter = SunlongitudeAdapter::fromDateTime($datetime);
        $diff = $slExpected - $slAdapter->getDegree();

        $this->assertLessThanOrEqual(0.0166, $diff);
    }

    /**
     * Kiểm tra tạo bộ chuyển đổi từ thời gian nguyên thủy
     *
     * @covers SunlongitudeAdapter
     * @dataProvider addition2022BeginPointProvider
     * @param string $dateStr
     * @param float $slExpected
     * @return void
     */
    public function testCreateFromDateTimePrimitive(string $dateStr, float $slExpected)
    {
        $datetime = new DateTime($dateStr, $this->timezone);
        $slAdapter = SunlongitudeAdapter::fromDateTimePrimitive(
            $datetime->getOffset(),
            $datetime->format('Y'),
            $datetime->format('m'),
            $datetime->format('d'),
            $datetime->format('H'),
            $datetime->format('i'),
            $datetime->format('s'),
        );

        $diff = $slExpected - $slAdapter->getDegree();

        $this->assertLessThanOrEqual(0.0166, $diff);
    }

    /**
     * Kiểm tra tìm điểm bắt đầu 15 độ
     *
     * @covers SunlongitudeAdapter
     * @dataProvider addition2022BeginPointProvider
     * @param string $dateStr
     * @param float $slExpected
     * @return void
     */
    public function testGetLongitudeNewTerm(string $dateStr, float $slExpected)
    {
        $datetime = new DateTime($dateStr, new DateTimeZone('+0700'));
        $timestamp = $datetime->getTimestamp() + 864000; // Add extra 10 days
        $slAdapter = SunlongitudeAdapter::fromTimestamp($timestamp, $datetime->getOffset());
        $slNewTerm = $slAdapter->getLongitudeNewTerm();

        $diff = $slExpected - $slNewTerm->getDegree();

        $this->assertLessThanOrEqual(0.0166, $diff);
    }

    /**
     * Kiểm tra lấy dữ liệu vị trí kinh độ kế tiếp trong năm 2022
     *
     * @covers SunlongitudeAdapter
     * @return void
     */
    public function testNextPositions() 
    {
        $_2022data = $this->addition2022BeginPointProvider();
        $slStart = SunLongitudeAdapter::fromDateTime(new DateTime('2022-01-01T16:04:52+0700'));

        foreach ($_2022data as $data) {
            $nextDegree = $data['sl'] - $slStart->getDegree();

            if ($nextDegree < 0) {
                $nextDegree += 360;
            }

            $slNext = $slStart->getNext($nextDegree);
            $output = $slNext->getDegree();

            $this->assertLessThanOrEqual(
                0.01, 
                abs($data['sl'] - $output)
            );
        }
    }

    /**
     * Kiểm tra lấy dữ liệu vị trí kinh độ trước trong năm 2022
     *
     * @covers SunlongitudeAdapter
     * @return void
     */
    public function testPreviosPositions()
    {
        $_2022data = $this->addition2022BeginPointProvider();
        $counter = count($_2022data);
        $slStart = SunLongitudeAdapter::fromDateTime(new DateTime('2023-01-01T00:00:00+0700'));

        $slPrev = $slStart->getPrevious()->getDegree();

        for ($i = $counter; $i > 0; $i --) {
            $slCompare = $_2022data[$i - 1]['sl'];
            $prevDegress = $slStart->getDegree() - $slCompare;

            if ($prevDegress < 0) {
                $prevDegress + 360;
            }

            $slPrev = $slStart->getPrevious($prevDegress);

            $this->assertLessThan(0.001, abs($slPrev->getDegree() - $slCompare));
        }
    }

}