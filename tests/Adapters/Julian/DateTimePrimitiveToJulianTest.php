<?php namespace Vantran\PhpNhamDate\Tests\Adapters\Julian;

use DateTime;
use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\Julian\DateTimePrimitiveToJulian as JulianAdapter;

class DateTimePrimitiveToJulianTest extends TestCase
{
    /**
     * Kiểm tra chuổi đổi thời điểm 1/1/1970
     *
     * @covers DateTimePrimitiveToJulian
     * @return void
     */
    public function testJdnAtEpochStarting()
    {
        $jdApdater = new JulianAdapter(1970, 1, 1, 0, 0, 0);

        $this->assertEquals($jdApdater::JDN_EPOCH_TIME, $jdApdater->getJdn());
    }

    /**
     * Kiểm tra 1 số thời điểm
     *
     * @covers DateTimePrimitiveToJulian
     * @return void
     */
    public function testSomseYears()
    {
        $Y = 1969;
        $m = 5;
        $d = 14;

        for ($i = 1; $i <= 10; $i ++) {
            // Befor Epoch starting point
            $phpDefault = gregoriantojd($m, $d, ($Y - $i));
            $jdApdater = new JulianAdapter(($Y - $i), $m, $d, 12, 0, 0, 0);

            $this->assertEquals($phpDefault, $jdApdater->getJdn(), $Y);

            // After Epoch starting point
            $phpDefault = gregoriantojd($m, $d, ($Y + $i + 1));
            $jdApdater = new JulianAdapter(($Y + $i + 1), $m, $d, 12, 0, 0, 0);

            $this->assertEquals($phpDefault, $jdApdater->getJdn());
        }
    }

    /**
     * Kiểm tra chuyển đổi về tem thời gian UNIX
     *
     * @covers DateTimePrimitiveToJulian
     * @return void
     */
    public function testToTimestamp() {
        $date = new DateTime();

        for ($k = 0; $k < 20; $k ++) {
            $d = rand(1, 30);
            $m = rand(1, 12);
            $Y = rand(1958, 2100);
            $H = rand(0, 23);
            $i = rand(0, 59);

            $date->setDate($Y, $m, $d);
            $date->setTime($H, $i);
            $jdApdater = new JulianAdapter($Y, $m, $d, $H, $i);
            
            $t1 = $date->getTimestamp();
            $t2 = $jdApdater->toTimestamp($date->getOffset());
            $diff = abs($t2 - $t1);
            
            $this->assertLessThan(
                0.001, 
                $diff,
                sprintf(
                    "Expected: %f (%s) \nMatched: %f \nDiffirent: %f seconds.",
                    $t1,
                    $date->format('c'),
                    $t2,
                    $diff
                )
            );
        }
    }
}