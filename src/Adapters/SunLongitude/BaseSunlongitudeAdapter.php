<?php namespace Vantran\PhpNhamDate\Adapters\SunLongitude;

use DateTimeInterface;
use DateTimeZone;
use Vantran\PhpNhamDate\Adapters\Factories\JulianAdapter;

class BaseSunlongitudeAdapter
{
    const JD_EACH_HOUR = 0.04166666666;
    const JD_EACH_MINUTE = 0.00069444444;

    /**
     * Góc KDMT tương ứng với ngày Julius
     *
     * @var float
     */
    protected float $sl;

    /**
     * Tạo mới đối tượng
     *
     * @param float|integer $jdn
     * @param float|integer $sl
     * @param integer $offset
     */
    public function __construct(
        protected float|int $jdn,
        protected int $offset
    ) {
        $this->sl = $this->convert($this->jdn);
    }

    /**
     * Cho phép điều chỉnh linh động một số thuộc tính
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value)
    {
        if ($name === 'sl' || $name === 'jdn') {
            $this->{$name} = floatval($value);
        }
    }

    /**
     * Chuyển đổi số ngày Julian đầu vào thành góc kinh độ mặt trời
     *
     * @param float|integer $jdn
     * @param integer $offset phần bù giờ địa phương, tính bằng giây
     * @return float
     */
    protected function convert(float|int $jdn): float
    {
        $T = ($jdn - 2451545 - $this->offset / 86400) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
        $dr = M_PI / 180; // degree to radian
        $L = 280.460 + 36000.770 * $T; //  degree
        $G = 357.528 + 35999.050 * $T; //  degree
        $ec = 1.915 * sin($dr *$G) + 0.020 * sin($dr *2*$G);
        $lambda = $L + $ec ;// true longitude, degree
        
        return $L =  $lambda - 360 * (floor($lambda / (360))); // Normalize to (0, 360)
    }

    /**
     * Hợp lệ hóa số đo độ
     *
     * @param integer|float|callable $degree
     * @return int|float
     */
    protected function legitimizeDegee(int|float|callable $degree)
    {
        if (is_callable($degree)) {
            $degree = $degree();
        }

        if ($degree < 0) {
            while ($degree < 0) {
                $degree += 360;
            }
        }

        if ($degree >= 360) {
            while ($degree >= 360) {
                $degree -= 360;
            }
        }

        return $degree;
    }

    /**
     * Trả về đối tượng với là điểm khởi đầu của một điểm KDMT.
     *
     * @param boolean $withHour Có bao gồm giờ hay không, mặc định không
     * @param boolean $withMinutes Có bao gồm phút hay không, mặc định không.
     * 
     * @return Sunlongitude
     */
    public function toStartingPoint()
    {
        $degree = $this->legitimizeDegee(function () {
            return $this->sl - floor($this->sl) + $this->sl % 15;
        });

        return $this->toPrevious($degree);
    }

    /**
     * Khớp số ngày Julian và số đo KDMT về một góc trước đó (tìm lùi về)
     *
     * @param float $jdn Ngày Julian bắt đầu
     * @param float $sl Số đo KDMT bắt đầu
     * @param integer $degree Góc KDMT cần giảm
     * @return void
     */
    private function matchToPrevPosition(float &$jdn, float &$sl, float $degree = 15)
    {
        $deg = $this->legitimizeDegee($sl - $degree + 360);
        $jdn = $jdn - ($degree % 360) * 1.0145625;
        $sl = $this->convert($jdn);

        // Đảm bảo giá trị để bắt đầu tính toán sẽ lớn hơn giá trị mong muốn
        while (
            $sl > 345 && $deg < 15 ||
            $sl < $deg
        ) {
            $jdn ++;
            $sl = $this->convert($jdn);
        }

        if ($sl === $deg) {
            return;
        }

        // Tìm kết quả
        $match = function(float &$jdn, float &$sl, float $unit, float $degree) {
            while (true) {
                $jdNext = $jdn - $unit;
                $slNext = $this->convert($jdNext);

                if (
                    ($slNext <= $degree) ||
                    ($slNext > 345 && $degree < 15 && $sl > 0)
                ) {
                    break;
                }

                $jdn = $jdNext;
                $sl = $slNext;
            }
        };

        $match($jdn, $sl, 1, $deg);
        $match($jdn, $sl, self::JD_EACH_HOUR, $deg);
        $match($jdn, $sl, self::JD_EACH_MINUTE, $deg);
    }

    /**
     * Khớp số ngày Julian và số đo KDMT về một góc trước đó (tìm lùi về)
     *
     * @param float $jdn Ngày Julian bắt đầu
     * @param float $sl Số đo KDMT bắt đầu
     * @param integer $degree Góc KDMT cần giảm
     * @return void
     */
    private function matchToNextPosition(float &$jdn, float &$sl, float $degree = 15)
    {
        $deg = $this->legitimizeDegee($sl + $degree);
        $jdn = $jdn + ($degree % 360) * 1.0145625;
        $sl = $this->convert($jdn);

        // Đảm bảo giá trị để bắt đầu tính toán sẽ nhỏ hơn giá trị mong muốn
        while (
            $sl > $deg ||
            $deg > 345 && $sl < 15
        ) {
            $jdn --;
            $sl = $this->convert($jdn);
        }

        if ($sl === $deg) {
            return;
        }

        // Tìm kết quả
        $match = function(float &$jdn, float &$sl, float $unit, float $degree) {
            $counter = 0;
            while ($counter < 60) {
                $jdNext = $jdn + $unit;
                $slNext = $this->convert($jdNext);

                if (
                    ($slNext >= $degree) ||
                    ($degree > 345 && $slNext < 15 && $sl > 345)
                ) {
                    break;
                }

                $jdn = $jdNext;
                $sl = $slNext;

                $counter ++;
            }
        };

        $match($jdn, $sl, 1, $deg);

        $match($jdn, $sl, self::JD_EACH_HOUR, $deg);
        $match($jdn, $sl, self::JD_EACH_MINUTE, $deg);

        if (floor($sl) !== floor($deg)) {
            $jdn += self::JD_EACH_MINUTE;
            $sl = $this->convert($jdn);
        }
    }

    /**
     * Tạo một bản sao và các thông số mới thông qua __set magic
     *
     * @param integer|float $jdn
     * @param integer|float $sl
     * @return Sunlongitude
     */
    protected function cloneNewInstance(int|float $jdn, int|float $sl)
    {
        $ins = clone($this);
        $ins->sl = $sl;
        $ins->jdn = $jdn;

        return $ins;
    }

    /**
     * Trả về đối tượng vị trí KDMT kế tiếp
     * 
     * @param integer $degree Xác định số đo góc sẽ được cộng thêm từ góc hiện
     * tại. Ví dụ, góc hiện tại là 274, sẽ trả về  góc 274 + 15 = 29 độ.
     * @return Sunlongitude
     */
    public function toNext(int|float $degree = 15) 
    {
        $jdn = $this->jdn;
        $sl = $this->sl;

        $this->matchToNextPosition($jdn, $sl, $degree);

        return $this->cloneNewInstance($jdn, $sl);
    }

    /**
     * Trả về đối tượng vị trí KDMT trước đó
     * 
     * @param integer $degree Xác định số đo góc sẽ được trừ đi kể từ góc hiện
     * tại. Ví dụ, góc hiện tại là 274, sẽ trả về  góc 274 - 15 = 259.
     * @return Sunlongitude
     */
    public function toPrevious(int|float $degree = 15) 
    {   
        $jdn = $this->jdn;
        $sl = $this->sl;

        $this->matchToPrevPosition($jdn, $sl, $degree);

        return $this->cloneNewInstance($jdn, $sl);
    }

    /**
     * Chuyển đổi góc KDMT về tem thời gian Unix. Tem thời gian nhận được theo
     * giờ UTC+0. 
     *
     * @param (callable():int|float)|int|float $offset khi sử dụng múi giờ KHÁC UTC+0, cần xác định phần bù (tính bằng 
     *                                                 giây) để kết quả trả về  chính xác. Chẳng hạn, Việt Nam sử dụng 
     *                                                 múi giờ GMT+7 vào thời điểm 2022 thì phần bù là 25200 giây, công 
     *                                                 thức cơ bản để tính phần bù này là: offset = 7 x 3600.
     * @return int|float
     */
    public function toTimeStamp(int $offset = 0)
    {
        $adapter = JulianAdapter::make($this->jdn);
        return $adapter->toTimeStamp($offset);
    }

    /**
     * Chuyển đổi góc KDMT thành đối tượng DateTime
     *
     * @param string|null|DateTimeZone|null $timezone
     * @return DateTimeInterface
     */
    public function toDateTime(string|null|DateTimeZone $timezone = null): DateTimeInterface
    {
        $adapter = JulianAdapter::make($this->jdn);
        return $adapter->toDateTime($timezone);
    }

    /**
     * Trả về giá trị KDMT tìm được
     *
     * @param boolean $withDecimal Có bao gồm phần lẻ thập phân trong kết quả 
     * không, nếu chọn không, giá trị sẽ được làm tròn.
     * @return float
     */
    public function getDegree($withDecimal = true): float
    {
        return $withDecimal? $this->sl : floor($this->sl);
    }

    /**
     * Trả về phần thập phân của KDMT
     *
     * @return float
     */
    public function getDecimal(): float
    {
        return $this->getDegree() - $this->getDegree(false);
    }

    /**
     * Trả về số ngày Julian tương ứng với KDMT
     *
     * @return float|int
     */
    public function getJdn(bool $withDecimal = true): float|int
    {
        return $withDecimal ? $this->jdn : floor($this->jdn);
    }
}