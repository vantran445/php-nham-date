# Xác định vị trí Kinh độ Mặt trời

Tạo một đối tượng từ lớp `Vantran\PhpNhamDate\Adapters\Sunlongitude` để có thể
tìm các thông tin về Kinh độ mặt trời (KDMT). Bạn có thể sử dụng các phương thức sau:

- `getDegree($withMinutes = true)`: Trả về số đo góc kinh độ mặt trời tại thời điểm tìm kiếm.
- `getJdn()`: Trả về số ngày Julian tương ứng với góc KDMT.
- `toDate()`: Chuyển đổi thành đối tượng PHP `DateTime`
- `toTimestamp()`: Chuyển đổi thành tem thời gian UNIX
- `toDateTimeFormat()`: Chuyển đổi thành chuỗi thời gian có định dạng mặc định 'd/m/Y H:i:s'. 
- `toPreviuos($deg = 15)`: Tạo một bản sao đối tượng mới với các giá trị tương ứng với 15 độ kế trước. Ví dụ, ban đầu là 14 độ, đầu ra sẽ tương đương 14 + 15 = 29 độ.
- `toNext($deg = 3.0034)`: Tạo một bản sao đối tượng mới với các trị trị tương ứng với 3.0034 độ tiếp theo.
- `toStartingPoint()`: Tạo một bản sao đối tượng mới với các giá trị tương ứng với điểm bắt đầu của 15 độ. Ví dụ, giá trị ban đầu là 287 độ, thì đầu ra sẽ tương ứng 285 độ. Trong Âm lịch, đây là các vị trí để xác định thời điểm khởi đầu của 1 Tiết hoặc Khí (Hệ thống 24 tiết khí).

```php
    <?php

    use Vantran\PhpNhamDate\Adapters\Sunlongitude;

    $date = new Datetime('2022-01-01');
    $sl = Sunlongitude::createFromDate($date);

    // Lấy số đo góc hiện tại
    echo 'Số đo góc bao gồm phần thập phân: ' . $sl->getDegree();
    echo 'Số đo góc được làm tròn: ' . $sl->getDegree(false);

    // Tìm các thông số ở 10.5 độ kế tiếp
    $next = $sl->toNextPosition(10.5);
    echo 'Chuỗi thòi gian tương ứng ở 10 độ kế tiếp: ' . $next->toDateTimeFormat();

    // Tìm điểm bắt đầu thời điểm lập tiết/khí
    $starting = $sl->toStartingPoint();
    echo 'Thời điểm lập tiết/khí vào lúc '. $starting->toDateTimeFormat();

    // Tìm điểm lập tiết/khí kế tiếp
    $nextStarting = $starting->toNext(15.0);
    echo 'Thời điểm lập tiết/khí kế tiếp vào lúc '. $nextStarting->toDateTimeFormat();
```