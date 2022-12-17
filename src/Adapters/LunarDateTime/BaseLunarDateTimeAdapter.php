<?php namespace Vantran\PhpNhamDate\Adapters\LunarDateTime;

use DateTimeInterface;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;
use Vantran\PhpNhamDate\Adapters\MoonPhaseAdapter;
use Vantran\PhpNhamDate\Adapters\SunlongitudeAdapter;

class BaseLunarDateTimeAdapter
{
    protected $timezone;
    protected $offset;

    protected $attributes = [];

    /**
     * Tạo mới đối tượng
     *
     * @param DateTimeInterface $solar
     */
    public function __construct(
        protected DateTimeInterface $solar
    ) {
        $this->timezone = $this->solar->getTimezone();
        $this->offset = $this->solar->getOffset();

        $this->init();
    }

    /**
     * Khởi tạo các thông số cần thiết
     *
     * @return void
     */
    public function init()
    {
        $this->moonPhase11th = $this->get11thMoonPhase($this->solar->format('Y'));
    }

    /**
     * Magic set
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Magic get
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return isset($this->attributes[$name])? $this->attributes[$name] : null;
    }

    protected function _get11thNewMoon($Y)
    {
        // Kinh độ mặt trời khởi Đông Chí (270 độ) 
        $wSl = SunLongitudeAdapter::fromDateTimePrimitive(
            $this->solar->getOffset(),
            $this->solar->format('Y'),
            12,
            30
        )
        ->toStartingPoint()
        ->toTimeStamp();

        $moonPhase = new MoonPhase($wSl + 86400);
        $newMoon = $moonPhase->getPhaseNewMoon();

        return JulianAdapter::fromTimestamp($newMoon, 0);
    }

    /**
     * Lấy thời điểm khởi đầu ngày 01 tháng 11 âm lịch
     *
     * @return MoonPhase
     */
    public function get11thNewMoon(): MoonPhaseAdapter
    {
        if (!$this->newMoon11th) {
            $moonPhase = $this->get11thMoonPhase($this->solar->format('Y'));
            $jdn = JulianAdapter::fromTimestamp($moonPhase->getPhaseNewMoon(), $this->offset);

        }

        return $this->moonPhase11th;
    }

    /**
     * Kiểm tra có phải năm nhuận âm lịch hay không, quy tắc chung là nếu số năm
     * dương lịch chia hết cho 19 hoặc dư 3, 6, 9, 11, 14, 17 thì năm âm lịch
     * tương ứng sẽ có tháng nhuận.
     *
     * @return boolean
     */
    public function isLeapYear(): bool
    {
        $Y = $this->solar->format('Y');
        $cal = $Y % 19;

        return in_array($cal, [0, 3, 6, 9, 11, 14, 17]);
    }

    /**
     * Lấy thời điểm khởi khởi Đông Chí
     *
     * @return SunlongitudeAdapter
     */
    public function getWinterSolsticeSunLongitude($Y): SunlongitudeAdapter
    {
        return SunLongitudeAdapter::fromDateTimePrimitive(
            $this->solar->getOffset(),
            $Y,
            12,
            30,
        )->toStartingPoint();
    }

    public function get11thMoonPhase($Y): MoonPhaseAdapter
    {
        $wSl = $this->getWinterSolsticeSunLongitude($Y);
        $moonPhase = new MoonPhaseAdapter($wSl->toTimeStamp() + 86400);

        return $moonPhase;
    }

    // Phương pháp này chuẩn
    protected function getLeapMonthMethod4()
    {
        if (!$this->isLeapYear()) {
            return;
        }

        $Y = $this->solar->format('Y') - 1;
        $timestamp = $this->get11thMoonPhase($Y)->getPhaseNewMoon() + 29.53 * 3 * 86400;
        $moonPhase = new MoonPhaseAdapter($timestamp);

        $offset = 2;

        for ($i = 0; $i <= 12; ++$i) {
            $slNewMoon = SunLongitudeAdapter::fromTimestamp($moonPhase->getPhaseNewMoon(), $this->offset);
            $slBegin = $slNewMoon->toStartingPoint();

            if ($slBegin->getDegree(true) % 30 == 0 ) {
                $beginJdn = $slBegin->getJdn(false);
                $slNewMoonJdn = $slNewMoon->getJdn(false);
                $diff = $slNewMoonJdn - $beginJdn;

                if ($diff == 0) {
                    continue;
                }
                elseif ($diff >= 1 && $diff <= 5) {
                    $nextPhase = new MoonPhaseAdapter($moonPhase->getPhaseNewMoon() + 30 * 86400);
                    $nextBeginSl = $slBegin->toNext(30);

                    $jdNextPhase = JulianAdapter::fromTimestamp($nextPhase->getPhaseNewMoon(), $this->offset)->getJdn(false);
                    $jdNextSl = $nextBeginSl->getJdn(false);

                    if ($jdNextSl >= $jdNextPhase) {
                        break;
                    }
                }
            }

            $moonPhase = (isset($nextPhase))? $nextPhase : new MoonPhaseAdapter($moonPhase->getPhaseNewMoon() + 30 * 86400);
            $offset ++;
        }

        return ($offset === 2)? $offset : $offset - 1;
    }

    public function getMonth()
    {
        $diff = $this->moonPhase11th->getPhaseNewMoon() - $this->solar->getTimestamp();

        if ($diff == 0) {
            return 11;
        }

        return 11 - floor($diff / 86400 / 29.53) - 1;
    }

    public function getLeapMonth()
    {
        return $this->getLeapMonthMethod4();
    }

}