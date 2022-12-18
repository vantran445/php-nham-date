<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Solaris\MoonPhase;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;
use Vantran\PhpNhamDate\Adapters\NewMoonAdapter;

class NewMoonAdapterTest extends TestCase
{
    /**
     * Phần bù múi giờ GMT+7 so với UTC
     *
     * @var integer
     */
    protected $offset = 25200;

    /**
     * Điểm Sóc của 13 tháng trong năm 2020 tại Việt Nam (GMT+7)
     *
     * @return array
     */
    public function additionNewMoon2020Provider(): array
    {
        return [
            ['solar' => '2020-01-25', 'lunar_month' => '1'],
            ['solar' => '2020-02-23', 'lunar_month' => '2'],
            ['solar' => '2020-03-24', 'lunar_month' => '3'],
            ['solar' => '2020-04-23', 'lunar_month' => '4'],
            ['solar' => '2020-05-23', 'lunar_month' => '4+'],
            ['solar' => '2020-06-21', 'lunar_month' => '5'],
            ['solar' => '2020-07-21', 'lunar_month' => '6'],
            ['solar' => '2020-08-19', 'lunar_month' => '7'],
            ['solar' => '2020-09-17', 'lunar_month' => '8'],
            ['solar' => '2020-10-17', 'lunar_month' => '9'],
            ['solar' => '2020-11-15', 'lunar_month' => '10'],
            ['solar' => '2020-12-14', 'lunar_month' => '11'],
            ['solar' => '2021-01-13', 'lunar_month' => '12'],
        ];
    }

    /**
     * Kiểm tra kết quả đầu ra
     *
     * @covers NewMoonAdapter
     * @return void
     */
    public function testOuput()
    {
        $date = new DateTime();
        $jdn = JulianAdapter::fromDateTime($date)->getJdn();

        $nmDateTimeAdapter = NewMoonAdapter::fromDateTime($date);
        $nmJdnAdapter = NewMoonAdapter::fromJdn($jdn);
        $nmDateTimePrimitiveAdapter = NewMoonAdapter::fromDateTimePrimitive(
            $date->getOffset(),
            $date->format('Y'),
            $date->format('m'),
            $date->format('d'),
            $date->format('H'),
            $date->format('i'),
            $date->format('s')
        );
        $expected = (new MoonPhase($date))->getPhaseNewMoon();

        $this->assertEquals($expected, $nmDateTimeAdapter->getTimestamp());
        $this->assertEquals($expected, $nmDateTimePrimitiveAdapter->getTimestamp());
        $this->assertEquals($expected, $nmJdnAdapter->getTimestamp());
    }

    /**
     * Kiểm tra tính điểm Sóc trong năm 2022 theo Âm lịch Việt Nam
     *
     * @dataProvider additionNewMoon2020Provider
     * @covers NewMoonAdapter
     * @param string $date
     * @param string $month
     * @return void
     */
    public function testCorrectedNewMoon(string $date, string $month)
    {
        $timezone = new DateTimeZone('+0700');
        $datetime = new DateTime($date, $timezone);
        $datetime->modify('+15 day');

        $newMoonAdapter = NewMoonAdapter::fromDateTime($datetime);
        $newMoonDate = new DateTime('now', $timezone);
        $newMoonDate->setTimestamp($newMoonAdapter->getTimestamp());

        $this->assertEquals($date, $newMoonDate->format('Y-m-d'));        
    }

    /**
     * Kiểm tra lấy các điểm Sóc tiếp theo (chưa đến) trong năm 2020
     *
     * @covers NewMoonAdapter
     * @return void
     */
    public function testToNextNewMoon()
    {
        $data = $this->additionNewMoon2020Provider();
        $newMoon = NewMoonAdapter::fromDateTimePrimitive($this->offset, 2020, 1, 5);
        $counter = 1;

        foreach ($data as $nm) {
            $dateExpected = $nm['solar'];
            $nextNewMoon = $newMoon->getNext($counter);
            
            $datetime = new DateTime('', new DateTimeZone('+0700'));
            $datetime->setTimestamp($nextNewMoon->getTimestamp());

            $this->assertEquals($dateExpected, $datetime->format('Y-m-d'));

            $counter ++;
        }
    }

    /**
     * Kiểm tra tìm về các điểm Sóc trước đó (đã qua)
     *
     * @covers NewMoonAdapter
     * @return void
     */
    public function testToPreviousNewMoon()
    {
        $data = $this->additionNewMoon2020Provider();
        $newMoon = NewMoonAdapter::fromDateTimePrimitive($this->offset, 2021, 3, 25);
        $counter = 13;

        foreach ($data as $nm) {
            $dateExpected = $nm['solar'];
            $nextNewMoon = $newMoon->getPrevious($counter);
            
            $datetime = new DateTime('', new DateTimeZone('+0700'));
            $datetime->setTimestamp($nextNewMoon->getTimestamp());

            $this->assertEquals($dateExpected, $datetime->format('Y-m-d'), $counter);

            $counter --;
        }
    }
}