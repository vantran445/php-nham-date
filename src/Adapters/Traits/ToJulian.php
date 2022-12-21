<?php namespace Vantran\PhpNhamDate\Adapters\Traits;

use Exception;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;

trait ToJulian
{
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
}