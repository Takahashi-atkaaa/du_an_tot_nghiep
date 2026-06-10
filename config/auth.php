<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Mặc định xác thực
    |--------------------------------------------------------------------------
    |
    | Tùy chọn này định nghĩa guard xác thực mặc định và broker đặt lại mật khẩu
    | cho ứng dụng của bạn. Bạn có thể thay đổi các giá trị này theo nhu cầu,
    | nhưng chúng là điểm khởi đầu hoàn hảo cho hầu hết các ứng dụng.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'admin'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Các Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Tiếp theo, bạn có thể định nghĩa mọi guard xác thực cho ứng dụng của mình.
    | Tất nhiên, một cấu hình mặc định tuyệt vời đã được định nghĩa sẵn cho bạn
    | sử dụng session storage cùng với Eloquent user provider.
    |
    | Tất cả các authentication guard đều có một user provider, định nghĩa cách
    | người dùng thực sự được truy xuất từ database hoặc hệ thống lưu trữ khác
    | mà ứng dụng sử dụng. Thông thường, Eloquent được sử dụng.
    |
    | Được hỗ trợ: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Guard admin - dành cho hệ thống quản trị
        'admin' => [
            'driver' => 'session',
            'provider' => 'nguoidung',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Tất cả các authentication guard đều có một user provider, định nghĩa cách
    | người dùng thực sự được truy xuất từ database hoặc hệ thống lưu trữ khác
    | mà ứng dụng sử dụng. Thông thường, Eloquent được sử dụng.
    |
    | Nếu bạn có nhiều bảng hoặc model người dùng, bạn có thể cấu hình nhiều
    | provider để đại diện cho model / bảng đó. Các provider này sau đó có thể
    | được gán cho bất kỳ guard xác thực bổ sung nào bạn đã định nghĩa.
    |
    | Được hỗ trợ: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        // Provider nguoidung - sử dụng model NguoiDung
        'nguoidung' => [
            'driver' => 'eloquent',
            'model' => App\Models\NguoiDung::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Đặt lại mật khẩu
    |--------------------------------------------------------------------------
    |
    | Các tùy chọn cấu hình này chỉ định hành vi của chức năng đặt lại mật khẩu
    | của Laravel, bao gồm bảng được sử dụng để lưu trữ token
    | và user provider được gọi để thực sự truy xuất người dùng.
    |
    | Thời gian hết hạn là số phút mà mỗi token đặt lại sẽ được coi là hợp lệ.
    | Tính năng bảo mật này giữ cho token có thời gian sống ngắn để
    | chúng có ít thời gian hơn để bị đoán. Bạn có thể thay đổi theo nhu cầu.
    |
    | Cài đặt throttle là số giây mà người dùng phải chờ trước khi
    | tạo thêm token đặt lại mật khẩu. Điều này ngăn người dùng
    | nhanh chóng tạo ra một lượng lớn token đặt lại mật khẩu.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Thời gian chờ xác nhận mật khẩu
    |--------------------------------------------------------------------------
    |
    | Ở đây bạn có thể định nghĩa số giây trước khi cửa sổ xác nhận mật khẩu
    | hết hạn và người dùng được yêu cầu nhập lại mật khẩu qua màn hình
    | xác nhận. Theo mặc định, thời gian chờ kéo dài ba giờ.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
