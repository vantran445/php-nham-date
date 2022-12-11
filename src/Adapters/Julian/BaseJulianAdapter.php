<?php namespace Vantran\PhpNhamDate\Adapters\Julian;

use DateTime;
use DateTimeZone;

class BaseJulianAdapter
{
    const JDN_EPOCH_TIME = 2440587.5;

    protected $attributes;
    protected $datetimeClass = DateTime::class;

    public function __construct(
        protected float $jdn = self::JDN_EPOCH_TIME
    ){}

    public function __get($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function __set($name, $value)
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
     * @param (callable():int|float)|int|float $offset khi sử dụng múi giờ KHÁC UTC+0, cần xác định phần bù (tính bằng 
     *                                                 giây) để kết quả trả về  chính xác. Chẳng hạn, Việt Nam sử dụng 
     *                                                 múi giờ GMT+7 vào thời điểm 2022 thì phần bù là 25200 giây, công 
     *                                                 thức cơ bản để tính phần bù này là: offset = 7 x 3600.
     * @return int|float
     */
    public function toTimestamp(int|float|callable $offset = 0)
    {
        if (!$this->unix) {
            $this->unix = ($this->getJdn() - self::JDN_EPOCH_TIME) * 86400;
        }

        return $this->unix - $offset;
    }

    /**
     * Chuyển đổi ngày Julius về đối tượng triển khai DateTimeInterface
     *
     * @param DateTimeZone|string|null $timezone
     * @return DateTimeInterface
     */
    public function toDateTime(string|null|DateTimeZone $timezone = null)
    {
        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }

        if (!$this->datetime) {
            $date = new $this->datetimeClass('now', $timezone);
            $date->setTimestamp($this->toTimestamp());

            $this->datetime = $date->format('c');
        }

        return (isset($date)) ? $date : new $this->datetimeClass($this->datetime, $timezone);
    }
}