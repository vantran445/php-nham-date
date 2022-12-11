<?php namespace Vantran\PhpNhamDate\Adapters\Factories;

use Vantran\PhpNhamDate\Adapters\SunLongitude\BaseSunlongitudeAdapter;

/**
 * @method static \Vantran\PhpNhamDate\Adapters\SunLongitude\TimeStampToSunLongitude make(int|float $timestamp, int $offset)
 * @method static \Vantran\PhpNhamDate\Adapters\SunLongitude\BaseSunlongitudeAdapter make(int|float $jdn, int $offset, int $adapter = 1)
 * @method static \Vantran\PhpNhamDate\Adapters\SunLongitude\DateTimePrimitiveToSunLongitude make(int $offset, int $year, int $month, int $day, int $hour = 0, int $minute = 0, int $second = 0)
 * @method static \Vantran\PhpNhamDate\Adapters\SunLongitude\DateTimeToSunLongitude make(DateTimeInterFace $datetime)
 */
class SunLongitudeAdapter
{
    const BASE_ADAPTER = 1;

    protected static $adapters = [
        \Vantran\PhpNhamDate\Adapters\SunLongitude\BaseSunlongitudeAdapter::class,
        \Vantran\PhpNhamDate\Adapters\SunLongitude\DateTimePrimitiveToSunLongitude::class,
        \Vantran\PhpNhamDate\Adapters\SunLongitude\DateTimeToSunLongitude::class,
        \Vantran\PhpNhamDate\Adapters\SunLongitude\TimeStampToSunLongitude::class,
    ];

    /**
     * Chuyển đổi nhanh nhiều loại giá trị khác nhau về kinh độ mặt trời
     *
     * @param mixed ...$args
     * @return BaseSunlongitudeAdapter
     */
    public static function make(mixed ...$args)
    {
        $total = count($args);

        switch($total) {
            case 0:
            case 1:
                $class = self::$adapters[2];
                break;

            case 2:
            case 3:
                $class = self::$adapters[3];

                if (isset($args[2]) && $args[2] === self::BASE_ADAPTER) {
                    $class = self::$adapters[0];
                }

                break;

            default:
                $class = self::$adapters[1];
        }

        return new $class(...$args);
    }
}