<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;

class JulianAdapterTest extends TestCase
{
    /**
     * Kiểm tra chuyển đổi ngày Julius về Unix
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testToTimestamp()
    {
        $jdn = fn($d) => JulianAdapter::JDN_EPOCH_TIME + $d;

        for ($i = 0; $i <= 10; $i ++) {
            $d = rand(-36.524, 36.524);

            $timstamp = $d * 86400;
            $jdAdapter = JulianAdapter::setJdn($jdn($d));

            $this->assertEquals($timstamp, $jdAdapter->toTimestamp());
        }
    }

    /**
     * Kiểm tra chuuyển đổi Julian từ triển khai DateTimeInterface
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testDateTimeToJulian()
    {
        $datetime = new DateTime('2022-10-10T10:10:00+0000');
        $jdAdapter = JulianAdapter::fromDateTime($datetime);
        $jdn = round($jdAdapter->getJdn(), 5);
        
        $this->assertEquals(2459863.42361, $jdn);
        $this->assertLessThanOrEqual(0.00001, abs($jdAdapter->toTimestamp() - $datetime->getTimestamp()));
    }

    /**
     * Kiểm tra chuyển đổi Unix thành Julian
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testTimestampToJulian()
    {
        $date = new DateTime('now');

        for ($i = 0; $i < 10; $i ++) {
            $jdAdapter = JulianAdapter::fromTimestamp($date->getTimestamp());
            $jdAdapterCom = JulianAdapter::fromDateTime($date);

            $this->assertEquals(round($jdAdapterCom->getJdn(), 6), round($jdAdapter->getJdn(), 6));

            $date->modify('- 1 year');
        }
    }
}