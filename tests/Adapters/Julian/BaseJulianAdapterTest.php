<?php namespace Vantran\PhpNhamDate\Tests\Adapters\Julian;

use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\Julian\BaseJulianAdapter;

class BaseJulianAdapterTest extends TestCase
{
    /**
     * Kiểm tra chuyển đổi ngày Julius về Unix
     *
     * @covers BaseJulianAdapter
     * @return void
     */
    public function testtoTimestamp()
    {
        // Epoch start
        $baseTimeStamp = 0;
        $jdn = new BaseJulianAdapter();

        $this->assertEquals($baseTimeStamp, $jdn->toTimestamp());

        // Before epoch
        $d = 10;
        $jdn = new BaseJulianAdapter(BaseJulianAdapter::JDN_EPOCH_TIME - $d);

        $this->assertEquals(($baseTimeStamp - $d * 86400), $jdn->toTimestamp());

        //After Epoch
        $d = 20;
        $jdn = new BaseJulianAdapter(BaseJulianAdapter::JDN_EPOCH_TIME + $d);

        $this->assertEquals(($baseTimeStamp + $d * 86400), $jdn->toTimestamp());
    }

    /**
     * Kiểm tra chuyển đổi ngày đối tượng DateTime
     *
     * @covers BaseJulianAdapter
     * @return void
     */
    public function testToDateTimeObject()
    {
        $datetimeFormat = '1970-01-01T00:00:00+00:00';
        $jdAdapter = new BaseJulianAdapter();
        $datetime = $jdAdapter->toDateTime('UTC');

        $this->assertEquals($datetimeFormat, $datetime->format('c'));
    }
}