<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\BaseAdapter;

/**
 * Lớp kiểm tra các cấu hình và quản lý múi giờ chung cho các bộ chuyển đổi
 */
class TimeZoneConfigTest extends TestCase
{
    /**
     * Kiểm tra thiết lập múi giờ
     *
     * @covers BaseAdapter
     * @return void
     */
    public function testTimeZone()
    {
        // Mặc định
        $timezone = BaseAdapter::getTimeZone();

        $this->assertEquals(BaseAdapter::DEFAULT_TIME_ZONE, $timezone->getName());
        $this->assertEquals(25200, BaseAdapter::getOffset());

        // Tùy chỉnh
        BaseAdapter::setTimeZone('UTC');
        BaseAdapter::setOffset(0);

        $this->assertNotEquals(BaseAdapter::DEFAULT_TIME_ZONE, BaseAdapter::getTimeZone()->getName());
        $this->assertEquals(0, BaseAdapter::getOffset());

        BaseAdapter::resetDefaultTimeZone();
    }
}