<?php namespace Vantran\PhpNhamDate;

use Exception;
use Vantran\PhpNhamDate\Adapters\Interfaces\JulianAccessable;
use Vantran\PhpNhamDate\Adapters\Interfaces\TimestampAccessable;
use Vantran\PhpNhamDate\Adapters\Traits\ToJulian;

/**
 * Lưu trữ các thuộc tính âm lịch
 * 
 * @author Văn Trần <caovan.info@gmail.com>
 * @package Vantran\PhpNhamDate
 */
class LunarDateTimeRepository implements TimestampAccessable, JulianAccessable
{
    use ToJulian;
    
    /**
     * Ngày âm lịch
     * @var int
     */
    protected $day = 24;

    /**
     * Tháng âm lịch
     * @var int
     */
    protected $month = 11;

    /**
     * Năm âm lịch
     * @var int
     */
    protected $year = 1969;

    /**
     * Giờ (âm/dương lịch)
     * @var mixed
     */
    protected $hour = 7;

    /**
     * Phút (âm/dương lịch)
     * @var mixed
     */
    protected $minute = 0;

    /**
     * Giây (âm/dương lịch)
     * @var mixed
     */
    protected $second = 0;

    /**
     * Số ngày Julian tương ứng với ngày tháng
     * @var int|float
     */
    protected $jdn = 2440588;

    /**
     * Vị trí tháng nhuận âm lịch
     * @var int
     */
    protected $leapMonth = null;

    /**
     * Timestamp tương ứng với điểm sóc của tháng nhuận âm lịch
     * @var mixed
     */
    protected $leapMonthTimestamp = null;

    /**
     * Tem thời gian unix tương ứng với thời gian âm lịch
     * @var int|float
     */
    protected $timestamp = 0;

    /**
     * Tên múi giờ được sử dụng
     * @var string
     */
    protected $timezone = 'Asia/Ho_Chi_Minh';

    /**
     * Phần bù chênh lệch giờ địa phương với UTC, tính bằng giây
     * @var int
     */
    protected $offset = 25200;

    /**
     * Tạo kho lưu trữ dữ liệu âm lịch
     * 
     * @param array $props mảng các thuộc tính
     * @return void 
     */
    public function __construct(array $props = [])
    {
        if (!empty($props)) {
            foreach ($props as $prop => $value) {
                $prop = str_replace('_', '', ucwords($prop, '_'));
                $propName = lcfirst($prop);
                $propSetter = 'set' . $prop;

                if (property_exists($this, $propName)) {
                    call_user_func([$this, $propSetter], $value);
                }
            }
        }
    }

    /** @return int|float  */
    public function getTimestamp(): int|float 
    {
        return $this->timestamp;
    }

    /**
     * Trả về ngày trong tháng từ 1 đến 31
     * 
     * @return int 
     */
    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * Trả về số tháng với định dạng số, từ 1 - 12
     * 
     * @return int 
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * Trả về năm với định dạng 4 chữ số - 1999, 2010...
     * 
     * @return int 
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Trả về chuỗi tên múi giờ
     * 
     * @return string 
     */
    public function getTimeZone(): string
    {
        return $this->timezone;
    }

    /**
     * Trả về phần bù chênh lệch giờ địa phương so với UTC, tính bằng giây
     * 
     * @return int 
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * 
     * @param int $day 
     * @return void 
     * @throws Exception 
     */
    public function setDay(int $day): void
    {
        if ($day < 1 || $day > 30) {
            throw new Exception("Error. The day must be between 1 and 30.");
        }

        $this->day = $day;
    }

    /**
     * Đặt số tháng
     * 
     * @param int $month Tháng hợp lệ từ 1 đến 12
     * @return void 
     * @throws Exception 
     */
    public function setMonth(int $month)
    {
        if ($month < 1 || $month > 12) {
            throw new Exception("Error. The month must be between 1 and 12.");
        }

        $this->month = $month;
    }
    
    /**
     * Đặt số năm
     * @param int $year Năm gồm 4 chữ số, hỗ trợ từ 1000 đến 9999
     * @return void 
     * @throws Exception 
     */
    public function setYear(int $year): void
    {
        if ($year <= 999 || $year > 9999) {
            throw new Exception("Error. The year supported between 1000 and 9999.");
        }

        $this->year = $year;
    }

    /**
     * Đặt tem thời gian Unix
     * 
     * @param int|float $timestamp 
     * @return void 
     */
    public function setTimestamp(int|float $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Đặt số ngày Julian
     * 
     * @param int|float $jdn 
     * @return void 
     */
    public function setJdn(int|float $jdn): void
    {
        $this->jdn = $jdn;
    }

    /**
     * Đặt múi giờ
     * 
     * @param string $timezone 
     * @return void 
     */
    public function setTimeZone(string $timezone): void
    {
        $this->timezone = $timezone;
    }

    /**
     * Đặt phần bù chênh lệch giờ địa phương so với UTC
     * 
     * @param int $offset phần bù tính bằng giây
     * @return void 
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * Trả về số tháng nhuận. Trả về 0 nếu không phải tháng nhuận. Trả về từ 2 - 10 tương ứng với vị trí tháng nhuận
     * 
     * @return null|int 
     */
    public function getLeapMonth(): ?int
    {
        return $this->leapMonth;
    }

    /**
     * Đặt giá trị tháng nhuận
     * 
     * @param int $leapMonth 
     * @return void 
     * @throws Exception 
     */
    public function setLeapMonth(int $leapMonth): void
    {
        if ($leapMonth < 0 || $leapMonth === 1 || $leapMonth > 10) {
            throw new Exception("Error. Leap month must be between 0 (without 1) and 10.");
        }

        $this->leapMonth = $leapMonth;
    }
}