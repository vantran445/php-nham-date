<?php namespace Vantran\PhpNhamDate\Adapters;

use Vantran\PhpNhamDate\Adapters\Interfaces\DateTimeAccessable;
use Vantran\PhpNhamDate\Adapters\Traits\ToDateTime;
use Vantran\PhpNhamDate\Adapters\Traits\ToJulian;
use Vantran\PhpNhamDate\Adapters\Traits\ToTimestamp;

class LunarAdapter extends BaseAdapter implements JulianAccessableInterface, DateTimeAccessable
{
    use ToJulian;
    use ToDateTime;
    use ToTimestamp;

    protected $attributes = [];

    public function __construct(
        protected int|float $timestamp
    ) {
        
    }

    public function init(): void
    {
        $jdAdapter = JulianAdapter::fromTimestamp($this->timestamp);

    }
}