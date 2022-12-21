<?php namespace Vantran\PhpNhamDate\Adapters\Traits;

use Exception;
use stdClass;
use Vantran\PhpNhamDate\Adapters\Interfaces\JulianAccessable;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;

trait ToJulian
{
    /**
     * Chuyển đổi số ngày Julian và mốc thời gian ngày tháng năm
     *
     * @param integer|float $jdn
     * @return array
     */
    protected function toBaseDateTime(int|float $jdn): array
    {
        $jdnDays  = floor($jdn);
        $jdnDecimalDays   = $jdn - $jdnDays;

        if ($jdnDays > 2299160) { // After 5/10/1582, Gregorian calendar
            $a = $jdnDays + 32044;
            $b = floor((4 * $a + 3) / 146097);
            $c = $a - floor(($b * 146097) / 4);
        } 
        else {
            $b = 0;
            $c = $jdnDays + 32082;
        }
        
        $d = floor((4 * $c + 3) / 1461);
        $e = $c - floor((1461 * $d) / 4);
        $m = floor((5 * $e + 2) / 153);
        $seconds = floor($jdnDecimalDays * 3600 * 24) + 1;

        return [
            'day'       => (int)($e - floor((153 * $m + 2) / 5) + 1),
            'month'     => (int)($m + 3 - 12 * floor($m / 10)),
            'year'      => (int)($b * 100 + $d - 4800 + floor($m / 10)),
            'hour'      => (int)(($seconds / 3600) % 24),
            'minute'    => (int)(($seconds / 60) % 60),
            'second'    => (int)($seconds % 60)
        ];
    }

    /**
     * Trả về số ngày Julian địa phương từ ngày Julian UTC
     *
     * @return float
     */
    protected function getJdnOffset($jdn): float
    {
        return $jdn + self::getOffset() / 86400;
    }

    /**
     * @inheritDoc
     * 
     * @return float
     */
    public function getJdn(bool $withDecimal = true): float
    {
        if (!$this->jdn) {
            if (!$this->timestamp) {
                throw new Exception("Error. The Object dose not contain 'jdn' or 'timestamp' attributes.");
            }

            $this->jdn = JulianAdapter::fromTimestamp($this->timestamp)->getJdn();
        }
        return ($withDecimal)? $this->jdn : floor($this->jdn);
    }

    /**
     * @inheritDoc
     *
     * @param boolean $withDecimal
     * @return float
     */
    public function getLocalJdn(bool $withDecimal = true): float
    {
        $jdn = $this->getJdnOffset($this->getJdn());
        return $withDecimal ? $jdn : floor($jdn);
    }

    /**
     * @inheritDoc
     *
     * @param string $output
     * @return array|stdClass
     */
    public function getBaseLocalDateTime(string $output = JulianAccessable::OBJECT_DATE_TIME): array|stdClass
    {
        $datetime = $this->toBaseDateTime($this->getLocalJdn());
        return ($output === JulianAdapter::ARRAY_DATE_TIME) ? $datetime : (object)$datetime;
    }
}