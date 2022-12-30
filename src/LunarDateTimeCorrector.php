<?php namespace Vantran\PhpNhamDate;

use Vantran\PhpNhamDate\Adapters\BaseAdapter;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;
use Vantran\PhpNhamDate\Adapters\NewMoonAdapter;
use Vantran\PhpNhamDate\Adapters\SunlongitudeAdapter;

class LunarDateTimeCorrector extends BaseAdapter
{
    protected $errors = [];

    public function __construct(protected LunarDateTimeRepository $repo)
    {

    }

    /**
     * Xác định tháng âm lịch đầu vào có thể là tháng nhuận hay không
     * 
     * @return bool 
     * @throws Exception 
     */
    public function canMonthToBeLeap(): bool
    {
        // Các tháng 01, 11 và 12 không được chọn làm tháng nhuận
        if ($this->repo->getMonth() === 1 || $this->repo->getMonth() >= 11) {
            return false;
        }

        /**
         * Trường hợp tháng âm lịch đầu vào từ 2 đến 10, cần phải xét năm đó có thể có tháng nhuận hay không, nếu số năm
         * không chia hết chia 19 và cũng không dư một trong 3, 6, 9, 11, 14, 17 thì năm đó không có tháng nhuận.
         */
        $remainder = $this->repo->getYear() % 19;

        if (!in_array($remainder, [0, 3, 6, 9, 11, 14, 17])) {
            return false;
        }

        /**
         * Trường hợp năm âm lịch đầu vào là năm nhuận, cần xét tháng âm lịch đầu vào có phải tháng nhuận hay không. Lưu
         * ý tùy theo múi giờ địa phương mà tháng nhuận có thể khác nhau do sự khác biệt về góc Kinh độ mặt trời. 
         */
        return ($this->repo->getMonth() === $this->getLeapMonth()) ? true : false;
    }

    /**
     * Trả về điểm sóc của tháng 11 âm lịch gần nhất đã qua
     * 
     * @return NewMoonAdapter 
     */
    public function get11thNewMoon(): NewMoonAdapter
    {
        if (!$this->newMoon11th) {
            /**
             * Vì ngày tháng dương lịch luôn đi trước ngày tháng âm lịch, do vậy nếu tháng âm lịch đầu vào lớn hơn hoặc
             * bằng 11, thì điểm Đông Chí nằm trong cùng năm âm lịch đó, ngược lại, cần phải tìm điểm Đông Chí của năm
             * trước đó.
             */
            $slAdapter = SunlongitudeAdapter::create(function () {
                $year = $this->repo->getYear();

                if ($this->repo->getMonth() < 11) {
                    $year --;
                }

                $jdAdapter = JulianAdapter::fromDateTimePrimitive($year, 12, 30);

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
     * Trả về số (vị trí) tương ứng với tháng nhuận âm lịch; trả về 0 nếu không tìm được tháng nhuận
     * 
     * @return int 
     * @throws Exception 
     */
    public function getLeapMonth(): int
    {
        // Lấy dữ liệu từ bộ đệm trước
        if (null !== $this->leapMonth) {
            return $this->leapMonth;
        }

        /**
         * Tháng Chạp (12) và tháng Giêng không bao giờ được chọn làm tháng nhuận, và phải sau tháng 2 âm lịch mới có
         * thể có tháng 2 nhuận, vậy từ điểm sóc của tháng 11 âm lịch năm trước đó, cộng thêm 3 tháng nữa để bắt đầu
         * tính từ sau tháng 2 âm lịch.
         */
        $newMoon = $this->get11thNewMoon()->getNext(3);

        /**
         * 1 năm nhuận âm lịch có 13 tháng, trừ đi các tháng 11, 12, 1, 2 (đầu) thì còn lại 9 tháng có thể nhuận. Tháng
         * nhuận là tháng không chứa trung khí - tức trong tháng không có ngày nào là ngày bắt đầu các góc Kinh độ mặt
         * trời ở 0, 30, 60...330 (chia hết cho 30)
         */
        $offset = 2;
        $matched = false;

        for ($i = 0; $i < 9; $i ++) {
            $slNewMoon = SunlongitudeAdapter::create($newMoon->getJdn());
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
                        $matched = true;

                        break;
                    }
                }
            }

            $newMoon = $newMoon->getNext(1);
            $offset ++;
        }

        // Đệm dữ liệu và trả về kết quả
        if (!$matched) {
            $this->leapMonth = 0;
        }
        else {
            $this->leapMonth = ($offset === 2) ? $offset : $offset - 1;
            $this->repo->setLeapMonth($this->leapMonth);
        }
        
        return $this->leapMonth;
    }
}