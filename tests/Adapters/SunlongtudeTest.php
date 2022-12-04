<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\Sunlongitude;

/**
 * Để đối chiếu các kết quả truy cập đường dẫn bên dưới và nhập vào các thông số
 * tương đương. Thuật toán tìm kinh độ Mặt trời có một số phương pháp, chương
 * trình này sử dụng phương pháp 4, với sai số nhất định ở hàng thập phân, do đó
 * các phép so sánh không sử dụng các giá trị bằng tuyệt đối.
 * 
 * @link https://clearskytonight.com/projects/astronomycalculator/sun/sunlongitude.html
 * @link https://tutorialspots.com/php-some-method-of-determining-the-suns-longitude-part-2-2479.html
 */
class SunlongtudeTest extends TestCase
{
    /**
     * Múi giờ mặc định
     *
     * @var DateTimeZone
     */
    protected $timezone;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->timezone = new DateTimeZone('+0700');
    }

    /**
     * Kiểm tra các giá trị trước năm 1970
     *
     * @covers Sunlongitude
     * @return void
     */
    public function testBefore1970() {
        // 1969-01-15 10:30:00 UTC
        $sl = Sunlongitude::createFromDates(1969, 1, 15, 10, 30);
        $prev = $sl->toPrevious(2);
        $start = $sl->toStartingPoint();

        $this->assertEquals('13/01/1969', $prev->toDateTimeFormat('d/m/Y'));
        $this->assertEquals('05/01/1969 12', $start->toDateTimeFormat('d/m/Y H'));
        $this->assertEquals('1/5/1969', jdtogregorian($start->getJdn()));
        $this->assertLessThan(0.0166, abs($sl->getDegree() - 295.120198912363));
    }

    /**
     * Kiểm tra 1 tập hợp kết quả trong năm 2022, sai số trong phạm vi 60 phút 
     * (1 giờ) - tương đương ~0.0166 độ.
     *
     * @dataProvider addition2022Provider
     * @covers Sunlongitude
     * @param string $date
     * @param float $slVal
     * @return void
     */
    public function test2022Resuilt(string $date, float $slVal)
    {
        $date = new DateTime("$date 00:00:00", new DateTimeZone('+0700'));
        $sl = Sunlongitude::createFromDate($date);

        $this->assertLessThan(0.0166, abs($slVal - $sl->getDegree(true)));
    }

    /**
     * Kiểm tra 1 tập hợp kết quả 24 điểm bắt đầu KDMT trong năm 2022, sai số
     * trong phạm vi 60 phút (1 giờ) - tương đương ~0.0166 độ.
     *
     * @dataProvider addition2022BeginPointProvider
     * @covers Sunlongitude
     * @param string $date
     * @param float $slVal
     * @return void
     */
    public function testStartingPoints(string $dateStr, $slCompare)
    {
        $date = new DateTimeImmutable($dateStr, $this->timezone);
        $nextDate = $date->modify('+ 1 day');

        $slBegin = Sunlongitude::createFromDate($nextDate)
                        ->toStartingPoint()
                        ->getDegree();

        $this->assertLessThanOrEqual(0.0166, abs($slBegin - $slCompare));
        $this->assertEquals(floor($slCompare), floor($slBegin));
    }

    /**
     * Kiểm tra lấy dữ liệu vị trí kinh độ kế tiếp trong năm 2022
     *
     * @covers Sunlongitude
     * @return void
     */
    public function testNextPositions() 
    {
        $_2022data = $this->addition2022BeginPointProvider();
        $slStart = Sunlongitude::createFromDate(new DateTime('2022-01-01 16:04:52', $this->timezone));

        foreach ($_2022data as $data) {
            $nextDegree = $data['sl'] - $slStart->getDegree();

            if ($nextDegree < 0) {
                $nextDegree += 360;
            }

            $slNext = $slStart->toNext($nextDegree)->getDegree();
            $this->assertLessThanOrEqual(0.001, abs($data['sl'] - $slNext));
        }
    }

    /**
     * Kiểm tra lấy dữ liệu vị trí kinh độ trước trong năm 2022
     *
     * @covers Sunlongitude
     * @return void
     */
    public function testPreviosPositions()
    {
        $_2022data = $this->addition2022Provider();
        $counter = count($_2022data);
        $slStart = Sunlongitude::createFromDate(new DateTime('2023-01-01 00:00:00', $this->timezone));

        $slPrev = $slStart->toPrevious()->getDegree();

        for ($i = $counter; $i > 0; $i --) {
            $slCompare = $_2022data[$i - 1]['sl'];
            $prevDegress = $slStart->getDegree() - $slCompare;

            if ($prevDegress < 0) {
                $prevDegress + 360;
            }

            $slPrev = $slStart->toPrevious($prevDegress);

            $this->assertLessThan(0.001, abs($slPrev->getDegree() - $slCompare));
        }
    }

    /**
     * Dữ liệu so sánh trong năm 2022, được lấy vào thời điểm mùng 1 và 15 mỗi
     * tháng. vào lúc 00:00:00 (0 giờ sáng), múi giờ GMT+7
     *
     * @link https://clearskytonight.com/projects/astronomycalculator/sun/sunlongitude.html
     * @return array
     */
    public function addition2022Provider()
    {
        return [
            ['date' => '2022-01-01', 'sl' => 280.242268375155],
            ['date' => '2022-01-15', 'sl' => 294.51149074748],
            ['date' => '2022-02-01', 'sl' => 311.802017600556],
            ['date' => '2022-02-15', 'sl' => 325.984503615979],
            ['date' => '2022-03-01', 'sl' => 340.087163738169],
            ['date' => '2022-03-15', 'sl' => 354.098325158878],
            ['date' => '2022-04-01', 'sl' => 10.9651653198798],
            ['date' => '2022-04-15', 'sl' => 24.7352277079809],
            ['date' => '2022-05-01', 'sl' => 40.3398261656552],
            ['date' => '2022-05-15', 'sl' => 53.8912431206067],
            ['date' => '2022-06-01', 'sl' => 70.2382449850886],
            ['date' => '2022-06-15', 'sl' => 83.6340137171336],
            ['date' => '2022-07-01', 'sl' => 98.9025082335133],
            ['date' => '2022-07-15', 'sl' => 112.250716282277],
            ['date' => '2022-08-01', 'sl' => 128.485719751876],
            ['date' => '2022-08-15', 'sl' => 141.900275567573],
            ['date' => '2022-09-01', 'sl' => 158.284797045662],
            ['date' => '2022-09-15', 'sl' => 171.869113028216],
            ['date' => '2022-10-01', 'sl' => 187.524156529792],
            ['date' => '2022-10-15', 'sl' => 201.33372535134],
            ['date' => '2022-11-01', 'sl' => 218.257000197568],
            ['date' => '2022-11-15', 'sl' => 232.301809709032],
            ['date' => '2022-12-01', 'sl' => 248.467971354473],
            ['date' => '2022-12-15', 'sl' => 262.678611917571],
        ];
    }

    /**
     * Dữ liệu 24 điểm khởi đầu của các góc KDMT trong năm 2022 theo dương lịch, 
     * mỗi điễm cách nhau ~15 độ. Cũng tương ứng với điểm khởi của 24 tiết khí 
     * trong âm lịch. Múi giờ của tập hợp dữ liệu là GMT +7 (Việt Nam).
     * 
     * Lưu ý, thời gian về điểm bắt đầu của một góc KDMT có thể chênh lệnh một
     * vài phút.
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
}