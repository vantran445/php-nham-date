<?php namespace Vantran\PhpNhamDate\Tests\Adapters\Factories;

use DateTime;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\Factories\JulianAdapter;
use Vantran\PhpNhamDate\Adapters\Julian\BaseJulianAdapter;
use Vantran\PhpNhamDate\Adapters\Julian\DateTimePrimitiveToJulian;
use Vantran\PhpNhamDate\Adapters\Julian\DateTimeToJulian;
use Vantran\PhpNhamDate\Adapters\Julian\TimeStampToJulian;

class JulianAdapterFactoryTest extends TestCase
{
    /**
     * Kiểm tra khởi tạo các bộ chuyển đổi Julian thông qua Factory
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testMakingAdapterTypes() {
        $jd = 2459924.5; // From 2022-12-11T00:00:00+0000
        $datetime = new DateTime('2022-12-11T00:00:00+0000');

        $baseAdapter = JulianAdapter::make($jd);
        $datetimePrimitiveAdapter = JulianAdapter::make(2022, 12, 11);
        $datetimeAdapter = JulianAdapter::make($datetime);
        $timestampAdapter = JulianAdapter::make($datetime->getTimestamp(), $datetime->getOffset());
        
        $this->assertInstanceOf(BaseJulianAdapter::class, $baseAdapter);
        $this->assertInstanceOf(DateTimePrimitiveToJulian::class, $datetimePrimitiveAdapter);
        $this->assertInstanceOf(DateTimeToJulian::class, $datetimeAdapter);
        $this->assertInstanceOf(TimeStampToJulian::class, $timestampAdapter);

        $this->assertEquals($baseAdapter->getJdn(), $datetimePrimitiveAdapter->getJdn());
        $this->assertEquals($baseAdapter->getJdn(), $datetimeAdapter->getJdn());
        $this->assertEquals($baseAdapter->getJdn(), $timestampAdapter->getJdn());
        
        // Now
        $this->assertInstanceOf(DateTimeToJulian::class, JulianAdapter::make());
    }
}