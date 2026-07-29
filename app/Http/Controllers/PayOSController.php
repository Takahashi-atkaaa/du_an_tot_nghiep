<?php

namespace App\Http\Controllers;

use App\Http\Controllers\nhan_vien\NhanVienController;
use App\Models\GiaoDich;
use App\Services\PayOSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayOSController extends Controller
{
    public function __construct(protected PayOSService $payos) {}

    /**
     * Nhân viên POS khởi tạo giao dịch PayOS:
     *   1. Validate giỏ + khách + khuyến mãi + điểm (tái sử dụng helper NhanVienController).
     *   2. Tạo hoa_don trạng thái 'Chờ thanh toán' (chưa trừ kho).
     *   3. Tạo giao_dich trạng thái 'cho_xac_nhan'.
     *   4. Gọi PayOS tạo payment link, trả về checkoutUrl/qrCode + hoa_don_id cho JS popup + polling.
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
        ]);

        /** @var NhanVienController $pos */
        $pos = app(NhanVienController::class);
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
                'phuong_thuc_thanh_toan' => 'PayOS',
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
                'phuong_thuc' => GiaoDich::PHUONG_THUC_PAYOS,
                'so_tien' => $calc['khach_can_tra'],
                'trang_thai' => GiaoDich::TRANG_THAI_CHO_XAC_NHAN,
                'ma_tham_chieu' => (string) $hoaDonId,
                'du_lieu_phan_hoi' => [
                    'created_from' => 'pos',
                    'orderCode' => $hoaDonId,
                ],
            ]);
        });

        $amountVnd = (int) round((float) $calc['khach_can_tra']);
        $description = 'DH '.$hoaDonId;

        try {
            $linkData = $this->payos->createPaymentLink(
                orderCode: $hoaDonId,
                amount: $amountVnd,
                description: $description,
            );
        } catch (\Throwable $e) {
            Log::error('PayOS createPaymentLink exception', [
                'hoa_don_id' => $hoaDonId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo link thanh toán PayOS: '.$e->getMessage(),
            ], 502);
        }

        $existingPayload = GiaoDich::where('id_hoa_don', $hoaDonId)
            ->where('phuong_thuc', GiaoDich::PHUONG_THUC_PAYOS)
            ->orderByDesc('id')
            ->first()
            ->du_lieu_phan_hoi ?? [];

        DB::table('giao_dich')
            ->where('id_hoa_don', $hoaDonId)
            ->where('phuong_thuc', GiaoDich::PHUONG_THUC_PAYOS)
            ->orderByDesc('id')
            ->limit(1)
            ->update([
                'ma_giao_dich_doi_tac' => $linkData['paymentLinkId'] ?? null,
                'du_lieu_phan_hoi' => json_encode(array_merge($existingPayload, [
                    'paymentLinkId' => $linkData['paymentLinkId'] ?? null,
                    'checkoutUrl' => $linkData['checkoutUrl'] ?? null,
                    'qrCode' => $linkData['qrCode'] ?? null,
                    'accountNumber' => $linkData['accountNumber'] ?? null,
                    'bin' => $linkData['bin'] ?? null,
                ])),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'hoa_don_id' => $hoaDonId,
            'orderCode' => $hoaDonId,
            'checkout_url' => $linkData['checkoutUrl'] ?? null,
            'qr_code' => $linkData['qrCode'] ?? null,
            'payment_link_id' => $linkData['paymentLinkId'] ?? null,
            'amount' => $amountVnd,
            'expired_at' => $linkData['expiredAt'] ?? null,
        ]);
    }

    /**
     * Endpoint polling để JS gọi và xem giao_dich đã thành công chưa.
     */
    public function checkStatus(int $hoaDonId): JsonResponse
    {
        $gd = GiaoDich::where('id_hoa_don', $hoaDonId)
            ->where('phuong_thuc', GiaoDich::PHUONG_THUC_PAYOS)
            ->orderByDesc('id')
            ->first();

        if (! $gd) {
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
     * Trang user-side return: PayOS redirect khách về sau khi thanh toán xong.
     * Dùng để hiển thị kết quả + postMessage về POS; webhook là nguồn cập nhật DB chính thức.
     */
    public function return(Request $request)
    {
        $orderCode = $request->query('orderCode') ?? $request->query('id');
        $statusParam = strtoupper((string) $request->query('status', ''));
        $code = (string) $request->query('code', '');

        $verified = false;
        $payload = [
            'orderCode' => $orderCode,
            'status' => $statusParam,
            'code' => $code,
            'cancel' => $statusParam === 'CANCELLED',
        ];

        if ($orderCode !== null) {
            try {
                $data = $this->payos->getPaymentLink($orderCode);
                $payload['amount'] = $data['amount'] ?? null;
                $payload['amountPaid'] = $data['amountPaid'] ?? null;
                $payload['status'] = $data['status'] ?? $statusParam;

                $isSuccess = ($data['status'] ?? '') === 'PAID' && (int) ($data['amountPaid'] ?? 0) > 0;
                $gd = GiaoDich::where('ma_tham_chieu', (string) $orderCode)
                    ->where('phuong_thuc', GiaoDich::PHUONG_THUC_PAYOS)
                    ->orderByDesc('id')
                    ->first();

                if ($gd && $gd->trang_thai !== GiaoDich::TRANG_THAI_THANH_CONG) {
                    $hoaDon = DB::table('hoa_don')->where('id', $gd->id_hoa_don)->first();
                    if ($hoaDon && $hoaDon->trang_thai !== 'Đã hủy') {
                        $payload['processed_via'] = 'return';
                        $this->xuLyKetQuaThanhToan($gd, $hoaDon, $data, $isSuccess);
                    }
                }
                $verified = true;
                $payload['verified'] = true;
            } catch (\Throwable $e) {
                Log::warning('PayOS return lookup failed', [
                    'orderCode' => $orderCode,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $success = ($payload['status'] ?? null) === 'PAID';

        return view('nhan_vien_view.payment.payos-return', [
            'verified' => $verified,
            'success' => $success,
            'payload' => $payload,
        ]);
    }

    /**
     * Trang user-side cancel: PayOS redirect khách về khi khách chọn Hủy.
     */
    public function cancel(Request $request)
    {
        $orderCode = $request->query('orderCode') ?? $request->query('id');
        $payload = [
            'orderCode' => $orderCode,
            'status' => 'CANCELLED',
            'cancel' => true,
        ];

        if ($orderCode !== null) {
            try {
                $this->payos->cancelPaymentLink($orderCode, 'User cancelled at checkout');
            } catch (\Throwable $e) {
                Log::info('PayOS cancelPaymentLink ignore (may already be CANCELLED)', [
                    'orderCode' => $orderCode,
                    'message' => $e->getMessage(),
                ]);
            }

            $gd = GiaoDich::where('ma_tham_chieu', (string) $orderCode)
                ->where('phuong_thuc', GiaoDich::PHUONG_THUC_PAYOS)
                ->orderByDesc('id')
                ->first();

            if ($gd && $gd->trang_thai === GiaoDich::TRANG_THAI_CHO_XAC_NHAN) {
                $hoaDon = DB::table('hoa_don')->where('id', $gd->id_hoa_don)->first();
                if ($hoaDon && $hoaDon->trang_thai !== 'Đã hủy') {
                    $this->xuLyKetQuaThanhToan(
                        $gd,
                        $hoaDon,
                        ['status' => 'CANCELLED', 'amountPaid' => 0],
                        false
                    );
                }
            }
        }

        return view('nhan_vien_view.payment.payos-return', [
            'verified' => true,
            'success' => false,
            'payload' => $payload,
            'cancelled' => true,
        ]);
    }

    /**
     * Server-to-server webhook PayOS. Đây là nguồn cập nhật DB chính thức.
     * Xác minh chữ ký HMAC SHA-256 trước khi cập nhật.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        $signature = (string) ($payload['signature'] ?? '');
        $data = $payload['data'] ?? [];

        if (! is_array($data) || $signature === '' || ! $this->payos->verifyWebhookSignature($data, $signature)) {
            Log::warning('PayOS webhook signature invalid', [
                'has_signature' => $signature !== '',
                'data_keys' => is_array($data) ? array_keys($data) : null,
            ]);

            return response()->json([
                'code' => '97',
                'desc' => 'Invalid signature',
            ], 400);
        }

        $orderCode = (int) ($data['orderCode'] ?? 0);
        $code = (string) ($data['code'] ?? '');
        $amountPaid = (int) ($data['amount'] ?? 0);

        $gd = GiaoDich::where('ma_tham_chieu', (string) $orderCode)
            ->where('phuong_thuc', GiaoDich::PHUONG_THUC_PAYOS)
            ->orderByDesc('id')
            ->first();

        if (! $gd) {
            return response()->json([
                'code' => '01',
                'desc' => 'Order not found',
            ], 404);
        }

        if ($gd->trang_thai === GiaoDich::TRANG_THAI_THANH_CONG) {
            return response()->json([
                'code' => '02',
                'desc' => 'Order already confirmed',
            ]);
        }

        if (abs(((float) $gd->so_tien) - (float) $amountPaid) > 0.01) {
            return response()->json([
                'code' => '04',
                'desc' => 'invalid amount',
            ], 400);
        }

        $hoaDon = DB::table('hoa_don')->where('id', $gd->id_hoa_don)->first();
        if (! $hoaDon || $hoaDon->trang_thai === 'Đã hủy') {
            return response()->json([
                'code' => '02',
                'desc' => 'Order already cancelled',
            ]);
        }

        $isSuccess = $code === '00' && $amountPaid > 0;

        $this->xuLyKetQuaThanhToan($gd, $hoaDon, array_merge($data, [
            'processed_via' => 'webhook',
        ]), $isSuccess);

        Log::info('PayOS webhook processed', [
            'hoa_don_id' => $hoaDon->id,
            'orderCode' => $orderCode,
            'success' => $isSuccess,
        ]);

        return response()->json([
            'code' => '00',
            'desc' => 'Confirm Success',
        ]);
    }

    /**
     * Tách logic cập nhật DB sau khi PayOS trả kết quả thành một method chung.
     * Idempotent: nếu giao_dich đã thanh_cong thì bỏ qua (an toàn khi return + webhook đều gọi).
     */
    protected function xuLyKetQuaThanhToan(GiaoDich $gd, $hoaDon, array $data, bool $isSuccess): void
    {
        if ($gd->trang_thai === GiaoDich::TRANG_THAI_THANH_CONG) {
            return;
        }

        DB::transaction(function () use ($gd, $hoaDon, $data, $isSuccess) {
            $gd->fill([
                'trang_thai' => $isSuccess
                    ? GiaoDich::TRANG_THAI_THANH_CONG
                    : GiaoDich::TRANG_THAI_THAT_BAI,
                'ma_giao_dich_doi_tac' => $data['paymentLinkId'] ?? ($data['reference'] ?? null),
                'ma_phan_hoi' => (string) ($data['code'] ?? ''),
                'trang_thai_doi_tac' => (string) ($data['status'] ?? ''),
                'ma_ngan_hang' => $data['counterAccountBankId'] ?? ($data['bin'] ?? null),
                'ngay_gio_thanh_toan' => $this->parsePayDateTime($data['transactionDateTime'] ?? null),
                'du_lieu_phan_hoi' => array_merge(
                    $gd->du_lieu_phan_hoi ?? [],
                    [
                        'processed_via' => $data['processed_via'] ?? 'unknown',
                        'description' => $data['description'] ?? null,
                        'accountNumber' => $data['accountNumber'] ?? null,
                        'reference' => $data['reference'] ?? null,
                    ]
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

                if (! empty($hoaDon->id_khach_hang)) {
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
                                'ly_do' => 'Sử dụng điểm thanh toán (PayOS)',
                                'created_at' => now(),
                            ]);
                        }
                        if ($diemThuDuoc > 0) {
                            DB::table('lich_su_tich_diem')->insert([
                                'id_khach_hang' => $khachHang->id,
                                'id_hoa_don' => $hoaDon->id,
                                'loai_bien_dong' => 'cong',
                                'so_diem' => $diemThuDuoc,
                                'ly_do' => 'Tích điểm từ hóa đơn (PayOS)',
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
                Log::warning('PayOS truTonKho: thiếu id_bien_the', [
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

    protected function parsePayDateTime(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
