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
     * @param DateTimeInterface $date
     * @return Sunlongitude
     */
    public static function createFromDate(DateTimeInterface $date): Sunlongitude
    {
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
     * Tìm số ngày Jdn là điểm bắt đầu của một góc KDMT, mỗi góc tương ứng 15 độ
     *
     * @param float $jdn
     * @param float $sl
     * @param float $timezone
     * @param boolean $withHour
     * @param boolean $withMinutes
     * @return void
     */
    protected function matchJdBegin(float &$jdn, float &$sl, float $timezone, bool $withHour = false, bool $withMinutes = false)
    {
        $breakPoint = $sl < 15
            ? 0
            : floor($sl) - $sl % 15;

        $match = function(&$jdn, &$sl, $jdMatchUnit, $breakPoint, $timezone) {
            while (true) {
                $nextJd = $jdn - $jdMatchUnit;
                $nextSl = $this->convert($nextJd, $timezone);

                if (
                    ($breakPoint === 0 && $nextSl > 359)|| 
                    ($breakPoint > 0 && $nextSl < $breakPoint)
                ) {
                    break;
                }

                $sl = $nextSl;
                $jdn = $nextJd;
            }
        };

        $match($jdn, $sl, 1, $breakPoint, $timezone);

        if ($withHour) {
            $match($jdn, $sl, 0.041666666666667, $breakPoint, $timezone);

            if ($withMinutes) {
                $match($jdn, $sl, 0.016666666666667, $breakPoint, $timezone);
            }
        }
    }

    /**
     * Trả về đối tượng với là điểm khởi đầu của một điểm KDMT.
     *
     * @param boolean $withHour Có bao gồm giờ hay không, mặc định không
     * @param boolean $withMinutes Có bao gồm phút hay không, mặc định không.
     * 
     * @return Sunlongitude
     */
    public function getStartingPoint($withHour = false, $withMinutes = false)
    {
        $jdn = $this->jdn;
        $sl = $this->sl;

        $this->matchJdBegin($jdn, $sl, $this->timezone, $withHour, $withMinutes);
        $ins = clone($this);
        $ins->jdn = $jdn;
        $ins->sl = $sl;

        return $ins;
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
}