<?php namespace Vantran\PhpNhamDate\Adapters\Julian;

class DateTimePrimitiveToJulian extends BaseJulianAdapter
{
    public function __construct(
        protected int $Y,
        protected int $m,
        protected int $d,
        protected int $H = 0,
        protected int $i = 0,
        protected int $s = 0
    ) {
        parent::__construct($this->_getJdn());
    }

    protected function _getJdn()
    {
        $jdn = gregoriantojd($this->m, $this->d, $this->Y) - 0.5;
        $jdn += ($this->H * 3600 + $this->i * 60 + $this->s) / 86400;

        return $jdn;
    }
}