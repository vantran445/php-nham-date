<?php namespace Vantran\PhpNhamDate\Adapters\Interfaces;

use DateTime;

interface DateTimeAccessable extends TimestampAccessable
{
    /**
     * Trả về đối tượng DateTime theo thiết lập giờ địa phương
     *
     * @return DateTime
     */
    public function getDateTime(): DateTime;
}