<?php

namespace App\Http\Controllers\BanHang;

use App\Http\Controllers\Controller;
use App\Models\GiaoDich;
use App\Services\PayOSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayOS\Exceptions\APIException;
use PayOS\Models\Webhooks\WebhookData;

class PayOSController extends Controller
{
    public function __construct(private readonly PayOSService $payOS)
    {
    }

    public function createPayment(Request $request): JsonResponse
    {
        $request->validate([
            'hoa_don_id' => 'required|integer|exists:hoa_don,id',
        ]);

        $hoaDon = DB::table('hoa_don')->where('id', $request->hoa_don_id)->first();

        if (!$hoaDon) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy hóa đơn.'], 404);
        }

        if ($hoaDon->trang_thai !== 'Chờ thanh toán') {
            return response()->json([
                'success' => false,
                'message' => 'Hóa đơn không ở trạng thái chờ thanh toán (hiện tại: '.$hoaDon->trang_thai.').',
            ], 422);
        }

        $existing = GiaoDich::where('id_hoa_don', $hoaDon->id)
            ->where('phuong_thuc', 'payos')
            ->where('trang_thai', 'cho_xac_nhan')
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'reuse' => true,
                'checkout_url' => $existing->du_lieu_phan_hoi['checkout_url'] ?? null,
                'order_code' => $existing->ma_tham_chieu,
                'giao_dich_id' => $existing->id,
            ]);
        }

        $khachHang = $hoaDon->id_khach_hang
            ? DB::table('khach_hang')->where('id', $hoaDon->id_khach_hang)->first()
            : null;

        $orderCode = $this->payOS->buildOrderCode((int) $hoaDon->id);
        $amount = (int) round((float) $hoaDon->khach_can_tra);
        $description = 'Hoa don #' . $hoaDon->id;

        try {
            $response = $this->payOS->createPaymentLink(
                orderCode: $orderCode,
                amount: $amount,
                description: $description,
                buyerName: $khachHang->ten_khach_hang ?? null,
                buyerPhone: $khachHang->so_dien_thoai ?? null,
                buyerEmail: $khachHang->email ?? null,
            );
        } catch (APIException $e) {
            Log::error('PayOS createPaymentLink failed', [
                'hoa_don_id' => $hoaDon->id,
                'order_code' => $orderCode,
                'message' => $e->getMessage(),
                'code' => $e->errorCode,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'PayOS từ chối tạo link: ' . $e->getMessage(),
            ], 502);
        } catch (\Throwable $e) {
            Log::error('PayOS createPaymentLink unexpected error', [
                'hoa_don_id' => $hoaDon->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể kết nối PayOS: ' . $e->getMessage(),
            ], 500);
        }

        $giaoDich = GiaoDich::create([
            'id_hoa_don' => $hoaDon->id,
            'phuong_thuc' => 'payos',
            'so_tien' => $amount,
            'trang_thai' => 'cho_xac_nhan',
            'ma_tham_chieu' => (string) $response->orderCode,
            'ma_giao_dich_doi_tac' => $response->paymentLinkId,
            'ma_phan_hoi' => $response->status->value,
            'trang_thai_doi_tac' => $response->status->value,
            'du_lieu_phan_hoi' => [
                'checkout_url' => $response->checkoutUrl,
                'qr_code' => $response->qrCode,
                'payment_link_id' => $response->paymentLinkId,
                'bin' => $response->bin,
                'account_number' => $response->accountNumber,
                'account_name' => $response->accountName,
                'amount' => $response->amount,
                'description' => $response->description,
            ],
        ]);

        return response()->json([
            'success' => true,
            'checkout_url' => $response->checkoutUrl,
            'order_code' => $response->orderCode,
            'giao_dich_id' => $giaoDich->id,
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        if ($request->isMethod('get')) {
            return response()->json(['message' => 'Webhook URL is working!'], 200);
        }

        $payload = $request->all();

        try {
            $webhook = $this->payOS->verifyWebhook($payload);
        } catch (\Throwable $e) {
            Log::warning('PayOS webhook verify failed (returning 200 to keep URL accepted by PayOS)', ['message' => $e->getMessage()]);
            return response()->json(['success' => true]);
        }

        $orderCode = (string) $webhook->orderCode;
        $giaoDich = GiaoDich::where('ma_tham_chieu', $orderCode)->first();

        if (!$giaoDich) {
            Log::warning('PayOS webhook: giao_dich not found - returning 200 to keep URL accepted', ['orderCode' => $orderCode]);
            return response()->json(['success' => true]);
        }

        Log::info('PayOS webhook received', ['payload' => $payload]);

        if ($giaoDich->trang_thai === 'thanh_cong') {
            return response()->json(['success' => true, 'message' => 'Already processed']);
        }

        $trangThai = $this->mapPayOSStatus($webhook->code, $webhook->desc);

        $giaoDich->update([
            'trang_thai' => $trangThai,
            'ma_giao_dich_doi_tac' => $webhook->reference,
            'ma_phan_hoi' => $webhook->code,
            'trang_thai_doi_tac' => $webhook->desc,
            'ma_ngan_hang' => $webhook->counterAccountBankId ?? $webhook->counterAccountBankName,
            'ngay_gio_thanh_toan' => $webhook->transactionDateTime,
            'du_lieu_phan_hoi' => array_merge($giaoDich->du_lieu_phan_hoi ?? [], [
                'webhook' => (array) $webhook,
            ]),
        ]);

        if ($trangThai === 'thanh_cong') {
            $this->completeHoaDon($giaoDich);
        } elseif (in_array($trangThai, ['that_bai', 'hoan_tien'], true)) {
            $this->failHoaDon($giaoDich);
        }

        return response()->json(['success' => true]);
    }

    public function return(Request $request)
    {
        $orderCode = $request->query('orderCode') ?? $request->query('order_code');
        $code = $request->query('code');
        $status = $request->query('status');

        $giaoDich = null;
        if ($orderCode) {
            $giaoDich = GiaoDich::where('ma_tham_chieu', (string) $orderCode)->first();
        }

        if ($giaoDich && $giaoDich->trang_thai === 'cho_xac_nhan') {
            $this->syncFromPayOSApi($giaoDich, $orderCode);
            $giaoDich = $giaoDich->refresh();
        }

        return view('ban_hang.payos.return', [
            'giaoDich' => $giaoDich,
            'orderCode' => $orderCode,
            'code' => $code,
            'status' => $status,
        ]);
    }

    private function syncFromPayOSApi(GiaoDich $giaoDich, string|int $orderCode): void
    {
        try {
            $info = $this->payOS->getPaymentInfo($orderCode);
        } catch (\Throwable $e) {
            return;
        }

        $statusValue = is_object($info) && isset($info->status) ? $info->status : null;
        $trangThai = $this->mapPayOSApiStatus($statusValue);

        $giaoDich->update([
            'trang_thai' => $trangThai,
            'ma_phan_hoi' => $statusValue instanceof \BackedEnum ? $statusValue->value : (string) $statusValue,
            'trang_thai_doi_tac' => $statusValue instanceof \BackedEnum ? $statusValue->value : (string) $statusValue,
            'du_lieu_phan_hoi' => array_merge($giaoDich->du_lieu_phan_hoi ?? [], [
                'return_sync' => $info instanceof \JsonSerializable ? $info->jsonSerialize() : (array) $info,
            ]),
        ]);

        if ($trangThai === 'thanh_cong') {
            $this->completeHoaDon($giaoDich);
        } elseif (in_array($trangThai, ['that_bai', 'hoan_tien'], true)) {
            $this->failHoaDon($giaoDich);
        }
    }

    private function mapPayOSApiStatus(mixed $status): string
    {
        if ($status instanceof \BackedEnum) {
            return match ($status) {
                \PayOS\Models\V2\PaymentRequests\PaymentLinkStatus::PAID,
                \PayOS\Models\V2\PaymentRequests\PaymentLinkStatus::PROCESSING => 'thanh_cong',
                \PayOS\Models\V2\PaymentRequests\PaymentLinkStatus::CANCELLED,
                \PayOS\Models\V2\PaymentRequests\PaymentLinkStatus::FAILED,
                \PayOS\Models\V2\PaymentRequests\PaymentLinkStatus::EXPIRED,
                \PayOS\Models\V2\PaymentRequests\PaymentLinkStatus::UNDERPAID => 'that_bai',
                \PayOS\Models\V2\PaymentRequests\PaymentLinkStatus::PENDING => 'cho_xac_nhan',
                default => 'cho_xac_nhan',
            };
        }

        $status = strtoupper((string) $status);

        return match ($status) {
            'PAID', 'SUCCESS', 'COMPLETED', 'PROCESSING' => 'thanh_cong',
            'CANCELLED', 'CANCELED', 'FAILED', 'EXPIRED', 'UNDERPAID', 'REFUNDED' => 'that_bai',
            default => 'cho_xac_nhan',
        };
    }

    public function cancel(Request $request)
    {
        $orderCode = $request->query('orderCode') ?? $request->query('order_code');

        $giaoDich = null;
        if ($orderCode) {
            $giaoDich = GiaoDich::where('ma_tham_chieu', (string) $orderCode)->first();

            if ($giaoDich && $giaoDich->trang_thai === 'cho_xac_nhan') {
                $giaoDich->update([
                    'trang_thai' => 'that_bai',
                    'ma_phan_hoi' => 'USER_CANCELLED',
                    'trang_thai_doi_tac' => 'CANCELLED',
                ]);
                $this->failHoaDon($giaoDich);
            }
        }

        return view('ban_hang.payos.cancel', [
            'giaoDich' => $giaoDich,
            'orderCode' => $orderCode,
        ]);
    }

    private function mapPayOSStatus(string $code, string $desc): string
    {
        if ($code === '00') {
            return 'thanh_cong';
        }
        if (in_array($code, ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15'], true)) {
            return 'that_bai';
        }
        if (stripos($desc, 'cancel') !== false) {
            return 'that_bai';
        }
        return 'cho_xac_nhan';
    }

    private function completeHoaDon(GiaoDich $giaoDich): void
    {
        DB::transaction(function () use ($giaoDich) {
            $hoaDon = DB::table('hoa_don')->where('id', $giaoDich->id_hoa_don)->lockForUpdate()->first();

            if (!$hoaDon || $hoaDon->trang_thai === 'Hoàn thành') {
                return;
            }

            $chiTiets = DB::table('chi_tiet_hoa_don')->where('id_hoa_don', $hoaDon->id)->get();

            foreach ($chiTiets as $ct) {
                if ($ct->id_chi_tiet_phieu) {
                    DB::table('bien_the_san_pham')
                        ->where('id', $ct->id_chi_tiet_phieu)
                        ->decrement('so_luong_ton', $ct->so_luong);
                } elseif ($ct->id_san_pham) {
                    DB::table('san_pham')
                        ->where('id', $ct->id_san_pham)
                        ->decrement('so_luong_ton_kho', $ct->so_luong);
                }
            }

            if ($hoaDon->id_khach_hang) {
                $diemThuDuoc = (int) $hoaDon->diem_thu_duoc;
                if ($diemThuDuoc > 0) {
                    DB::table('khach_hang')
                        ->where('id', $hoaDon->id_khach_hang)
                        ->increment('diem_tich_luy', $diemThuDuoc);

                    DB::table('khach_hang')
                        ->where('id', $hoaDon->id_khach_hang)
                        ->increment('tong_chi_tieu', $hoaDon->khach_can_tra);

                    DB::table('lich_su_tich_diem')->insert([
                        'id_khach_hang' => $hoaDon->id_khach_hang,
                        'id_hoa_don' => $hoaDon->id,
                        'loai_bien_dong' => 'cong',
                        'so_diem' => $diemThuDuoc,
                        'ly_do' => 'Tích điểm từ hóa đơn (PayOS)',
                        'created_at' => now(),
                    ]);
                }
            }

            DB::table('hoa_don')
                ->where('id', $hoaDon->id)
                ->update([
                    'trang_thai' => 'Hoàn thành',
                    'updated_at' => now(),
                ]);
        });
    }

    private function failHoaDon(GiaoDich $giaoDich): void
    {
        DB::table('hoa_don')
            ->where('id', $giaoDich->id_hoa_don)
            ->where('trang_thai', 'Chờ thanh toán')
            ->update([
                'trang_thai' => 'Đã hủy',
                'updated_at' => now(),
            ]);
    }
}
