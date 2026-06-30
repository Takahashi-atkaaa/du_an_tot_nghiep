<?php

namespace App\Http\Controllers\admin\CaLam;

use App\Http\Controllers\Controller;
use App\Models\ChiaCaLamViec;
use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\DiemDanh;

class DiemDanhNhanVien extends Controller
{
    //
    //Chi tiết lịch sử điểm danh của nhân viên
    public function chi_tiet_diem_danh($id_chia_ca_lam_viec, $id_nv){
         $cham_cong_nhan_vien = DiemDanh::where('id_chia_ca_lam_viec',$id_chia_ca_lam_viec)->firstOrFail();
         $nhan_vien = NguoiDung::findOrFail($id_nv);
         return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.chi-tiet-diem-danh-cua-nhan-vien', compact('cham_cong_nhan_vien', 'nhan_vien'));   
    }

    //chấm công bổ xung cho nhân viên 
    public function diem_danh_bu($id_chia_ca_lam_viec, $id_nv){

        $nhan_vien = NguoiDung::findOrfail($id_nv);
        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.diem-danh-bu', compact('id_nv', 'id_chia_ca_lam_viec', 'nhan_vien'));
    }

    //tạo chấm công bù
    public function luu_diem_danh_bu(Request $request)
    {
        $du_lieu = [
            'id_chia_ca_lam_viec' => $request->id_chia_ca_lam_viec,
            'gio_vao'             => $request->gio_vao,
            'gio_tan_ca'          => $request->gio_tan_ca,
            'so_gio_di_lam_muon'  => $request->so_gio_di_lam_muon,
            'so_gio_lam_them'     => $request->so_gio_lam_them,
            'trang_thai_vao_lam'  => $request->trang_thai_vao_lam,
            'trang_thai_tan_ca'   => $request->trang_thai_tan_ca
        ];

        $diem_danh = \App\Models\DiemDanh::create($du_lieu);

        if ($diem_danh) {
            return redirect()->back()
                ->with('thong_bao', 'Chấm công bù thành công');
        }

        return redirect()->back()
            ->with('thong_bao', 'Chấm công bù thất bại');
    }

    //cập nhật điểm danh 
public function cap_nhat_diem_danh(Request $request, $id)
{
    $diemDanh = DiemDanh::findOrFail($id);

    $diemDanh->update([
        'gio_vao' => $request->gio_vao,
        'gio_tan_ca' => $request->gio_tan_ca,
        'so_gio_di_lam_muon' => $request->so_gio_di_lam_muon,
        'so_gio_lam_them' => $request->so_gio_lam_them,
        'trang_thai_vao_lam' => $request->trang_thai_vao_lam,
        'trang_thai_tan_ca' => $request->trang_thai_tan_ca,
    ]);

    return redirect()->back()->with(
        'thong_bao',
        'Cập nhật thành công.'
    );
}
}
