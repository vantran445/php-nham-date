<?php namespace Vantran\PhpNhamDate\Adapters;

use DateTimeInterface;
use Exception;
use Vantran\PhpNhamDate\Adapters\Interfaces\DateTimeAccessable;
use Vantran\PhpNhamDate\Adapters\Interfaces\JulianAccessable;
use Vantran\PhpNhamDate\Adapters\Traits\ToDateTime;
use Vantran\PhpNhamDate\Adapters\Traits\ToJulian;

/**
 * Lớp tìm kiếm thời điểm Trăng mới - còn gọi là điểm Sóc, tức ngày mùng 01 đầu
 * tháng Âm lịch.
 * 
 * @author Văn Trần <caovan.info@gmail.com>
 */
class NewMoonAdapter extends BaseAdapter implements JulianAccessable, DateTimeAccessable
{
    use ToJulian;
    use ToDateTime;

    /**
     * Bộ chuyển đổi các pha Mặt trăng
     *
     * @var MoonPhaseAdapter
     */
    protected $moonPhase;

    /**
     * Tạo đối tượng mới
     *
     * @param integer|float $timestamp
     */
    public function __construct(int|float $timestamp) 
    {
        $moonPhase = new MoonPhaseAdapter($timestamp);
        $this->timestamp = $moonPhase->getPhaseNewMoon();
    }

    /**
     * Chuyển đổi từ triển khai DateTimeInterface
     *
     * @param DateTimeInterface|null $datetime
     * @return NewMoonAdapter
     */
    public static function fromDateTime(?DateTimeInterface $datetime = null): NewMoonAdapter
    {
        $timestamp = (!$datetime)? time() : $datetime->getTimestamp();
        return new self($timestamp);
    }

    /**
     * Phương thức tĩnh tạo nhanh bộ chuyển đổi
     *
     * @param integer|float|callable $timestamp
     * @return NewMoonAdapter
     */
    public static function create(int|float|callable $timestamp): NewMoonAdapter
    {
        if (is_callable($timestamp)) {
            $timestamp = $timestamp();
        }

        return new self($timestamp);
    }

    /**
     * Trả về đối tượng mới từ tem thời gian Unix
     *
     * @param integer|float $timestamp
     * @return NewMoonAdapter
     */
    public static function setTimestamp(int|float $timestamp): NewMoonAdapter
    {
        return new self($timestamp);
    }


    /**
     * Chuyển đổi từ số ngày Julian
     *
     * @param integer|float $jdn
     * @param integer $offset       phần bù giờ địa phương, tính bằng giây 
     * @return NewMoonAdapter
     */
    public static function fromJdn(int|float $jdn): NewMoonAdapter
    {
        $timestamp = JulianAdapter::setJdn($jdn)->getTimestamp();
        return new self($timestamp);
    }

    /**
     * Tìm điểm sóc từ nhóm thời gian nguyên thủy
     *
     * @param integer $Y        năm gồm 4 chữ số
     * @param integer $m        tháng từ 1 đến 12
     * @param integer $d        ngày từ 1 đến 31
     * @param integer $H        giờ từ 0 đến 23
     * @param integer $i        phút từ 0 đến 59
     * @param integer $s        giây từ 0 đến 59
     * @return NewMoonAdapter
     */
    public static function fromDateTimePrimitive(
        int $Y,
        int $m,
        int $d,
        int $H = 0,
        int $i = 0,
        int $s = 0
    ): NewMoonAdapter
    {
        $jdAdapter = JulianAdapter::fromDateTimePrimitive($Y, $m, $d, $H, $i, $s);
        $timestamp = $jdAdapter->getTimestamp();

        return new self($timestamp);
    }

    /**
     * Trả về bộ chuyển đổi điểm Sóc tiếp theo (chưa đến)
     *
     * @param integer $position bao nhiêu điểm tiếp theo cần tính, mặc định 1 tức điểm kế tiếp
     * @return NewMoonAdapter
     */
    public function getNext(int $position = 1): NewMoonAdapter
    {
        if ($position === 0) {
            throw new Exception('Error. You are at the current new moon.');
        }

        $timestamp = $this->getTimestamp() + $position * 30 * 86400;
        return new self($timestamp);
    }

    /**
     * Trả về bộ chuyển đổi điểm Sóc trước đó (đã qua)
     *
     * @param integer $position Bao nhiêu điểm trước đó cần tính, mặc định 1 tức điểm kế trước
     * @return NewMoonAdapter
     */
    public function getPrevious(int $position = 1): NewMoonAdapter
    {
        $position = -1 * abs($position);
        return $this->getNext($position);
    }

    /**
     * @inheritDoc
     *
     * @return integer|float
     */
    public function getTimestamp(): int|float
    {
        return $this->timestamp;
    }
}