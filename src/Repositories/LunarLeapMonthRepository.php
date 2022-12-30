<?php namespace Vantran\PhpNhamDate\Repositories;

class LunarLeapMonthRepository
{
    /**
     * 
     * @param bool $isLeap Xác định tháng âm lịch có nhuận hay không
     * @param int $offset 
     * @param int|float $timestamp 
     * @return void 
     */
    public function __construct(
        protected bool $isLeap,
        protected int $offset,
        protected int|float $timestamp
    )
    {
        
    }
}