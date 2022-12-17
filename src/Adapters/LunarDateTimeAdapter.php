<?php namespace Vantran\PhpNhamDate\Adapters;

class LunarDateTimeAdapter
{
    protected $attributes = [];

    public function __construct(
        protected int $Y,
        protected int $m,
        protected int $d,
        protected int $offset
    ) {
        
    }

    /**
     * Magic getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return (isset($this->attributes[$name])) ? $this->attributes[$name] : null;
    }

    /**
     * Magic setter
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Trả về bộ chuyển đổi Kinh độ mặt trời của điểm Đông Chí 1 năm dương lịch bất kỳ
     *
     * @param integer $Y năm dương lịch định dạng 4 chữ số (YYYY)
     * @return SunlongitudeAdapter
     */
    public function getWinterSolsticeSunLongitude(int $Y): SunlongitudeAdapter
    {
        $sl = SunlongitudeAdapter::fromDateTimePrimitive($this->offset, $Y, 12, 30);
        return $sl->toStartingPoint();
    }

    /**
     * Trả về bộ chuyển đổi điểm sóc của tháng 11 Âm lịch
     *
     * @param integer $Y
     * @return NewMoonAdapter
     */
    public function getNewMoon11thMonth(int $Y): NewMoonAdapter
    {
        $sunlongitude = $this->getWinterSolsticeSunLongitude($Y);
        $newMoon = NewMoonAdapter::fromJdn($sunlongitude->getJdn());

        return $newMoon;
    }

    /**
     * Kiểm tra có phải năm nhuận âm lịch hay không, quy tắc chung là nếu số năm dương lịch chia hết cho 19 hoặc dư 
     * 3, 6, 9, 11, 14, 17 thì năm âm lịch tương ứng sẽ có tháng nhuận.
     *
     * @return boolean
     */
    public function isLeapYear(): bool
    {
        $cal = $this->Y % 19;
        return in_array($cal, [0, 3, 6, 9, 11, 14, 17]);
    }

    public function getLeapMonth()
    {
        if (!$this->isLeapYear()) {
            return 0;
        }

        /**
         * Tháng Chạp (12) và tháng Giêng không bao giờ được chọn làm tháng nhuận, và phải sau tháng 2 âm lịch mới có
         * thể có tháng 2 nhuận, vậy ta bắt đầu tính từ điểm sóc của tháng sau tháng 2 âm lịch.
         */
        $newMoon = $this->getNewMoon11thMonth($this->Y - 1)->toNext(3);

        /**
         * Xác định điểm khởi trung khí gần nhất của tháng sau tháng 2 âm lịch. Lưu ý có 12 trung khí là vị trí kinh độ
         * mặt trời ở các góc chia hết cho 30 (0, 30, 60...)
         */
        // $sunlongitude = SunlongitudeAdapter::fromTimestamp($newMoon->getTimestamp(), $this->offset);
        // $sunlongitude = $sunlongitude->toStartingPoint();

        // if ($sunlongitude->getDegree(false) % 30 != 0) {
        //     $sunlongitude->toNext(15);
        // }

        /**
         * 1 năm nhuận âm lịch có 13 tháng, trừ đi các tháng 11, 12, 1, 2 (đầu) thì còn lại 9 tháng có thể nhuận. Tháng
         * nhuận là tháng không chứa trung khí (tức trong tháng không có ngày nào là ngày bắt đầu các góc 0, 30, 60...)
         */
        $offset = 2;
        for ($i = 0; $i < 13; $i ++) {
            $slNewMoon = SunlongitudeAdapter::fromTimestamp($newMoon->getTimestamp(), $this->offset);
            $slNewTerm = $slNewMoon->toStartingPoint();

            if ($slNewTerm->getDegree(false) % 30 == 0) {
                $diffJdn = $slNewMoon->getJdn(false) - $slNewTerm->getJdn(false);

                if ($diffJdn == 0) {
                    continue;
                }
                elseif ($diffJdn >= 1 && $diffJdn <= 5) {
                    $nextNewMoon = $newMoon->toNext(1);
                    $nextSlNewTerm = $slNewTerm->toNext(30);

                    if ($nextSlNewTerm->getJdn(false) >= $nextNewMoon->toJdn(false)) {
                        break;
                    }
                }
            }

            $newMoon = $newMoon->toNext(1);
            $offset ++;
        }

        return ($offset === 2) ? $offset : $offset - 1;
    }
}