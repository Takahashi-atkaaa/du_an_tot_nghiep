<table>
    <thead>
        <tr>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">STT</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Mã vạch</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Mã hàng</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold; text-align: center; border: 1px solid black;">Số lượng xuất</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: center; border: 1px solid black;">1</td>
            <td style="text-align: left; border: 1px solid black;">VB00123456</td>
            <td style="text-align: left; border: 1px solid black;">MH001</td>
            <td style="text-align: right; border: 1px solid black;">10</td>
        </tr>
        <tr>
            <td style="text-align: center; border: 1px solid black;">2</td>
            <td style="text-align: left; border: 1px solid black;">VB00123457</td>
            <td style="text-align: left; border: 1px solid black;"></td>
            <td style="text-align: right; border: 1px solid black;">25</td>
        </tr>
    </tbody>
</table>
<p style="margin-top: 10px; font-size: 11px; color: #666;">
    <strong>Hướng dẫn:</strong><br>
    - Cột <strong>Mã vạch</strong> hoặc <strong>Mã hàng</strong>: Bắt buộc nhập ít nhất một trong hai cột để xác định sản phẩm.<br>
    - Cột <strong>Số lượng xuất</strong>: Số nguyên dương, bắt buộc.<br>
    - Hệ thống sẽ tự động trừ kho theo nguyên tắc <strong>FIFO</strong> (lô hàng có hạn sử dụng gần nhất xuất trước).<br>
    - Nếu tổng tồn kho của một sản phẩm không đủ, phiếu xuất sẽ bị hủy và báo lỗi.<br>
    - Các dòng trống sẽ bị bỏ qua khi import.
</p>
