<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use DateTime;
use Vantran\PhpNhamDate\Adapters\JulianAdapter;

/**
 * Lớp kiểm tra bộ chuyển đổi Julian
 */
class JulianAdapterTest extends AdapterTestCase
{
    /**
     * Kiểm tra khởi tạo từ thời gian nguyên thủy
     *
     * @covers JulianAdapter
     * @return JulianAdapter
     */
    public function testCreateFromDateTimePrimitive(): JulianAdapter
    {
        $expected = JulianAdapter::JDN_EPOCH_TIME; // 2440588 - 1990-01-01T00:00:00+0000
        $jdAdapter = JulianAdapter::fromDateTimePrimitive(
            1970,
            1,
            1,
            0,
            0,
            0
        );
        
        $this->assertEquals($expected, $jdAdapter->getJdn());

        return $jdAdapter;
    }

    /**
     * Kiểm tra khởi tạo từ Timestamp
     *
     * @depends testCreateFromDateTimePrimitive
     * @covers JulianAdapter
     * 
     * @param JulianAdapter $baseAdapter
     * @return void
     */
    public function testCreateFromTimestamp(JulianAdapter $baseAdapter)
    {
        $jdAdapter = JulianAdapter::fromTimestamp(0);
        $this->assertEquals($baseAdapter->getJdn(), $jdAdapter->getJdn());
    }

    /**
     * Trong việc lập lịch, Dương lịch bắt đầu ngày mới lúc 00:00, Âm lịch bắt đầu lúc 23:00 hoặc 00:00, ngày Julian thông
     * thường cũng được tính toán vào lúc 00:00 để đồng bộ. Do sự chênh lệch múi giờ, nếu sử dụng số ngày Julian theo 
     * UTC sẽ gặp một số khó khăn trong quá trình tính toán vì phải xét đến phần thập phân của đầu ra. Bài kiểm tra này
     * kiểm tra đầu ra của số ngày Julian theo giờ địa phương chứ không theo UTC nữa.
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testLocalOutput()
    {
        /**
         * UTC chênh lệnh 7 giờ so với giờ VN vào thời điểm 2022, tức vào lúc 19:00 theo giờ UTC thì tại Việt Nam đã
         * chuyển sang 00:00 ngày mới tiếp theo, do đó bộ đếm Julian giờ địa phương phải tăng lên giá trị của ngày mới
         * để thuận tiện cho việc so sánh.
         */
        $jdAdapter = JulianAdapter::fromDateTimePrimitive(
            2022,
            10,
            15,
            19,
            0
        );
        $diffDays = $jdAdapter->getLocalJdn(false) - $jdAdapter->getJdn(false);

        $this->assertEquals(1, $diffDays);
        $this->assertEquals('10/16/2022', jdtogregorian($jdAdapter->getLocalJdn()));
    }

    /**
     * Kiểm tra chuyển đổi ngược ngày Julian về mốc ngày tháng năm địa phương.
     *
     * @covers JulianAdapter
     * @return void
     */
    public function testGetBaseLocalDateTime()
    {
        $datetime = new DateTime('now', JulianAdapter::getTimeZone());
        $jdAdapter = JulianAdapter::fromDateTime($datetime);
        $baseDateTime = $jdAdapter->getBaseLocalDateTime();

        $this->assertEquals($datetime->format('Y'), $baseDateTime->year);
        $this->assertEquals($datetime->format('n'), $baseDateTime->month);
        $this->assertEquals($datetime->format('j'), $baseDateTime->day);
        $this->assertEquals($datetime->format('H'), $baseDateTime->hour);
        $this->assertEquals($datetime->format('i'), $baseDateTime->minute);
        $this->assertEquals($datetime->format('s'), $baseDateTime->second);
    }
}