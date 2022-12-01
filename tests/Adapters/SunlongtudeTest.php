<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
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
     * Kiểm tra KDMT thời điểm viết test và trả về đối tượng cho một số phụ thuộc
     *
     * @covers Sunlongitude
     * @return Sunlongitude
     */
    public function testInstance(): Sunlongitude
    {
        /**
         * Thời gian test được viết tại Đăk Nông ngày 01 tháng 12 năm 2022, lúc
         * 10 giờ 10 phút (đêm) - GMT+7
         * 
         * KDMT đối chiếu: 249.40392880463 (clearskytonight.com)
         * KDMT tính toán được: 249.40098961882
         */
        $compareSl = 249.40392880463;
        $timezone = new DateTimeZone('+0700');
        $date = new DateTime('2022-12-01 22:10:00', $timezone);
        $sl = Sunlongitude::createFromDate($date);

        $this->assertEquals(249, $sl->getDegree(false));
        $this->assertLessThan(0.01, abs($compareSl - $sl->getDegree()));
        
        return $sl;
    }

    /**
     * Kiểm tra 1 tập hợp kết quả trong năm 2022, với mức sai số 0.01 phút.
     *
     * @dataProvider addition2022Provider
     * @covers Sunlongitude
     * @param string $date
     * @param float $slVal
     * @return void
     */
    public function test2022OResuilt(string $date, float $slVal)
    {
        $date = new DateTime("$date 00:00:00", new DateTimeZone('+0700'));
        $sl = Sunlongitude::createFromDate($date);

        $this->assertLessThan(0.01, abs($slVal - $sl->getDegree(true)));
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
}