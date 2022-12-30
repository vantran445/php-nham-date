<?php namespace Vantran\PhpNhamDate\Tests;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Vantran\PhpNhamDate\LunarDateTimeRepository;

class LunarDateTimeRepositoryTest extends TestCase
{
    /**
     * Kiểm tra tạo mới đối tượng với một số thuộc tính
     * 
     * @covers LunarDateTimeRepository
     * @return void 
     * @throws InvalidArgumentException 
     * @throws ExpectationFailedException 
     */
    public function testNewInstanceWithProps()
    {
        $props = [
            'day' => 10,
            'month' => 3,
            'year' => 2022,
            'leapMonth' => false,
        ];

        $repo = new LunarDateTimeRepository($props);

        foreach ($props as $name => $val) {
            $method = 'get' . ucfirst($name);

            $this->assertEquals($val, $repo->{$method}());
        }
    }
}