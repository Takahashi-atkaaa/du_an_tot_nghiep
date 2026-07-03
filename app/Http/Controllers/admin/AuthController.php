<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatLaiMatKhauRequest;
use App\Http\Requests\DoiMatKhauRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\QuenMatKhauRequest;
use App\Mail\QuenMatKhauMail;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin_xem_truoc.auth.login');
    }

    public function login(LoginRequest $request)
    {
        // Laravel thấy type là LoginRequest
        // → Tự tạo LoginRequest (chạy validation trước)
        // → Nếu pass → truyền vào
        // → Nếu fail → trả lỗi, không vào hàm này

        if (!Auth::attempt(
        [
        'email' => $request->email, 
        'password' => $request->password
        ])) 
         {
            return back()->withErrors(['email' => 'Email hoac mat khau khong dung']);
         }

        $nguoiDung = Auth::user();

        if ($nguoiDung->trang_thai == false) {
            Auth::logout();
            return back()->withErrors(['email' => 'Tai khoan da bi khoa']);
        }

        $request->session()->regenerate();

        $idVaiTro = $nguoiDung->id_vai_tro;

        if (in_array($idVaiTro, [1, 2], true)) {
            return redirect('/admin/dashboard');
        }

        if ($idVaiTro === 3) {
            return redirect('/nhan-vien/');
        }

        return redirect('/admin/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    public function showDoiMatKhau()
    {
        return view('admin_xem_truoc.auth.doi-mat-khau');
    }

    public function doiMatKhau(DoiMatKhauRequest $request)
    {
        $user = $request->user();
        $user->mat_khau = $request->mat_khau_moi;
        $user->save();

        return redirect('/admin/dashboard')->with('success', 'Đổi mật khẩu thành công');
    }

    public function showQuenMatKhau()
    {
        return view('admin_xem_truoc.auth.quen-mat-khau');
    }

    public function guiEmailQuenMatKhau(QuenMatKhauRequest $request)
    {
        $email = $request->email;

        $user = NguoiDung::where('email', $email)->first();

        if (!$user) {
            return back()->with('error', 'Email không tồn tại trong hệ thống.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        Mail::to($email)->send(new QuenMatKhauMail($token));

        return back()->with('success', 'Đã gửi email đặt lại mật khẩu. Vui lòng kiểm tra hộp thư.');
    }

    public function showFormDatLaiMatKhau($token)
    {
        return view('admin_xem_truoc.auth.dat-lai-mat-khau', ['token' => $token]);
    }

    public function datLaiMatKhau(DatLaiMatKhauRequest $request)
    {
        $record = DB::table('password_reset_tokens')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$record) {
            return back()->with('error', 'Token không hợp lệ.');
        }

        $expiresAt = \Carbon\Carbon::parse($record->created_at)->addMinutes(60);
        if (now()->greaterThan($expiresAt)) {
            DB::table('password_reset_tokens')->where('email', $record->email)->delete();
            return back()->with('error', 'Token đã hết hạn. Vui lòng yêu cầu đặt lại mật khẩu mới.');
        }

        if (!Hash::check($request->token, $record->token)) {
            return back()->with('error', 'Token không hợp lệ.');
        }

        $user = NguoiDung::where('email', $record->email)->first();

        if (!$user) {
            return back()->with('error', 'Không tìm thấy tài khoản.');
        }

        $user->mat_khau = $request->mat_khau_moi;
        $user->save();

        DB::table('password_reset_tokens')->where('email', $record->email)->delete();

        return redirect('/admin/login')->with('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập.');
    }
}
