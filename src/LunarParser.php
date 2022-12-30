<?php namespace Vantran\PhpNhamDate;

class LunarParser
{
    /**
     * Lưu trữ dữ liệu phân tích chuỗi thời gian Âm lịch
     * @var array
     */
    protected $parsedResult = [];

    public function __construct(
        protected string $datetime
    ) {
        if (str_contains($datetime, 'l')) {
            $this->parsedResult['leap_month'] = true;
            str_replace('l', '', $datetime);
        }

        array_merge($this->parsedResult, date_parse($datetime));

        if ($this->parsedResult['error_count'] > 0) {
            throw new \Exception("Error. Invalid date time");
        }
    }
    
}