<?php namespace Vantran\PhpNhamDate\Tests\Adapters\Julian;

use DateTime;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\Julian\DateTimeToJulian;
use Vantran\PhpNhamDate\Adapters\Julian\TimeStampToJulian;

class TimeStampToJulianTest extends TestCase
{
    /**
     * Kiểm tra chuyển đổi Unix thành Julian
     *
     * @covers TimeStampToJulian
     * @return void
     */
    public function testCreatingFromTimeStamp()
    {
        $date = new DateTime('now');

        for ($i = 0; $i < 10; $i ++) {
            $offset = $date->getOffset();
            $jdAdapter = new TimeStampToJulian($date->getTimestamp(), $offset);
            $jdAdapterCom = new DateTimeToJulian($date);

            $this->assertEquals($jdAdapterCom->getJdn(), $jdAdapter->getJdn());

            $date->modify('- 1 year');
        }
    }
}