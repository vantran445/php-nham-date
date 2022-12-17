<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
use PHPUnit\Framework\TestCase;
use Solaris\MoonPhase;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;
use Vantran\PhpNhamDate\Adapters\MoonPhaseAdapter;

class MoonPhaseAdapterTest extends TestCase
{
    /**
     * Kiểm tra lấy điểm Sóc từ các đầu vào khác nhau
     *
     * @covers MoonPhaseAdapter
     * @return void
     */
    public function testNewMoonFromMultipleInput()
    {
        $datetime = new DateTime('2022-12-20T20:30:40+0700');
        $offset = $datetime->getOffset();
        $jdn = JulianAdapter::fromDateTime($datetime)->getJdn();

        $baseAdapter = new MoonPhase($datetime);
        $fromDateAdapter = MoonPhaseAdapter::fromDateTime($datetime);
        $fromJdnAdapter = MoonPhaseAdapter::fromJdn($jdn, $offset);
        $fromDateTimePrimitiveAdapter = MoonPhaseAdapter::fromDateTimePrimitive(
            $offset,
            2022,
            12,
            20,
            20,
            30,
            40
        );

        $baseNewMoon = $baseAdapter->getPhaseNewMoon();

        $this->assertEquals($baseNewMoon, $fromDateAdapter->getPhaseNewMoon());
        $this->assertEquals($baseNewMoon, $fromJdnAdapter->getPhaseNewMoon());
        $this->assertEquals($baseNewMoon, $fromDateTimePrimitiveAdapter->getPhaseNewMoon());
    }
}