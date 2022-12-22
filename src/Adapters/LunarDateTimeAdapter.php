<?php namespace Vantran\PhpNhamDate\Adapters;

/**
 * Bộ chuyển đổi dương lịch sang âm lịch
 * 
 * @author Văn Trần <caovan.info@gmail.com>
 */
class LunarDateTimeAdapter extends BaseAdapter
{
    /**
     * Tạo mới đối tượng
     *
     * @param integer $Y năm dương lịch - 4 chữ số
     * @param integer $m tháng dương lịch - từ 1 đến 12
     * @param integer $d ngày dương lịch - từ 1 đến 31
     */
    public function __construct(
        protected int $Y,
        protected int $m,
        protected int $d,
    ) {}

    /**
     * Kiểm tra có phải năm nhuận âm lịch hay không, quy tắc chung là nếu số năm dương lịch chia hết cho 19 hoặc dư 
     * 3, 6, 9, 11, 14, 17 thì năm âm lịch tương ứng sẽ có tháng nhuận.
     *
     * @return boolean
     */
    public function isLeapYear(): bool
    {
        if (null === $this->leapYear) {
            $cal = $this->Y % 19;
            $this->leapYear = in_array($cal, [0, 3, 6, 9, 11, 14, 17]);
        }

        return $this->leapYear;
    }

    /**
     * Trả về bộ chuyển đổi Julian tại thời điểm tính toán
     *
     * @return JulianAdapter
     */
    protected function getJdAdapter(): JulianAdapter
    {
        if (!$this->jdAdapter) {
            $this->jdAdapter = JulianAdapter::fromDateTimePrimitive(
                $this->Y, 
                $this->m, 
                $this->d,
            );
        }

        return $this->jdAdapter;
    }

    /**
     * Trả về bộ chuyển đổi điểm Sóc của thời điểm nhập (đầu vào). Lưu ý, điểm sóc có thể khởi ở bất kỳ giờ nào trong
     * ngày chứ không nhất thiết là lúc 00:00.
     *
     * @return NewMoonAdapter
     */
    public function getNewMoon(): NewMoonAdapter
    {
        if (!$this->newMoon) {
            $jdAdapter = $this->getJdAdapter();
            $newMoon = NewMoonAdapter::create($jdAdapter->getTimestamp() + 86400);

            /**
             * Một số trường hợp khi điểm nhập vào ngày cuối tháng (29 hoặc 30), nếu điểm Sóc tính được là ngày 01 đầu
             * tháng mới, thì cần tìm về điểm sóc trước đó nữa.
             */
            if ($newMoon->getLocalJdn(false) > $jdAdapter->getLocalJdn(false)) {
                $newMoon = NewMoonAdapter::create($newMoon->getTimestamp() - 86400);
            }

            $this->newMoon = $newMoon;
        }

        return $this->newMoon;
    }

    /**
     * Trả về điểm sóc của tháng 11 gần nhất đã qua (tức ngày 01 tháng 11 âm lịch của năm ngay kế trước). Việc sử dụng 
     * điểm sóc tháng 11 của năm kế trước làm điểm mốc giúp thuận tiện hơn cho việc tính toán tháng nhuận cũng như số 
     * ngày tháng, năm, âm lịch.
     *
     * @return NewMoonAdapter
     */
    public function getNewMoon11th(): NewMoonAdapter
    {
        if (!$this->newMoon11th) {
            // Kinh độ mặt trời tại thời điểm 270 độ (Đông chí) năm trước
            $slAdapter = SunlongitudeAdapter::create(function () {
                $jdAdapter = JulianAdapter::fromDateTimePrimitive(($this->Y - 1), 12, 30);
                return $jdAdapter->getJdn();
            })
                ->getLongitudeNewTerm();

            /**
             * Tăng thêm 1 ngày để đảm bảo điểm sóc được tính toán đúng vì đôi khi có sự chênh lệch một vài giờ giữa
             * điểm bắt đầu Đông Chí và bắt đầu ngày Sóc. Quy tắc cần ghi nhớ rằng tháng 11 âm lịch phải luôn luôn chứa
             * điểm bắt đầu Đông Chí.
             */
            $this->newMoon11th = NewMoonAdapter::create($slAdapter->getTimestamp() + 86400);
        }

        return $this->newMoon11th;
    }

    /**
     * Trả về tháng nhuận âm lịch
     *
     * @return integer
     */
    public function getLeapMonth(): int
    {
        // Lấy dữ liệu từ bộ đệm trước
        if (null !== $this->leapMonth) {
            return $this->leapMonth;
        }

        // Nếu không phải năm nhuận thì không cần tính nữa, vì năm nhuận mới có tháng nhuận
        if (!$this->isLeapYear()) {
            return $this->leapMonth = 0;
        }

        /**
         * Tháng Chạp (12) và tháng Giêng không bao giờ được chọn làm tháng nhuận, và phải sau tháng 2 âm lịch mới có
         * thể có tháng 2 nhuận, vậy từ điểm sóc của tháng 11 âm lịch năm trước đó, cộng thêm 3 tháng nữa để bắt đầu
         * tính từ sau tháng 2 âm lịch.
         */
        $newMoon = $this->getNewMoon11th()->getNext(3);

        /**
         * 1 năm nhuận âm lịch có 13 tháng, trừ đi các tháng 11, 12, 1, 2 (đầu) thì còn lại 9 tháng có thể nhuận. Tháng
         * nhuận là tháng không chứa trung khí - tức trong tháng không có ngày nào là ngày bắt đầu các góc Kinh độ mặt
         * trời ở 0, 30, 60...330 (chia hết cho 30)
         */
        $offset = 2;
        for ($i = 0; $i < 9; $i ++) {
            $slNewMoon = SunlongitudeAdapter::fromTimestamp($newMoon->getTimestamp());
            $slNewTerm = $slNewMoon->getLongitudeNewTerm();

            if ($slNewTerm->getDegree(false) % 30 == 0) {
                $diffJdn = $slNewMoon->getLocalJdn(false) - $slNewTerm->getLocalJdn(false);

                if ($diffJdn == 0) {
                    continue;
                }
                elseif ($diffJdn >= 1 && $diffJdn <= 5) {
                    $nextNewMoon = $newMoon->getNext(1);
                    $nextSlNewTerm = $slNewTerm->getNext(30);

                    if ($nextSlNewTerm->getLocalJdn(false) >= $nextNewMoon->getLocalJdn(false)) {
                        // Lưu tem thời gian UNIX của điểm sóc tháng nhuận để sử dụng sau
                        $this->leapMonthTimestamp = $newMoon->getTimestamp();
                        break;
                    }
                }
            }

            $newMoon = $newMoon->getNext(1);
            $offset ++;
        }

        // Đệm dữ liệu và trả về kết quả
        $this->leapMonth = ($offset === 2) ? $offset : $offset - 1;
        return $this->leapMonth;
    }

    /**
     * Lấy tháng âm lịch
     *
     * @return integer
     */
    public function getMonth(): int
    {
        if (!$this->month) {
            $diffDays = $this->getNewMoon()->getLocalJdn() - $this->getNewMoon11th()->getLocalJdn();
            $diffMonth = round($diffDays / 29.53, 1);

            // Tính toán năm âm lịch kết hợp
            $this->year = $this->Y - 1;

            if ($diffMonth <= 1) {
                if ($this->getNewMoon()->getTimestamp() === $this->getNewMoon11th()->getTimestamp()) {
                    $month = 11;
                }
                else {
                    $month = 12;
                }
            }
            else {
                $this->year ++;
                $month = 11 + $diffMonth;

                if ($month > 12) {
                    $month %= 12;
                }

                if (
                    $this->getLeapMonth() && 
                    $this->leapMonthTimestamp <= $this->getNewMoon()->getTimestamp()
                ) {
                    $month --;
                }
            }

            $this->month = $month;
        }
        
        return $this->month;
    }

    /**
     * Trả về số (vị trí) ngày âm lịch trong tháng
     *
     * @return integer
     */
    public function getDay(): int
    {
        if (!$this->day) {
            $diff = $this->getJdAdapter()->getLocalJdn(false) - $this->getNewMoon()->getLocalJdn(false);
            $this->day = $diff + 1;
        }

        return $this->day;
    }

    /**
     * Trả về số năm Âm lịch tương ứng với Dương lịch
     *
     * @return integer
     */
    public function getYear(): int 
    {
        if (!$this->year) {
            $this->getMonth();
        }

        return $this->year;
    }
}