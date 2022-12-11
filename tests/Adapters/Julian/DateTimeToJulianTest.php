<?php namespace Vantran\PhpNhamDate\Tests\Adapters\Julian;

use DateTime;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\Julian\DateTimeToJulian;

class DateTimeToJulianTest extends TestCase
{
    /**
     * Kiểm tra khởi tạo
     *
     * @covers DateTimeToJulian
     * @return void
     */
    public function testInit()
    {
        $datetime = new DateTime('2022-10-10T10:10:00+0000');
        $jdAdapter = new DateTimeToJulian($datetime);
        $jdn = round($jdAdapter->getJdn(), 5);
        
        $this->assertEquals(2459862.92361, $jdn);
        $this->assertLessThanOrEqual(0.00001, abs($jdAdapter->toTimestamp() - $datetime->getTimestamp()));
    }
}