<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;

class JulianAdapterTest extends TestCase
{
    /**
     * Kiểm tra khởi tạo đối tượng
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testInit()
    {
        $jdn = new JulianAdapter();
        $this->assertEquals($jdn::JDN_EPOCH_TIME, $jdn->getJdn());
    }

    /**
     * Kiểm tra tạo bộ chuyển đổi mới từ đối tượng DateTime
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testCreateFromDateTime()
    {
        $datetime = new DateTime();
        $jdAdapter = JulianAdapter::fromDateTime($datetime);
        
        $expect = gregoriantojd(
            $datetime->format('n'),
            $datetime->format('j'),
            $datetime->format('Y')
        );

        $this->assertEquals($expect, $jdAdapter->getJdn(false));
    }

    /**
     * Kiểm tra tạo bộ chuyển đổi mới từ timestamp
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testCreateFromTimestamp()
    {
        $timestamp = time();
        $jdAdapter = JulianAdapter::fromTimestamp($timestamp);

        $this->assertEquals($timestamp, $jdAdapter->getTimestamp());
    }

    /**
     * Kiểm tra tạo bộ chuyển đổi từ nhóm thời gian nguyên thủy
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testCreateDateTimePrimitive()
    {
        // UTC
        $datetime = new DateTime('2020-10-20T00:00:00+0000');
        $jdUtc = JulianAdapter::fromDateTime($datetime);

        $datetime->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
        $jdLocal = JulianAdapter::fromDateTimePrimitive(
            $datetime->getOffset(),
            $datetime->format('Y'),
            $datetime->format('m'),
            $datetime->format('d'),
            $datetime->format('H'),
            $datetime->format('i'),
            $datetime->format('s'),
        );
        
        $this->assertEquals($jdUtc->getJdn(), $jdLocal->getJdn());
        $this->assertEquals($datetime->getTimestamp(), $jdLocal->getTimestamp());
    }
}