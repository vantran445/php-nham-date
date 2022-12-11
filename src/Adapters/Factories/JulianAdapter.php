<?php namespace Vantran\PhpNhamDate\Adapters\Factories;

use DateTimeInterface;
use Vantran\PhpNhamDate\Adapters\Julian\BaseJulianAdapter;

class JulianAdapter
{
    protected static $adapters =[
        'from_jd' => \Vantran\PhpNhamDate\Adapters\Julian\BaseJulianAdapter::class,
        'from_primmitive' => \Vantran\PhpNhamDate\Adapters\Julian\DateTimePrimitiveToJulian::class,
        'from_datetime'  => \Vantran\PhpNhamDate\Adapters\Julian\DateTimeToJulian::class,
        'from_unix' => \Vantran\PhpNhamDate\Adapters\Julian\TimeStampToJulian::class,
    ];

    /**
     * Tạo nhanh bộ chuyển đổi Julian các loại tham số đầu vào khác nhau.
     *
     * @param mixed ...$arguments để chuyển đổi chính xác, áp dụng các quy tắc như sau:
     *                              - Chuyển đổi từ Julian: JulianAdapter::make(float|int $jdn);
     *                              - Chuyển đổi từ DateTimeInterface: JulianAdapter::make(?DateTimeInterface $datetime);
     *                              - Chuyển đổi từ tem thời gian Unix: JulianAdapter::make(int|float $unix, int $offset);
     *                              - Chuyển đổi từ thời gian nguyên thủy: 
     *                                JulianAdapter::make(int $Y, int $m, int $d, int $H = 0, int $i = 0, int $s = 0);
     * @return BaseJulianAdapter
     */
    public static function make(...$arguments): BaseJulianAdapter
    {
        $total = count($arguments);

        switch ($total) {
            case 0:
                $class = self::$adapters['from_datetime'];
                $adapter =new $class();
                break;

            case 1:
                $arg = $arguments[0];

                if ($arg === null || $arg instanceof DateTimeInterface ) {
                    $class = self::$adapters['from_datetime'];
                }
                else {
                    $class = self::$adapters['from_jd'];
                }

                $adapter =new $class($arg);

                break;
            
            case 2:
                $class = self::$adapters['from_unix'];
                $adapter =new $class($arguments[0], $arguments[1]);

                break;
            
            default:
                $class = self::$adapters['from_primmitive'];

                for ($i = 3; $i <= 5; ++$i) {
                    if (!isset($arguments[$i])) {
                        $arguments[$i] = 0;
                    }
                }

                $adapter =new $class(
                    $arguments[0], // Years
                    $arguments[1], // Months
                    $arguments[2], // Days
                    $arguments[3], // Hours
                    $arguments[4], // Minutes
                    $arguments[5], // Seconds
                );
        }

        return $adapter;
    }
}