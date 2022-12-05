<?php namespace Vantran\PhpNhamDate\Adapters;

use DateTime;
use DateTimeInterface;
use DateTimeZone;

/**
 * Lớp tính toán Kinh độ Mặt trời tại một thời điểm và vị trí.
 * 
 * @since 1.0.0
 * @author Văn Trần <caovan.info@gmail.com>
 */
class Sunlongitude
{
    const JD_EACH_HOUR = 0.041666666666667;
    const JD_EACH_MINUTE = 0.0006944444444444444;

    protected float $sl;

    /**
     * Tạo mới đối tượng
     *
     * @param float $jdn
     * @param float $timezone
     */
    public function __construct(
        protected float $jdn,
        protected float $timezone = 0.0 // UTC

    ) {
        $this->sl = $this->convert($this->jdn, $this->timezone);
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
     * @return float 0 - 360 độ
     */
    protected function convert($jdn, $timezone = 0): float
    {
        $T = ($jdn - 2451545.5 - $timezone / 24) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
        $dr = M_PI / 180; // degree to radian
        $L = 280.460 + 36000.770 * $T; //  degree
        $G = 357.528 + 35999.050 * $T; //  degree
        $ec = 1.915 * sin($dr *$G) + 0.020 * sin($dr *2*$G);
        $lambda = $L + $ec ;// true longitude, degree
        
        return $L =  $lambda - 360 * (floor($lambda / (360))); // Normalize to (0, 360)
    }

    /**
     * Tạo đối tượng từ nhóm các thông số ngày tháng
     *
     * @param integer $Y
     * @param integer $m
     * @param integer $d
     * @param integer $H
     * @param integer $i
     * @param integer $s
     * @param integer $timezone
     * @return Sunlongitude
     */
    public static function createFromDates(
        int $Y, 
        int $m, 
        int $d, 
        int $H = 0, 
        int $i = 0, 
        int $s = 0, 
        float $timezone = 0
    ) {
        $jdn = gregoriantojd($m, $d, $Y) + ($H + $i / 60 + $s / 3600) / 24;
        return new Sunlongitude($jdn, $timezone);
    }

    /**
     * Tạo đối tượng từ một đối tượng có triển khai DateTimeInterface
     *
     * @param ?DateTimeInterface $date
     * @return Sunlongitude
     */
    public static function createFromDate(?DateTimeInterface $date = null): Sunlongitude
    {
        if ($date === null) {
            $date = new DateTime();
        }

        return Sunlongitude::createFromDates(
            $date->format('Y'),
            $date->format('m'),
            $date->format('d'),
            $date->format('H'),
            $date->format('i'),
            $date->format('s'),
            $date->getOffset() / 3600
        );
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
     * Trả về giá trị KDMT tìm được
     *
     * @param boolean $withMinutes Có bao gồm phần lẻ thập phân trong kết quả 
     * không, nếu chọn không, giá trị sẽ được làm tròn.
     * @return float
     */
    public function getDegree($withMinutes = true): float
    {
        return $withMinutes? $this->sl : floor($this->sl);
    }
    
    /**
     * Lấy phần lẻ thập phân của giá trị KDMT tại thời điểm
     *
     * @return integer
     */
    public function getMinutes(): int
    {
        return $this->getDegree() - $this->getDegree(false);
    }

    /**
     * Chuyển đổi thời điểm KDMT về mốc tem thời gian UNIX
     *
     * @return integer
     */
    public function toTimestamp(): int
    {
        return ($this->jdn - 2440587.5 - 0.5 - $this->timezone / 24) * 86400;
    }

    /**
     * Chuyển đổi thời điểm KDMT thành đối tượng DateTime
     *
     * @return DateTime
     */
    public function toDate(): DateTime
    {
        $sign = $this->timezone < 0? '-' : '+';
        $timezone = $sign . $this->timezone;
        $date = new DateTime('', new DateTimeZone($timezone));

        return $date->setTimestamp($this->toTimestamp());
    }

    /**
     * Trả về số ngày Julian tương ứng với KDMT
     *
     * @return float
     */
    public function getJdn(): float
    {
        return $this->jdn;
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
        $sl = $this->convert($jdn, $this->timezone);

        // Đảm bảo giá trị để bắt đầu tính toán sẽ lớn hơn giá trị mong muốn
        while (
            $sl > 345 && $deg < 15 ||
            $sl < $deg
        ) {
            $jdn ++;
            $sl = $this->convert($jdn, $this->timezone);
        }

        if ($sl === $deg) {
            return;
        }

        // Tìm kết quả
        $match = function(float &$jdn, float &$sl, float $unit, float $degree) {
            while (true) {
                $jdNext = $jdn - $unit;
                $slNext = $this->convert($jdNext, $this->timezone);

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
        $sl = $this->convert($jdn, $this->timezone);

        // Đảm bảo giá trị để bắt đầu tính toán sẽ nhỏ hơn giá trị mong muốn
        while (
            $sl > $deg ||
            $deg > 345 && $sl < 15
        ) {
            $jdn --;
            $sl = $this->convert($jdn, $this->timezone);
        }

        if ($sl === $deg) {
            return;
        }

        // Tìm kết quả
        $match = function(float &$jdn, float &$sl, float $unit, float $degree) {
            $counter = 0;
            while ($counter < 60) {
                $jdNext = $jdn + $unit;
                $slNext = $this->convert($jdNext, $this->timezone);

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
            $sl = $this->convert($jdn, $this->timezone);
        }
    }

    /**
     * Tạo một bản sao và các thông số mới thông qua __set magic
     *
     * @param integer|float $jdn
     * @param integer|float $sl
     * @return Sunlongitude
     */
    private function cloneNew(int|float $jdn, int|float $sl)
    {
        $ins = clone($this);
        $ins->sl = $sl;
        $ins->jdn = $jdn;

        return $ins;
    }

    /**
     * Trả về chuỗi xác định thời gian dương lịch tương ứng với góc KDMT
     *
     * @param string $formater
     * @return string
     */
    public function toDateTimeFormat(string $formater = 'd/m/Y H:i:s')
    {
        return $this->toDate()->format($formater);
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

        return $this->cloneNew($jdn, $sl);
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

        return $this->cloneNew($jdn, $sl);
    }
}