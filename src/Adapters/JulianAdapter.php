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
class JulianAdapter implements JulianAccessableInterface, TimestampAccessableInterface
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
     * Chuyển đổi từ các tham số ngày tháng nguyên thủy (dương lịch). Vì ngày Julian tương ứng với UTC, do đó nếu các
     * mốc thời gian đầu vào là giờ địa phương, cần phản xác định được phần bù thời gian chênh lệnh để nhận kết quả chính
     * xác. Chẳng hạn, vào năm 2022 tại Việt Nam sử dụng múi giờ GMT+7, ta được 7 x 3600 = 25200 giây
     *
     * @param integer $offset   phần bù múi giờ địa phương, tính bằng giây
     * @param integer $Y        năm với 4 chữ số, vd: 1990, 2022...
     * @param integer $m        tháng trong năm - từ 1 - 12
     * @param integer $d        ngày trong tháng từ 1- 31
     * @param integer $H        giờ trong ngày từ 0 - 23
     * @param integer $i        số phút từ 0 - 59
     * @param integer $s        số giây từ 0 - 59
     * @return JulianAdapter
     */
    public static function fromDateTimePrimitive(int $offset, int $Y, int $m, int $d, int $H = 0, int $i = 0, int $s = 0): JulianAdapter
    {
        $jdn = gregoriantojd($m, $d, $Y);
        $jdn += ($H * 3600 + $i * 60 + $s) / 86400;

        if ($offset !== 0) {
            $jdn -= $offset / 86400;
        }

        return new self($jdn);
    }

    /**
     * Tạo bộ chuyển đổi từ một bộ chuyển đổi khác
     *
     * @param JulianAccessableInterface|TimestampAccessableInterface $adapter
     * @return JulianAdapter
     */
    public static function fromAdapter(JulianAccessableInterface|TimestampAccessableInterface $adapter): JulianAdapter
    {
        if ($adapter instanceof JulianAccessableInterface) {
            return self::setJdn($adapter->getJdn());
        }

        return self::fromTimestamp($adapter->getTimestamp());
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
     * Nhận trực tiếp giá trị đầu vào là số ngày Julian để có thể chuyển đổi ngược thành các loại giá trị khác
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
     * @inheritDoc
     * 
     * @return float
     */
    public function getJdn(bool $withDecimal = true): float
    {
        return ($withDecimal)? $this->jdn : floor($this->jdn);
    }

    /**
     * @inheritDoc
     *
     * @return float
     */
    public function getJdnDecimal(): float
    {
        return $this->getJdn() - $this->getJdn(false);
    }

    /**
     * @inheritDoc
     *
     * @return integer|float
     */
    public function getTimestamp(): int|float
    {
        if (!$this->unix) {
            $this->unix = ($this->getJdn() - self::JDN_EPOCH_TIME) * 86400;
        }

        return $this->unix;
    }
}