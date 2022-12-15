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
        // Epoch start
        $baseTimeStamp = 0;
        $jdn = new JulianAdapter();

        $this->assertEquals($baseTimeStamp, $jdn->toTimestamp());

        // Before epoch
        $d = 10;
        $jdn = new JulianAdapter(JulianAdapter::JDN_EPOCH_TIME - $d);

        $this->assertEquals(($baseTimeStamp - $d * 86400), $jdn->toTimestamp());

        //After Epoch
        $d = 20;
        $jdn = new JulianAdapter(JulianAdapter::JDN_EPOCH_TIME + $d);

        $this->assertEquals(($baseTimeStamp + $d * 86400), $jdn->toTimestamp());
    }

    /**
     * Kiểm tra chuyển đổi ngày đối tượng DateTime
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testToDateTime()
    {
        // Local Test
        $datetimeFormat = '1970-01-01T00:00:00+07:00';
        $jdAdapter = new JulianAdapter();
        $datetime = $jdAdapter->toDateTime('+0700');

        $this->assertEquals($datetimeFormat, $datetime->format('c'));

        // UTC test
        $format = '2022-10-30T16:04:05+00:00';
        $jdAdapter = JulianAdapter::fromDateTimePrimitive(
            2022,
            10,
            30,
            16,
            04,
            05
        );

        $this->assertEquals($format, $jdAdapter->toDateTime()->format('c'));
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
            $offset = $date->getOffset();
            $jdAdapter = JulianAdapter::fromTimestamp($date->getTimestamp(), $offset);
            $jdAdapterCom = JulianAdapter::fromDateTime($date);

            $this->assertEquals(round($jdAdapterCom->getJdn(), 6), round($jdAdapter->getJdn(), 6));

            $date->modify('- 1 year');
        }
    }
}