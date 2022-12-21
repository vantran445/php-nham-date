<?php namespace Vantran\PhpNhamDate\Adapters\Traits;

use DateTime;
use Vantran\PhpNhamDate\Adapters\BaseAdapter;

trait ToDateTime
{
    /**
     * @inheritDoc
     *
     * @return DateTime
     */
    public function getDateTime(): DateTime
    {
        $datetime = new DateTime('now', BaseAdapter::getTimeZone());
        $datetime->setTimestamp($this->getTimestamp());

        return $datetime;
    }
}