<?php

namespace App\Http\Controllers;

use App\Models\GiaoDich;
use App\Services\VnpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VnpayController extends Controller
{
    public function __construct(protected VnpayService $vnpay)
    {
    }

    /**
     * Nhân viên POS khởi tạo giao dịch VNPay:
     *   1. Validate giỏ + khách + khuyến mãi + điểm (tái sử dụng helper của NhanVienController).
     *   2. Tạo hoa_don trạng thái 'Chờ thanh toán' (chưa trừ kho).
     *   3. Tạo giao_dich trạng thái 'cho_xac_nhan'.
     *   4. Trả về redirect_url VNPay + hoa_don_id cho JS dùng để render QR và polling.
     */
    public function createPayment(Request $request): JsonResponse
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer|exists:bien_the_san_pham,id',
            'cart.*.qty' => 'required|integer|min:1',
            'id_khach_hang' => 'nullable|integer|exists:khach_hang,id',
            'id_khuyen_mai' => 'nullable|integer|exists:khuyen_mai,id',
            'diem_su_dung' => 'nullable|integer|min:0',
            'bank_code' => 'nullable|string|max:50',
        ]);

        /** @var \App\Http\Controllers\nhan_vien\NhanVienController $pos */
        $pos = app(\App\Http\Controllers\nhan_vien\NhanVienController::class);
        $calc = $pos->tinhTienDonHang($request);

        if ($calc instanceof JsonResponse) {
            return $calc;
        }

        $hoaDonId = null;

        DB::transaction(function () use ($request, $calc, &$hoaDonId) {
            $hoaDonId = DB::table('hoa_don')->insertGetId([
                'id_nguoi_dung' => auth()->user()->id,
                'id_khach_hang' => $request->id_khach_hang,
                'id_ca_lam_viec' => session('id_ca_lam_viec') ?? null,
                'id_khuyen_mai' => $request->id_khuyen_mai,
                'tong_tien_hang' => $calc['tong_tien_hang'],
                'tien_giam_gia' => $calc['tien_giam_gia'],
                'khach_can_tra' => $calc['khach_can_tra'],
                'tien_khach_dua' => $calc['khach_can_tra'],
                'tien_thua' => 0,
                'phuong_thuc_thanh_toan' => 'VNPay',
                'trang_thai' => 'Chờ thanh toán',
                'diem_su_dung' => $calc['diem_su_dung'],
                'diem_thu_duoc' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('chi_tiet_hoa_don')->insert(
                collect($calc['items'])->map(fn ($item) => [
                    'id_hoa_don' => $hoaDonId,
                    'id_san_pham' => $item['bien_the']->product_id,
                    'id_bien_the' => $item['bien_the']->id,
                    'id_chi_tiet_phieu' => null,
                    'so_luong' => $item['so_luong'],
                    'gia_ban' => $item['gia_ban'],
                    'thanh_tien' => $item['thanh_tien'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all()
            );

            GiaoDich::create([
                'id_hoa_don' => $hoaDonId,
                'phuong_thuc' => GiaoDich::PHUONG_THUC_VNPAY,
                'so_tien' => $calc['khach_can_tra'],
                'trang_thai' => GiaoDich::TRANG_THAI_CHO_XAC_NHAN,
                'ma_tham_chieu' => (string) $hoaDonId,
                'du_lieu_phan_hoi' => [
                    'order_info' => 'Thanh toan GD:' . $hoaDonId,
                    'bank_code_request' => $request->bank_code,
                    'created_from' => 'pos',
                ],
            ]);
        });

        $amount = (float) $calc['khach_can_tra'];
        $orderInfo = $this->vnpay->sanitizeOrderInfo('Thanh toan GD:' . $hoaDonId);

        $redirectUrl = $this->vnpay->buildPaymentUrl(
            txnRef: $hoaDonId,
            amount: $amount,
            orderInfo: $orderInfo,
            bankCode: $request->bank_code,
            clientIp: $request->ip(),
        );

        // Sinh HTML form auto-submit với cùng bộ params + secureHash đã verify chéo.
        $formParams = [
            'vnp_Version'    => (string) config('vnpay.version', '2.1.0'),
            'vnp_TmnCode'    => (string) config('vnpay.tmn_code'),
            'vnp_Amount'     => (string) ((int) round($amount * 100)),
            'vnp_Command'    => (string) config('vnpay.command', 'pay'),
            'vnp_CreateDate' => $this->vnpayCreateDate(),
            'vnp_CurrCode'   => (string) config('vnpay.currency', 'VND'),
            'vnp_IpAddr'     => $request->ip() ?: '127.0.0.1',
            'vnp_Locale'     => (string) config('vnpay.locale', 'vn'),
            'vnp_OrderInfo'  => $orderInfo,
            'vnp_OrderType'  => (string) config('vnpay.order_type', 'other'),
            'vnp_ReturnUrl'  => (string) config('vnpay.return_url'),
            'vnp_TxnRef'     => (string) $hoaDonId,
            'vnp_ExpireDate' => $this->vnpayCreateDate((int) config('vnpay.expire_minutes', 15)),
        ];

        // #region agent log — lỗi-03 investigation session 9920af
        $logPath = __DIR__ . '/../../../.cursor/debug-9920af.log';
        $hashSecret = (string) config('vnpay.hash_secret');

        // Build hashdata y hệt VnpayService::buildPaymentUrl để so sánh với form
        $urlParams = [
            'vnp_Version'    => (string) config('vnpay.version', '2.1.0'),
            'vnp_TmnCode'    => (string) config('vnpay.tmn_code'),
            'vnp_Amount'     => (string) ((int) round((float) $amount * 100)),
            'vnp_Command'    => (string) config('vnpay.command', 'pay'),
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode'   => (string) config('vnpay.currency', 'VND'),
            'vnp_IpAddr'     => $request->ip() ?: '127.0.0.1',
            'vnp_Locale'     => (string) config('vnpay.locale', 'vn'),
            'vnp_OrderInfo'  => $orderInfo,
            'vnp_OrderType'  => (string) config('vnpay.order_type', 'other'),
            'vnp_ReturnUrl'  => (string) config('vnpay.return_url'),
            'vnp_TxnRef'     => (string) $hoaDonId,
            'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
        ];
        $urlHashData = $this->vnpay->buildHashData($urlParams);
        $urlSecureHash = hash_hmac('sha512', $urlHashData, $hashSecret);
        $formHashData = $this->vnpay->buildHashData($formParams);
        $formSecureHash = hash_hmac('sha512', $formHashData, $hashSecret);

        $logEntries = [
            'URL_PARAMS' => $urlParams,
            'FORM_PARAMS' => $formParams,
            'URL_HASHDATA' => $urlHashData,
            'FORM_HASHDATA' => $formHashData,
            'URL_SECUREHASH' => $urlSecureHash,
            'FORM_SECUREHASH' => $formSecureHash,
            'HASHES_MATCH' => ($urlSecureHash === $formSecureHash),
            'HASH_SECRET_LEN' => strlen($hashSecret),
            'HASH_SECRET_PREVIEW' => substr($hashSecret, 0, 6) . '...' . substr($hashSecret, -4),
            'PAY_URL_CONFIG' => (string) config('vnpay.pay_url'),
            'RETURN_URL_CONFIG' => (string) config('vnpay.return_url'),
            'TMN_CODE_CONFIG' => (string) config('vnpay.tmn_code'),
            'AMOUNT_ORIGINAL' => $amount,
            'AMOUNT_x100' => (string) ((int) round($amount * 100)),
            'HOA_DON_ID' => $hoaDonId,
            'ORDER_INFO_RAW' => 'Thanh toan GD:' . $hoaDonId,
            'ORDER_INFO_SANITIZED' => $orderInfo,
            'CLIENT_IP' => $request->ip(),
            'BANK_CODE_REQUEST' => $request->bank_code,
            'PHP_TIMEZONE' => date_default_timezone_get(),
            'URL_CREATEDATE_UTC' => date('YmdHis'),
            'FORM_CREATEDATE_HOCHIMINH' => $this->vnpayCreateDate(),
            'FORM_EXPIREDATE_HOCHIMINH' => $this->vnpayCreateDate(15),
            'URL_EXPIREDATE_UTC' => date('YmdHis', strtotime('+15 minutes')),
        ];
        foreach ($logEntries as $key => $val) {
            $payload = [
                'sessionId' => '9920af',
                'runId' => 'round2-loi-03',
                'hypothesisId' => in_array($key, ['HASHES_MATCH', 'URL_SECUREHASH', 'FORM_SECUREHASH']) ? 'H1' :
                                 (in_array($key, ['AMOUNT_x100', 'AMOUNT_ORIGINAL']) ? 'H2' :
                                 (in_array($key, ['PAY_URL_CONFIG', 'RETURN_URL_CONFIG']) ? 'H3' :
                                 (in_array($key, ['ORDER_INFO_RAW', 'ORDER_INFO_SANITIZED']) ? 'H4' :
                                 (in_array($key, ['URL_CREATEDATE_UTC', 'FORM_CREATEDATE_HOCHIMINH', 'FORM_EXPIREDATE_HOCHIMINH', 'URL_EXPIREDATE_UTC', 'PHP_TIMEZONE']) ? 'H6' :
                                 (in_array($key, ['TMN_CODE_CONFIG', 'HASH_SECRET_LEN', 'HASH_SECRET_PREVIEW']) ? 'H8' : 'H7'))))),
                'location' => 'VnpayController::createPayment',
                'message' => $key,
                'data' => is_string($val) ? $val : $val,
                'timestamp' => (int) (microtime(true) * 1000),
            ];
            @file_put_contents($logPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND);
        }
        // #endregion agent log

        $formHtml = $this->vnpay->buildPaymentFormHtml(
            (string) config('vnpay.pay_url'),
            $formParams
        );

        return response()->json([
            'success' => true,
            'hoa_don_id' => $hoaDonId,
            'redirect_url' => $redirectUrl,
            'form_html' => $formHtml,
            'amount' => $amount,
        ]);
    }

    /**
     * Sinh timestamp YmdHis cho vnp_CreateDate / vnp_ExpireDate,
     * ép dùng cùng định dạng để hashdata của form POST và URL khớp nhau.
     */
    protected function vnpayCreateDate(int $addMinutes = 0): string
    {
        $now = new \DateTime('now', new \DateTimeZone('Asia/Ho_Chi_Minh'));
        if ($addMinutes > 0) {
            $now->modify("+{$addMinutes} minutes");
        }
        return $now->format('YmdHis');
    }

    /**
     * Endpoint polling để JS gọi và xem giao_dich đã thành công chưa.
     */
    public function checkStatus(int $hoaDonId): JsonResponse
    {
        $gd = GiaoDich::where('id_hoa_don', $hoaDonId)
            ->where('phuong_thuc', GiaoDich::PHUONG_THUC_VNPAY)
            ->orderByDesc('id')
            ->first();

        if (!$gd) {
            return response()->json([
                'status' => 'not_found',
                'hoa_don_id' => $hoaDonId,
            ], 404);
        }

        return response()->json([
            'hoa_don_id' => $hoaDonId,
            'trang_thai' => $gd->trang_thai,
            'so_tien' => (float) $gd->so_tien,
            'ma_giao_dich_doi_tac' => $gd->ma_giao_dich_doi_tac,
            'ngay_gio_thanh_toan' => optional($gd->ngay_gio_thanh_toan)->toIso8601String(),
        ]);
    }

    /**
     * Trang user-side return: VNPay redirect khách về sau khi thanh toán.
     */
    public function return(Request $request)
    {
        $verified = $this->vnpay->verifySignature($request);
        $payload = $this->vnpay->parseIpnPayload($request);

        // Fallback: nếu IPN không reach được server (vd: localhost không public),
        // vẫn cập nhật DB từ return() — method xuLyKetQuaThanhToan đã idempotent
        // nên gọi 2 lần (return + ipn) vẫn an toàn.
        if ($verified) {
            $payload['processed_via'] = 'return';
            $gd = GiaoDich::where('ma_tham_chieu', (string) $payload['txn_ref'])
                ->where('phuong_thuc', GiaoDich::PHUONG_THUC_VNPAY)
                ->orderByDesc('id')
                ->first();
            if ($gd && $gd->trang_thai !== GiaoDich::TRANG_THAI_THANH_CONG) {
                $hoaDon = DB::table('hoa_don')->where('id', $gd->id_hoa_don)->first();
                if ($hoaDon && $hoaDon->trang_thai !== 'Đã hủy') {
                    $isSuccess = $payload['response_code'] === '00'
                        && $payload['transaction_status'] === '00';
                    $this->xuLyKetQuaThanhToan($gd, $hoaDon, $payload, $isSuccess);
                }
            }
        }

        $success = $verified
            && $payload['response_code'] === '00'
            && $payload['transaction_status'] === '00';

        return view('nhan_vien_view.payment.vnpay-return', [
            'verified' => $verified,
            'success' => $success,
            'payload' => $payload,
        ]);
    }

    /**
     * Server-to-server IPN callback. Đây là nguồn cập nhật chính thức từ VNPay.
     */
    public function ipn(Request $request): JsonResponse
    {
        if (!$this->vnpay->verifySignature($request)) {
            return response()->json([
                'RspCode' => '97',
                'Message' => 'Invalid signature',
            ]);
        }

        $payload = $this->vnpay->parseIpnPayload($request);

        $gd = GiaoDich::where('ma_tham_chieu', (string) $payload['txn_ref'])
            ->where('phuong_thuc', GiaoDich::PHUONG_THUC_VNPAY)
            ->orderByDesc('id')
            ->first();

        if (!$gd) {
            return response()->json([
                'RspCode' => '01',
                'Message' => 'Order not found',
            ]);
        }

        if ($gd->trang_thai === GiaoDich::TRANG_THAI_THANH_CONG) {
            return response()->json([
                'RspCode' => '02',
                'Message' => 'Order already confirmed',
            ]);
        }

        if (abs(((float) $gd->so_tien) - (float) $payload['amount']) > 0.01) {
            return response()->json([
                'RspCode' => '04',
                'Message' => 'invalid amount',
            ]);
        }

        $hoaDon = DB::table('hoa_don')->where('id', $gd->id_hoa_don)->first();
        if (!$hoaDon || $hoaDon->trang_thai === 'Đã hủy') {
            return response()->json([
                'RspCode' => '02',
                'Message' => 'Order already cancelled',
            ]);
        }

        $isSuccess = $payload['response_code'] === '00'
            && $payload['transaction_status'] === '00';

        $this->xuLyKetQuaThanhToan($gd, $hoaDon, $payload, $isSuccess);

        Log::info('VNPay IPN processed', [
            'hoa_don_id' => $hoaDon->id,
            'txn_ref' => $payload['txn_ref'],
            'success' => $isSuccess,
        ]);

        return response()->json([
            'RspCode' => '00',
            'Message' => 'Confirm Success',
        ]);
    }

    protected function parsePayDate(?string $v): ?Carbon
    {
        if (!$v || strlen($v) < 14) {
            return null;
        }
        try {
            return Carbon::createFromFormat('YmdHis', $v);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Chuẩn hoá mã giao dịch đối tác trước khi lưu vào cột UNIQUE.
     * Trường `ma_giao_dich_doi_tac` có UNIQUE index, nên giá trị rỗng / '0'
     * (VNPay thường trả về khi khách HỦY thanh toán) sẽ gây 1062 Duplicate entry
     * nếu đã có record cũ cùng giá trị. MySQL cho phép nhiều NULL trong UNIQUE.
     */
    protected function normalizeTxnNo($v): ?string
    {
        if ($v === null) return null;
        $s = trim((string) $v);
        if ($s === '' || $s === '0') return null;
        return $s;
    }

    /**
     * Tách logic cập nhật DB sau khi VNPay trả kết quả thành một method chung,
     * để cả return() (user-side) và ipn() (server-to-server) đều dùng chung.
     * Idempotent: nếu giao_dich đã thanh_cong thì bỏ qua.
     */
    protected function xuLyKetQuaThanhToan(GiaoDich $gd, $hoaDon, array $payload, bool $isSuccess): void
    {
        if ($gd->trang_thai === GiaoDich::TRANG_THAI_THANH_CONG) {
            return;
        }

        DB::transaction(function () use ($gd, $hoaDon, $payload, $isSuccess) {
            $gd->fill([
                'trang_thai' => $isSuccess
                    ? GiaoDich::TRANG_THAI_THANH_CONG
                    : GiaoDich::TRANG_THAI_THAT_BAI,
                'ma_giao_dich_doi_tac' => $this->normalizeTxnNo($payload['transaction_no'] ?? null),
                'ma_phan_hoi' => $payload['response_code'],
                'trang_thai_doi_tac' => $payload['transaction_status'],
                'ma_ngan_hang' => $payload['bank_code'],
                'ngay_gio_thanh_toan' => $this->parsePayDate($payload['pay_date']),
                'du_lieu_phan_hoi' => array_merge(
                    $gd->du_lieu_phan_hoi ?? [],
                    ['processed_via' => $payload['processed_via'] ?? 'unknown']
                ),
            ]);
            $gd->save();

            if ($isSuccess) {
                DB::table('chi_tiet_hoa_don')
                    ->where('id_hoa_don', $hoaDon->id)
                    ->update(['updated_at' => now()]);

                $canTra = (float) $hoaDon->khach_can_tra;
                $diemThuDuoc = (int) floor($canTra / 10000);

                $this->truTonKho($hoaDon->id);

                DB::table('hoa_don')
                    ->where('id', $hoaDon->id)
                    ->update([
                        'trang_thai' => 'Hoàn thành',
                        'diem_thu_duoc' => $diemThuDuoc,
                        'updated_at' => now(),
                    ]);

                if (!empty($hoaDon->id_khach_hang)) {
                    $diemSuDung = (int) ($hoaDon->diem_su_dung ?? 0);
                    $khachHang = DB::table('khach_hang')->where('id', $hoaDon->id_khach_hang)->first();
                    if ($khachHang) {
                        $diemMoi = (int) $khachHang->diem_tich_luy - $diemSuDung + $diemThuDuoc;
                        DB::table('khach_hang')
                            ->where('id', $khachHang->id)
                            ->update([
                                'diem_tich_luy' => max(0, $diemMoi),
                                'tong_chi_tieu' => (float) $khachHang->tong_chi_tieu + $canTra,
                                'updated_at' => now(),
                            ]);

                        if ($diemSuDung > 0) {
                            DB::table('lich_su_tich_diem')->insert([
                                'id_khach_hang' => $khachHang->id,
                                'id_hoa_don' => $hoaDon->id,
                                'loai_bien_dong' => 'tru',
                                'so_diem' => $diemSuDung,
                                'ly_do' => 'Sử dụng điểm thanh toán (VNPay)',
                                'created_at' => now(),
                            ]);
                        }
                        if ($diemThuDuoc > 0) {
                            DB::table('lich_su_tich_diem')->insert([
                                'id_khach_hang' => $khachHang->id,
                                'id_hoa_don' => $hoaDon->id,
                                'loai_bien_dong' => 'cong',
                                'so_diem' => $diemThuDuoc,
                                'ly_do' => 'Tích điểm từ hóa đơn (VNPay)',
                                'created_at' => now(),
                            ]);
                        }
                    }
                }
            } else {
                DB::table('hoa_don')
                    ->where('id', $hoaDon->id)
                    ->update([
                        'trang_thai' => 'Thanh toán thất bại',
                        'updated_at' => now(),
                    ]);
            }
        });
    }

    protected function truTonKho(int $hoaDonId): void
    {
        $rows = DB::table('chi_tiet_hoa_don')
            ->where('id_hoa_don', $hoaDonId)
            ->get();

        foreach ($rows as $row) {
            if (empty($row->id_bien_the)) {
                Log::warning('VNPay truTonKho: thiếu id_bien_the', [
                    'id_hoa_don' => $hoaDonId,
                    'id_chi_tiet_hoa_don' => $row->id,
                ]);
                continue;
            }

            DB::table('bien_the_san_pham')
                ->where('id', $row->id_bien_the)
                ->decrement('so_luong_ton', (int) $row->so_luong);
        }
    }
}
