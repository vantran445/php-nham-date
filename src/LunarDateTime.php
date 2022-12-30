<?php namespace Vantran\PhpNhamDate;

use DateTime;
use Vantran\PhpNhamDate\Adapters\LunarDateTimeAdapter;

class LunarDateTime extends LunarDateTimeAdapter
{
    const DEFAULT_FORMAT = 'Y-m-d';

    public static function now() {}

    public function format($formater = ''): string
    {
        return '';
    }

    public function addDay(int $days): LunarDateTime
    {

    }

    public function addMonth(int $month): LunarDateTime
    {

    }
}
