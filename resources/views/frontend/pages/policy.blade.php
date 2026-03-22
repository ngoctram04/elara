@extends('layouts.frontend')

@section('title','Chính sách')

@section('content')
<div class="container py-4">

    <div class="row">

        {{-- SIDEBAR --}}
        <div class="col-md-3 mb-3">
            <div class="policy-sidebar p-3 shadow-sm rounded-4 bg-white">

                <h6 class="fw-bold mb-3 text-primary">
                    <i class="bi bi-info-circle"></i> Chính sách
                </h6>

                <a href="#ship" class="policy-link active">
                    <i class="bi bi-truck"></i> Giao hàng
                </a>

                <a href="#terms" class="policy-link">
                    <i class="bi bi-file-text"></i> Điều khoản
                </a>

                <a href="#rank" class="policy-link">
                    <i class="bi bi-star"></i> Thành viên
                </a>

            </div>
        </div>

        {{-- CONTENT --}}
        <div class="col-md-9">

            {{-- SHIPPING --}}
            <div id="ship" class="policy-card mb-4">
                <div class="policy-header">
                    <i class="bi bi-truck"></i>
                    <span>Chính sách giao hàng</span>
                </div>

                <ul class="policy-list">
                    <li>Thời gian giao hàng từ <b>2 - 5 ngày</b></li>
                    <li>Hỗ trợ thanh toán khi nhận hàng (COD)</li>
                    <li>Được kiểm tra sản phẩm trước khi thanh toán</li>
                    <li>Trả hàng miễn phí khi có vấn đề</li>
                </ul>
            </div>

            {{-- TERMS --}}
            <div id="terms" class="policy-card mb-4">
                <div class="policy-header">
                    <i class="bi bi-file-text"></i>
                    <span>Điều khoản sử dụng</span>
                </div>

                <p>
                    Khi sử dụng hệ thống ELARA, bạn đồng ý tuân thủ các điều khoản sau:
                </p>

                <ul class="policy-list">
                    <li>Không sử dụng website cho mục đích gian lận hoặc vi phạm pháp luật</li>
                    <li>Cung cấp thông tin cá nhân chính xác khi mua hàng</li>
                    <li>Tôn trọng hệ thống và các người dùng khác</li>
                </ul>

                <div class="policy-warning mt-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <b>Cảnh báo:</b><br>
                        Nếu tài khoản thực hiện <b>7 lần hủy đơn trong vòng 7 ngày</b>,
                        hệ thống sẽ <b>tạm khóa tài khoản</b> để hạn chế hành vi đặt hàng không nghiêm túc.
                        <br><br>
                        Việc mở khóa sẽ được xem xét bởi quản trị viên.
                    </div>
                </div>

            </div>

            {{-- MEMBERSHIP --}}
            <div id="rank" class="policy-card">
                <div class="policy-header">
                    <i class="bi bi-star"></i>
                    <span>Chính sách thành viên</span>
                </div>

                <div class="policy-note mb-3">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Hạng thành viên được xét theo <b>tổng chi tiêu trong năm</b>. 
                    Điểm tích lũy vẫn được giữ để <b>đổi voucher / quà tặng</b>.
                </div>

                <div class="table-responsive">
                    <table class="table text-center align-middle mt-3">

                        <thead class="table-light">
                            <tr>
                                <th>Hạng</th>
                                <th>Tổng chi tiêu năm</th>
                                <th>Ưu đãi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>
                                    <span class="badge bg-dark px-3 py-2">
                                        <i class="bi bi-person"></i> Đồng
                                    </span>
                                </td>
                                <td>Dưới 1.000.000đ</td>
                                <td>Không ưu đãi</td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="badge bg-secondary px-3 py-2">
                                        <i class="bi bi-award"></i> Bạc
                                    </span>
                                </td>
                                <td>Từ 1.000.000đ đến dưới 3.000.000đ</td>
                                <td>Giảm 5% vào ngày sinh nhật</td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="badge bg-warning text-dark px-3 py-2">
                                        <i class="bi bi-award-fill"></i> Vàng
                                    </span>
                                </td>
                                <td>Từ 3.000.000đ đến dưới 10.000.000đ</td>
                                <td>
                                    Freeship đơn từ 300.000đ <br>
                                    + Giảm 10% ngày sinh nhật
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="badge bg-info text-dark px-3 py-2">
                                        <i class="bi bi-gem"></i> Bạch kim
                                    </span>
                                </td>
                                <td>Từ 10.000.000đ trở lên</td>
                                <td>
                                    Freeship mọi đơn <br>
                                    + Giảm 15% ngày sinh nhật
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>

                <div class="voucher-note mt-3">
                    <div class="fw-semibold mb-1">
                        <i class="bi bi-ticket-perforated me-1"></i> Điểm tích lũy
                    </div>
                    <div>
                        Điểm tích lũy không dùng để xét hạng thành viên. 
                        Điểm được sử dụng để <b>đổi voucher ưu đãi</b> hoặc các quyền lợi khác theo chương trình của ELARA.
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
@endsection


@push('styles')
<style>
.policy-sidebar{
    position: sticky;
    top: 90px;
}

.policy-link{
    display:block;
    padding:10px 12px;
    border-radius:8px;
    color:#333;
    text-decoration:none;
    margin-bottom:6px;
}

.policy-link i{
    margin-right:6px;
}

.policy-link.active{
    background:#0d6efd;
    color:white;
}

.policy-card{
    background:white;
    padding:20px;
    border-radius:14px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

.policy-header{
    font-weight:600;
    font-size:18px;
    margin-bottom:15px;
    display:flex;
    align-items:center;
    gap:8px;
    color:#0d6efd;
}

.policy-list{
    padding-left:18px;
}

.policy-list li{
    margin-bottom:6px;
}

.policy-warning{
    display:flex;
    gap:10px;
    background:#fff3cd;
    color:#856404;
    padding:12px;
    border-radius:10px;
    border:1px solid #ffeeba;
    font-size:14px;
}

.policy-warning i{
    font-size:20px;
}

.policy-note{
    background:#eef6ff;
    color:#0b5ed7;
    border:1px solid #cfe2ff;
    padding:12px;
    border-radius:10px;
    font-size:14px;
}

.voucher-note{
    background:#f8f9fa;
    border:1px solid #e9ecef;
    padding:12px;
    border-radius:10px;
    font-size:14px;
    color:#495057;
}

.table td, .table th{
    vertical-align:middle;
}
</style>
@endpush


@push('scripts')
<script>
document.querySelectorAll('.policy-link').forEach(link => {
    link.addEventListener('click', function(){
        document.querySelectorAll('.policy-link').forEach(a => a.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
@endpush