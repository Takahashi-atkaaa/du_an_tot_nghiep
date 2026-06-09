@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Bán hàng - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Bán hàng</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Bán hàng</li>
            </ol>
        </nav>
    </div>
    <div>
        <span class="badge bg-success fs-6"><i class="fas fa-circle me-1"></i>Đang mở ca</span>
        <span class="ms-2 text-muted">Ca: Sáng | 08:00 - 16:00</span>
    </div>
</div>

<div class="row g-4">
    <!-- Left: Product List -->
    <div class="col-lg-8">
        <!-- Search & Filter -->
        <div class="card table-admin mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control" placeholder="Tìm sản phẩm..." id="searchProduct">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="categoryFilter">
                            <option value="">Tất cả danh mục</option>
                            <option>Thực phẩm</option>
                            <option>Đồ uống</option>
                            <option>Bánh kẹo</option>
                            <option>Mì gói</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="card table-admin">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-th-large me-2"></i>Danh sách sản phẩm</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card border rounded p-2 text-center cursor-pointer hover-shadow" onclick="addToCart('SP001', 'Sữa tươi Vinamilk 180ml', 8500)">
                            <img src="https://via.placeholder.com/80" class="rounded mb-2" alt="Product" style="width: 80px; height: 80px; object-fit: cover;">
                            <h6 class="mb-1" style="font-size: 13px;">Sữa tươi Vinamilk</h6>
                            <p class="text-primary fw-bold mb-0">8,500 đ</p>
                            <span class="badge bg-success mt-1" style="font-size: 10px;">Còn 250</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card border rounded p-2 text-center cursor-pointer hover-shadow" onclick="addToCart('SP002', 'Bánh Oreo 133g', 22000)">
                            <img src="https://via.placeholder.com/80" class="rounded mb-2" alt="Product" style="width: 80px; height: 80px; object-fit: cover;">
                            <h6 class="mb-1" style="font-size: 13px;">Bánh Oreo 133g</h6>
                            <p class="text-primary fw-bold mb-0">22,000 đ</p>
                            <span class="badge bg-success mt-1" style="font-size: 10px;">Còn 180</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card border rounded p-2 text-center cursor-pointer hover-shadow" onclick="addToCart('SP003', 'Mì Hảo Tấm gói', 7000)">
                            <img src="https://via.placeholder.com/80" class="rounded mb-2" alt="Product" style="width: 80px; height: 80px; object-fit: cover;">
                            <h6 class="mb-1" style="font-size: 13px;">Mì Hảo Tấm gói</h6>
                            <p class="text-primary fw-bold mb-0">7,000 đ</p>
                            <span class="badge bg-warning mt-1" style="font-size: 10px;">Còn 45</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card border rounded p-2 text-center cursor-pointer hover-shadow" onclick="addToCart('SP004', 'Coca Cola 330ml', 12000)">
                            <img src="https://via.placeholder.com/80" class="rounded mb-2" alt="Product" style="width: 80px; height: 80px; object-fit: cover;">
                            <h6 class="mb-1" style="font-size: 13px;">Coca Cola 330ml</h6>
                            <p class="text-primary fw-bold mb-0">12,000 đ</p>
                            <span class="badge bg-danger mt-1" style="font-size: 10px;">Hết hàng</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card border rounded p-2 text-center cursor-pointer hover-shadow" onclick="addToCart('SP005', 'Cà phê G7 3in1', 35000)">
                            <img src="https://via.placeholder.com/80" class="rounded mb-2" alt="Product" style="width: 80px; height: 80px; object-fit: cover;">
                            <h6 class="mb-1" style="font-size: 13px;">Cà phê G7 3in1</h6>
                            <p class="text-primary fw-bold mb-0">35,000 đ</p>
                            <span class="badge bg-success mt-1" style="font-size: 10px;">Còn 120</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card border rounded p-2 text-center cursor-pointer hover-shadow" onclick="addToCart('SP006', 'Bia Tiger lon', 18000)">
                            <img src="https://via.placeholder.com/80" class="rounded mb-2" alt="Product" style="width: 80px; height: 80px; object-fit: cover;">
                            <h6 class="mb-1" style="font-size: 13px;">Bia Tiger lon</h6>
                            <p class="text-primary fw-bold mb-0">18,000 đ</p>
                            <span class="badge bg-success mt-1" style="font-size: 10px;">Còn 96</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card border rounded p-2 text-center cursor-pointer hover-shadow" onclick="addToCart('SP007', 'Bánh Choco Pie', 12000)">
                            <img src="https://via.placeholder.com/80" class="rounded mb-2" alt="Product" style="width: 80px; height: 80px; object-fit: cover;">
                            <h6 class="mb-1" style="font-size: 13px;">Bánh Choco Pie</h6>
                            <p class="text-primary fw-bold mb-0">12,000 đ</p>
                            <span class="badge bg-success mt-1" style="font-size: 10px;">Còn 150</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card border rounded p-2 text-center cursor-pointer hover-shadow" onclick="addToCart('SP008', 'Sữa Milo 180ml', 10000)">
                            <img src="https://via.placeholder.com/80" class="rounded mb-2" alt="Product" style="width: 80px; height: 80px; object-fit: cover;">
                            <h6 class="mb-1" style="font-size: 13px;">Sữa Milo 180ml</h6>
                            <p class="text-primary fw-bold mb-0">10,000 đ</p>
                            <span class="badge bg-success mt-1" style="font-size: 10px;">Còn 200</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Cart -->
    <div class="col-lg-4">
        <div class="card table-admin sticky-top" style="top: 20px;">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Giỏ hàng</h5>
                    <button class="btn btn-sm btn-light" onclick="clearCart()">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Customer Info -->
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-user me-2"></i>Khách hàng</span>
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option>Khách lẻ</option>
                            <option>Nguyễn Văn Minh</option>
                            <option>Trần Thị Lan</option>
                        </select>
                    </div>
                </div>
                
                <!-- Cart Items -->
                <div class="cart-items" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>SP</th>
                                <th>SL</th>
                                <th>Đ.Giá</th>
                                <th>T.Tiền</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cartItems">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-shopping-basket fa-2x mb-2"></i>
                                    <p class="mb-0">Giỏ hàng trống</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <!-- Discount -->
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Giảm giá:</span>
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <input type="text" class="form-control text-end" value="0" id="discount">
                        <span class="input-group-text">đ</span>
                    </div>
                </div>
                
                <!-- Total -->
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold">Tổng cộng:</span>
                    <span class="fw-bold text-primary fs-5" id="totalAmount">0 đ</span>
                </div>
                
                <!-- Payment Method -->
                <div class="mb-3">
                    <label class="form-label small text-muted">Thanh toán:</label>
                    <div class="btn-group w-100">
                        <button class="btn btn-outline-primary active">Tiền mặt</button>
                        <button class="btn btn-outline-primary">Chuyển khoản</button>
                        <button class="btn btn-outline-primary">Quẹt thẻ</button>
                    </div>
                </div>
                
                <!-- Customer Payment -->
                <div class="mb-3">
                    <label class="form-label small text-muted">Khách thanh toán:</label>
                    <input type="text" class="form-control text-end fw-bold" id="customerPayment" placeholder="0" oninput="calculateChange()">
                </div>
                
                <!-- Change -->
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Tiền thừa:</span>
                    <span class="fw-bold text-success" id="changeAmount">0 đ</span>
                </div>
                
                <!-- Actions -->
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" onclick="createInvoice()">
                        <i class="fas fa-check-circle me-2"></i>Thanh toán
                    </button>
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-pause me-2"></i>Tạm dừng
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    transition: all 0.2s;
    cursor: pointer;
}
.product-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}
.hover-shadow:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

@section('scripts')
<script>
let cart = [];
let total = 0;

function addToCart(code, name, price) {
    const existing = cart.find(item => item.code === code);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ code, name, price, quantity: 1 });
    }
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cartItems');
    if (cart.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="fas fa-shopping-basket fa-2x mb-2"></i>
                    <p class="mb-0">Giỏ hàng trống</p>
                </td>
            </tr>
        `;
        document.getElementById('totalAmount').textContent = '0 đ';
        return;
    }
    
    let html = '';
    total = 0;
    cart.forEach((item, index) => {
        const subtotal = item.price * item.quantity;
        total += subtotal;
        html += `
            <tr>
                <td><small>${item.code}</small></td>
                <td>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="updateQuantity(${index}, -1)">-</button>
                        <span class="mx-1">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="updateQuantity(${index}, 1)">+</button>
                    </div>
                </td>
                <td><small>${formatCurrency(item.price)}</small></td>
                <td><strong>${formatCurrency(subtotal)}</strong></td>
                <td>
                    <button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItem(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
    document.getElementById('totalAmount').textContent = formatCurrency(total);
}

function updateQuantity(index, change) {
    cart[index].quantity += change;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    renderCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

function clearCart() {
    cart = [];
    renderCart();
}

function calculateChange() {
    const payment = parseInt(document.getElementById('customerPayment').value) || 0;
    const discount = parseInt(document.getElementById('discount').value) || 0;
    const change = payment - (total - discount);
    document.getElementById('changeAmount').textContent = formatCurrency(Math.max(0, change));
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
}

function createInvoice() {
    if (cart.length === 0) {
        alert('Vui lòng thêm sản phẩm vào giỏ hàng!');
        return;
    }
    alert('Tạo hóa đơn thành công!');
    clearCart();
}
</script>
@endsection
@endsection
