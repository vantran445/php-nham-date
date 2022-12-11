<?php namespace Vantran\PhpNhamDate\Tests\Adapters\Factories;

use DateTime;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\Factories\JulianAdapter;
use Vantran\PhpNhamDate\Adapters\Factories\SunLongitudeAdapter;
use Vantran\PhpNhamDate\Adapters\SunLongitude\BaseSunlongitudeAdapter;
use Vantran\PhpNhamDate\Adapters\SunLongitude\DateTimePrimitiveToSunLongitude;
use Vantran\PhpNhamDate\Adapters\SunLongitude\DateTimeToSunLongitude;
use Vantran\PhpNhamDate\Adapters\SunLongitude\TimeStampToSunLongitude;

class SunLongitudeAdapterFactoryTest extends TestCase
{
    /**
     * Kiểm tra lớp tạo các bộ chuyển đổi Kinh độ mặt trời từ các loại dữ liệu
     * khác nhau.
     * 
     * @covers SunLongitudeAdapter
     * @return void
     */
    public function testMakingAdapters()
    {
        $datetime = new DateTime('2022-12-11T00:00:00+0700');
        $jdn = JulianAdapter::make($datetime)->getJdn();

        $baseAdapter = SunLongitudeAdapter::make($jdn, $datetime->getOffset(), SunLongitudeAdapter::BASE_ADAPTER);
        $datetimeAdapter = SunLongitudeAdapter::make($datetime);
        $timestampAdapter = SunLongitudeAdapter::make($datetime->getTimestamp(), $datetime->getOffset());
        $datetimePrimitiveAdapter = SunLongitudeAdapter::make(
            25200,
            2022,
            12,
            11,
            0,
            0,
            0
        );

        $this->assertNotInstanceOf(TimeStampToSunLongitude::class, $baseAdapter);
        $this->assertInstanceOf(BaseSunlongitudeAdapter::class, $baseAdapter);
        $this->assertInstanceOf(DateTimeToSunLongitude::class, $datetimeAdapter);
        $this->assertInstanceOf(TimeStampToSunLongitude::class, $timestampAdapter);
        $this->assertInstanceOf(DateTimePrimitiveToSunLongitude::class, $datetimePrimitiveAdapter);

        // Test Ouput
        $sl = $baseAdapter->getDegree();

        $this->assertEquals($sl, $datetimeAdapter->getDegree());
        $this->assertEquals($sl, $timestampAdapter->getDegree());
        $this->assertEquals($sl, $datetimePrimitiveAdapter->getDegree());
    }
}