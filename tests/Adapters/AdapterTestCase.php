<?php namespace Vantran\PhpNhamDate\Tests\Adapters;

use PHPUnit\Framework\TestCase;
use Vantran\PhpNhamDate\Adapters\BaseAdapter;

class AdapterTestCase extends TestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        // Múi giờ địa phương được đặt về mặc định cho mỗi bài test
        BaseAdapter::resetDefaultTimeZone();
    }
}