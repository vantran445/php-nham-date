<?php namespace Vantran\PhpNhamDate\Adapters;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;

/**
 * Hỗ trợ chuyển đổi một số loại đầu vào thông dụng thành góc Kinh độ mặt trời và ngược lại
 * 
 * @author Văn Trần <caovan.info@gmail.com>
 */
class SunlongitudeAdapter extends BaseAdapter implements JulianAccessableInterface, TimestampAccessableInterface
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
        protected float|int $jdn
    ) {
        $this->sl = $this->convert($this->jdn);
    }

    /**
     * Chuyển đổi KDMT từ đối tượng triển khai DateTimeInterface
     *
     * @param DateTimeInterface|null $datetime
     * @return SunLongitudeAdapter
     */
    public static function fromDateTime(?DateTimeInterface $datetime = null): SunLongitudeAdapter
    {
        if (!$datetime) {
            $datetime = new DateTime('now', new DateTimeZone('UTC'));
        }

        $jdn = JulianAdapter::fromDateTime($datetime)->getJdn();
        return new self($jdn, $datetime->getOffset());
    }

    public static function fromJdn(int|float $jdn): SunlongitudeAdapter
    {
        return new self($jdn);
    }

    /**
     * Chuyển đổi KDMT từ tem thời gian Unix
     *
     * @param integer|float $timestamp
     * @param integer $offset Phần bù múi giờ địa phương so với UTC tính bằng giây
     * @return SunlongitudeAdapter
     */
    public static function fromTimestamp(int|float $timestamp): SunlongitudeAdapter
    {
        $jdn = JulianAdapter::fromTimestamp($timestamp)->getJdn();
        return new self($jdn);
    }

    /**
     * Chuyển đổi KDMT từ nhóm thời gian nguyên thủy
     *
     * @param integer $Y    năm gồm 4 chữ số
     * @param integer $m    tháng từ 1 đến 12
     * @param integer $d    ngày từ 1 đến 31
     * @param integer $H    giờ từ 0 đến 23
     * @param integer $i    phút từ 0 đến 59
     * @param integer $s    giây từ 0 đến 59
     * @return SunlongitudeAdapter
     */
    public static function fromDateTimePrimitive(
        int $Y,
        int $m,
        int $d,
        int $H = 0,
        int $i = 0,
        int $s = 0
    ): SunlongitudeAdapter
    {
        $jdn = JulianAdapter::fromDateTimePrimitive($Y, $m, $d, $H, $i, $s)->getJdn();
        return new self($jdn);
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
        $T = ($jdn - 2451545.5) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
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
     * Trả về đối tượng mới với là điểm khởi đầu của một điểm KDMT.
     *
     * @param boolean $withHour Có bao gồm giờ hay không, mặc định không
     * @param boolean $withMinutes Có bao gồm phút hay không, mặc định không.
     * 
     * @return SunlongitudeAdapter
     */
    public function getLongitudeNewTerm()
    {
        $degree = $this->legitimizeDegee(function () {
            return $this->sl - floor($this->sl) + $this->sl % 15;
        });

        return $this->getPrevious($degree);
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
     * Khớp số ngày Julian và số đo KDMT về một góc kế tiếp
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

        // Đảm bảo giá trị bắt đầu tính toán sẽ nhỏ hơn giá trị mong muốn
        if ($sl > $deg && floor($deg) == 0) {
            $jdn -= ($jdn - floor($jdn) + 3);
            $sl = $this->convert($jdn);
        }

        $counter = 0;
        while (
            $sl > $deg && floor($deg) != 0 ||
            $deg > 345 && $sl < 15
        ) {
            if ($counter > 360) {
                throw new Exception("Error. Some operation is not correct.");
            }
            
            $counter ++;
            $jdn --;
            $sl = $this->convert($jdn);
        }

        if ($sl === $deg) {
            return;
        }

        // Tìm kết quả
        $match = function(float &$jdn, float &$sl, float $unit, float $degree) {
            $counter = 0;
            $flooredDeg = floor($degree);
            
            while ($counter < 60) {
                $jdNext = $jdn + $unit;
                $slNext = $this->convert($jdNext);

                if (
                    ($slNext >= $degree && $flooredDeg != 0) ||
                    ($degree > 345 && $slNext < 15 && $sl > 345) ||
                    ($flooredDeg == 0 && $slNext < 1 && $sl > 345)
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
     * @return SunlongitudeAdapter
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
     * @return SunlongitudeAdapter
     */
    public function getNext(int|float $degree = 15) 
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
     * @return SunlongitudeAdapter
     */
    public function getPrevious(int|float $degree = 15) 
    {   
        $jdn = $this->jdn;
        $sl = $this->sl;

        $this->matchToPrevPosition($jdn, $sl, $degree);

        return $this->cloneNewInstance($jdn, $sl);
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
     * @inheritDoc
     *
     * @param boolean $withDecimal
     * @return float
     */
    public function getJdn(bool $withDecimal = true): float
    {
        return JulianAdapter::setJdn($this->jdn)->getJdn($withDecimal);
    }

    /**
     * @inheritDoc
     *
     * @param boolean $withDecimal
     * @return float
     */
    public function getLocalJdn(bool $withDecimal = true): float
    {
        return JulianAdapter::setJdn($this->jdn)->getLocalJdn($withDecimal);
    }

    /**
     * @inheritDoc
     *
     * @return integer|float
     */
    public function getTimestamp(): int|float
    {
        return JulianAdapter::setJdn($this->jdn)->getTimestamp();
    }
}