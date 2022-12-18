<?php namespace Vantran\PhpNhamDate\Adapters;

use DateTimeZone;

abstract class BaseAdapter
{
    /**
     * Mặc định hỗ trợ người dùng tại Việt Nam
     * 
     */
    const DEFAULT_TIME_ZONE = 'Asia/Ho_Chi_Minh';

    /**
     * Phần bù múi giờ địa phương mặc định
     */
    const DEFAULT_OFFSET = 25200;
    
    /**
     * Múi giờ địa phương
     *
     * @var DateTimeZone
     */
    private static $timezone;

    /**
     * Phần bù chênh lệnh giữa giờ địa phương và UTC, tính bằng giây
     *
     * @var int
     */
    private static $offset = self::DEFAULT_OFFSET;

    /**
     * Trả về đối tượng DateTimeZone
     *
     * @return DateTimeZone
     */
    public final static function getTimeZone(): DateTimeZone
    {
        if (!self::$timezone) {
            self::setTimeZone();
        }

        return self::$timezone;
    }

    /**
     * Đặt múi giờ địa phương tùy chỉnh
     *
     * @param string|DateTimeZone $timezone chuỗi timezone hợp lệ hoặc đối tượng 
     * @link https://www.php.net/manual/en/timezones.php
     * @return void
     */
    public final static function setTimeZone(string|DateTimeZone $timezone = self::DEFAULT_TIME_ZONE): void
    {
        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }

        self::$timezone = $timezone;
    }

    /**
     * Đặt phần bù chênh lệnh với UTC, tính bằng giây
     *
     * @param integer $offset
     * @return void
     */
    public final static function setOffset(int $offset): void
    {
        self::$offset = $offset;
    }

    /**
     * Lấy phần bù chênh lệch thời gian với UTC, tính bằng giây.
     *
     * @return integer
     */
    public final static function getOffset(): int
    {
        return self::$offset;
    }

    public final static function resetDefaultTimeZone(): void
    {
        self::setTimeZone();
        self::$offset = self::DEFAULT_OFFSET;
    }
}