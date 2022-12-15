<?php namespace Vantran\PhpNhamDate\Adapters;

use DateTime;
use DateTimeInterface;
use DateTimeZone;

/**
 * Lớp hỗ trợ chuyển đổi các loại giá trị đầu vào thành số ngày Julian và chuyển
 * đổi ngược số ngày Julian thành các loại giá trị khác.
 * 
 * @author Văn Trần <caovan.info@gmail.com>
 */
class JulianAdapter
{
    /**
     * Số ngày Julian tại thời điểm 1990-01-01T00:00+0000
     */
    const JDN_EPOCH_TIME = 2440588;

    /**
     * Lưu trữ và truy xuất các thuộc tính động thông qua __set, __get
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Khởi tạo đối tượng mới
     *
     * @param null|float|int $jdn
     */
    public function __construct(
        protected float $jdn = self::JDN_EPOCH_TIME
    ){}

    /**
     * Chuyển đổi từ đối tượng triển khai DateTimeInterface
     *
     * @param DateTimeInterface|null $datetime
     * @return JulianAdapter
     */
    public static function fromDateTime(?DateTimeInterface $datetime = null): JulianAdapter
    {
        if (!$datetime) {
            $datetime = new DateTime('now');
        }

        return self::fromDateTimePrimitive(
            $datetime->format('Y'),
            $datetime->format('m'),
            $datetime->format('d'),
            $datetime->format('H'),
            $datetime->format('i'),
            $datetime->format('s')
        );
    }

    /**
     * Chuyển đổi từ các tham số ngày tháng nguyên thủy (dương lịch)
     *
     * @param integer $Y năm với 4 chữ số, vd: 1990, 2022...
     * @param integer $m tháng trong năm - từ 1 - 12
     * @param integer $d ngày trong tháng từ 1- 31
     * @param integer $H giờ trong ngày từ 0 - 23
     * @param integer $i số phút từ 0 - 59
     * @param integer $s số giây từ 0 - 59
     * @return JulianAdapter
     */
    public static function fromDateTimePrimitive(int $Y, int $m, int $d, int $H = 0, int $i = 0, int $s = 0): JulianAdapter
    {
        $jdn = gregoriantojd($m, $d, $Y);
        $jdn += ($H * 3600 + $i * 60 + $s) / 86400;

        return new self($jdn);
    }

    /**
     * Chuyển đổi từ tem thời gian Unix
     *
     * @param integer|float $timestamp tem thời gian UTC
     * @param integer $offset phần bù giờ địa phương, tính bằng giây. Ví dụ, tại Việt Nam vào năm 2022 sử dụng múi giờ 
     *                        GMT+7, thì phần bù này có thể tính theo công thức (offset = 7 * 3600) 
     * @return JulianAdapter
     */
    public static function fromTimestamp(int|float $timestamp, int $offset): JulianAdapter
    {
        $jdn = ($timestamp + $offset)  / 86400 + self::JDN_EPOCH_TIME;
        return new self($jdn);
    }

    /**
     * Nhận trực tiếp giá trị đầu vào là số ngày Julian để có thể chuyển đổi
     * ngược thành các loại giá trị khác
     *
     * @param integer|float $jdn
     * @return JulianAdapter
     */
    public static function fromJdn(int|float $jdn): JulianAdapter
    {
        return new self($jdn);
    }

    /**
     * Magic get
     *
     * @param string $name
     * @return void
     */
    public function __get(string $name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * Magic set
     *
     * @param string $name
     * @param string $value
     */
    public function __set(string $name, string $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Trả về số ngày Julian
     *
     * @param boolean $withDecimal có bao gồm phần thập phân (xác định giờ, phút giây) hay không, mặc định có.
     * 
     * @return float
     */
    public function getJdn(bool $withDecimal = true): float
    {
        return ($withDecimal)? $this->jdn : floor($this->jdn);
    }

    /**
     * Chuyển đổi ngày Julius về tem thời gian Unix. Tem thời gian nhận được theo
     * giờ UTC+0. 
     *
     * @param int|float $offset khi sử dụng múi giờ KHÁC UTC+0, cần xác định phần bù (tính bằng giây) để kết quả trả về  
     *                          chính xác. Chẳng hạn, Việt Nam sử dụng múi giờ GMT+7 vào thời điểm 2022 thì phần bù là 
     *                          25200 giây, công thức cơ bản để tính phần bù này là: offset = 7 x 3600.
     * @return int|float
     */
    public function toTimestamp(int|float $offset = 0)
    {
        if (is_callable($offset)) {
            $offset = $offset();
        }
        
        if (!$this->unix) {
            $this->unix = ($this->getJdn() - self::JDN_EPOCH_TIME) * 86400;
        }

        return $this->unix - $offset;
    }

    /**
     * Chuyển đổi ngày Julius về đối tượng triển khai DateTimeInterface
     *
     * @param DateTimeZone|string|null $timezone
     * @return \DateTimeInterface
     */
    public function toDateTime(string|DateTimeZone $timezone = 'UTC')
    {
        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }

        $date = new DateTime('now', $timezone);
        $offset = $date->getOffset();
        $date->setTimestamp($this->toTimestamp($offset));

        return $date;
    }
}