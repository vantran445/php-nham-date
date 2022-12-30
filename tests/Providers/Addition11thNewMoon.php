<?php namespace Vantran\PhpNhamDate\Tests\Providers;

/**
 * Dữ liệu điểm sóc (ngày 01 âm lịch) tháng 11 của một số năm
 */
trait Addition11thNewMoon
{
    /**
     * Mảng dữ liệu điểm bắt đầu 01 tháng 11 âm lịch của một số năm. Dữ liệu được khớp từ một số phần mềm âm lịch sẵn có 
     * bằng các ngôn ngữ khác PHP. Múi giờ được xác định là Asia/Ho_Chi_Minh
     *
     * @return array
     */
    public function addition11thNewMonData(): array
    {
        $data = [
            '2022-11-24',
            '2021-12-04',
            '2020-12-14',
            '2019-11-26',
            '2018-12-07',
            '2017-12-18',
            '2016-11-29',
            '2015-12-11',
            '2014-12-22', 
            '2013-12-03',
            '2012-12-13'
        ];

        return array_map(function ($date) {
            $date = explode('-', $date);
            return array_map(fn($val) => (int)$val, $date);
        }, $data);
    }
}