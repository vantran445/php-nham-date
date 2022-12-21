<?php namespace Vantran\PhpNhamDate\Adapters\Traits;

use Exception;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;

trait ToTimestamp
{
    /**
     * @inheritDoc
     *
     * @return integer|float
     */
    public function getTimestamp(): int|float
    {
        if (!$this->timestamp) {
            if (!$this->jdn) {
                throw new Exception("Error. The object must has 'timestamp' or 'jdn' attributes.");
            }

            $this->timestamp = JulianAdapter::setJdn($this->jdn)->getTimestamp();
        }

        return $this->timestamp;
    }
}