<?php namespace Vantran\PhpNhamDate\Adapters\Interfaces;

use DateTime;
use Vantran\PhpNhamDate\Adapters\TimestampAccessableInterface;

interface DateTimeAccessable extends TimestampAccessableInterface
{
    /**
     * Trả về đối tượng DateTime theo thiết lập giờ địa phương
     *
     * @return DateTime
     */
    public function getDateTime(): DateTime;
}