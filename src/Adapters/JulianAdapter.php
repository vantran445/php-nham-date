<?php namespace Vantran\PhpNhamDate\Adapters;

use DateTimeInterface;

/**
 * Lớp hỗ trợ chuyển đổi các loại giá trị đầu vào thành số ngày Julian và chuyển đổi ngược số ngày Julian thành một số 
 * loại giá trị thông dụng khác.
 * 
 * Lưu ý rằng số ngày Julian tương ứng với giờ UTC mà không sử dụng giờ địa phương.
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
        $timestamp = (!$datetime)? time() : $datetime->getTimestamp();
        return self::fromTimestamp($timestamp);
    }

    /**
     * Chuyển đổi từ các tham số ngày tháng nguyên thủy (dương lịch). Hàm này giả định rằng các tham số bạn truyền vào
     * tương ứng với giờ UTC. Nếu bạn sử dụng giờ địa phương, nên tạo bộ chuyển đổi từ tem thời gian Unix hoặc đối tượng
     * DateTime thay thế.
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
     * @param integer|float $timestamp tem thời gian Unix 
     * @return JulianAdapter
     */
    public static function fromTimestamp(int|float $timestamp): JulianAdapter
    {
        $jdn = $timestamp  / 86400 + self::JDN_EPOCH_TIME;
        return new self($jdn);
    }

    /**
     * Nhận trực tiếp giá trị đầu vào là số ngày Julian để có thể chuyển đổi
     * ngược thành các loại giá trị khác
     *
     * @param integer|float $jdn
     * @return JulianAdapter
     */
    public static function setJdn(int|float $jdn): JulianAdapter
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
     * Trả về tem thời gian Unix tương ứng
     *
     * @return int|float
     */
    public function toTimestamp()
    {
        if (!$this->unix) {
            $this->unix = ($this->getJdn() - self::JDN_EPOCH_TIME) * 86400;
        }

        return $this->unix;
    }
}