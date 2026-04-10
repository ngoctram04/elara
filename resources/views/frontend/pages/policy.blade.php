@extends('layouts.frontend')

@section('title', 'Chính sách')

@section('content')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Chính sách']
]" />

<div class="container policy-page pb-4">
    <div class="row g-3">
        {{-- SIDEBAR --}}
        <div class="col-lg-3">
            <div class="policy-sidebar">
                <div class="policy-sidebar-box">
                    <div class="policy-sidebar-title">
                        <i class="bi bi-shield-check me-2"></i>
                        Chính sách
                    </div>

                    <a href="#ship" class="policy-link active">
                        <i class="bi bi-truck"></i>
                        <span>Giao hàng</span>
                    </a>

                    <a href="#terms" class="policy-link">
                        <i class="bi bi-file-text"></i>
                        <span>Điều khoản</span>
                    </a>

                    <a href="#rank" class="policy-link">
                        <i class="bi bi-star"></i>
                        <span>Thành viên</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- CONTENT --}}
        <div class="col-lg-9">
            {{-- SHIPPING --}}
            <section id="ship" class="policy-card mb-3">
                <div class="policy-card-header">
                    <div class="policy-card-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <h2 class="policy-card-title">Chính sách giao hàng</h2>
                        <div class="policy-card-subtitle">Thông tin vận chuyển, nhận hàng và đổi trả</div>
                    </div>
                </div>

                <ul class="policy-list">
                    <li>Thời gian giao hàng dự kiến từ <b>2 - 5 ngày</b>.</li>
                    <li>Hỗ trợ <b>thanh toán khi nhận hàng (COD)</b>.</li>
                    <li>Khách hàng được <b>kiểm tra sản phẩm</b> trước khi thanh toán.</li>
                    <li>
                        Hỗ trợ <b>đổi trả/hoàn tiền trong vòng 3 ngày</b> kể từ khi nhận hàng
                        nếu sản phẩm bị lỗi, hư hỏng hoặc giao sai sản phẩm.
                    </li>
                </ul>

                <div class="policy-alert warning mt-2">
                    <div class="alert-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">Lưu ý về đổi trả và hoàn tiền</div>
                        <div>
                            Khách hàng chỉ được yêu cầu đổi trả trong vòng <b>3 ngày</b> kể từ khi nhận hàng.
                            <b>Phí vận chuyển khi đổi trả sẽ do khách hàng chịu</b>.
                            Số tiền hoàn lại sẽ được tính sau khi <b>trừ phí vận chuyển</b> phát sinh (nếu có).
                        </div>
                    </div>
                </div>
            </section>

            {{-- TERMS --}}
            <section id="terms" class="policy-card mb-3">
                <div class="policy-card-header">
                    <div class="policy-card-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div>
                        <h2 class="policy-card-title">Điều khoản sử dụng</h2>
                        <div class="policy-card-subtitle">Quy định khi sử dụng hệ thống ELARA</div>
                    </div>
                </div>

                <p class="policy-text">
                    Khi sử dụng hệ thống ELARA, khách hàng đồng ý tuân thủ các điều khoản sau:
                </p>

                <ul class="policy-list">
                    <li>Không sử dụng website cho mục đích gian lận hoặc vi phạm pháp luật.</li>
                    <li>Cung cấp thông tin cá nhân chính xác khi mua hàng.</li>
                    <li>Tôn trọng hệ thống và người dùng khác trong quá trình sử dụng.</li>
                    <li>Khi yêu cầu đổi trả/hoàn tiền, khách hàng cần cung cấp thông tin trung thực để hệ thống xác minh đơn hàng.</li>
                </ul>

                <div class="policy-alert warning mt-2">
                    <div class="alert-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">Lưu ý quan trọng</div>
                        <div>
                            Nếu tài khoản thực hiện <b>7 lần hủy đơn trong vòng 7 ngày</b>,
                            hệ thống có thể <b>tạm khóa tài khoản</b> để hạn chế hành vi đặt hàng không nghiêm túc.
                            Việc mở khóa sẽ do quản trị viên xem xét.
                        </div>
                    </div>
                </div>
            </section>

            {{-- MEMBERSHIP --}}
            <section id="rank" class="policy-card">
                <div class="policy-card-header">
                    <div class="policy-card-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <div>
                        <h2 class="policy-card-title">Chính sách thành viên</h2>
                        <div class="policy-card-subtitle">Xét hạng theo tổng chi tiêu trong năm</div>
                    </div>
                </div>

                <div class="policy-alert info mb-2">
                    <div class="alert-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        Hạng thành viên được xét theo <b>tổng chi tiêu trong năm</b>.
                        Điểm tích lũy được giữ lại để <b>đổi voucher hoặc quà tặng</b>.
                    </div>
                </div>

                <div class="table-responsive membership-table-wrap">
                    <table class="table membership-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Hạng</th>
                                <th>Tổng chi tiêu năm</th>
                                <th>Ưu đãi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="rank-badge bronze">
                                        <i class="bi bi-person"></i> Đồng
                                    </span>
                                </td>
                                <td>Dưới 1.000.000đ</td>
                                <td>Không ưu đãi</td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="rank-badge silver">
                                        <i class="bi bi-award"></i> Bạc
                                    </span>
                                </td>
                                <td>Từ 1.000.000đ đến dưới 3.000.000đ</td>
                                <td>Giảm 5% vào ngày sinh nhật</td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="rank-badge gold">
                                        <i class="bi bi-award-fill"></i> Vàng
                                    </span>
                                </td>
                                <td>Từ 3.000.000đ đến dưới 10.000.000đ</td>
                                <td>
                                    Freeship đơn từ 300.000đ<br>
                                    + Giảm 10% ngày sinh nhật
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="rank-badge diamond">
                                        <i class="bi bi-gem"></i> Kim Cương
                                    </span>
                                </td>
                                <td>Từ 10.000.000đ trở lên</td>
                                <td>
                                    Freeship mọi đơn<br>
                                    + Giảm 15% ngày sinh nhật
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="policy-note-box mt-2">
                    <div class="note-title">
                        <i class="bi bi-ticket-perforated me-1"></i>
                        Điểm tích lũy
                    </div>
                    <div class="note-text">
                        Điểm tích lũy không dùng để xét hạng thành viên.
                        Điểm được sử dụng để <b>đổi voucher ưu đãi</b> hoặc các quyền lợi khác theo chương trình của ELARA.
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .policy-page{
        padding-top:0 !important;
    }

    .policy-sidebar{
        position:sticky;
        top:90px;
    }

    .policy-sidebar-box{
        background:#fff;
        border:1px solid #eef2f7;
        border-radius:14px;
        padding:14px;
        box-shadow:0 6px 18px rgba(15, 23, 42, 0.05);
    }

    .policy-sidebar-title{
        display:flex;
        align-items:center;
        font-size:15px;
        font-weight:700;
        color:#111827;
        margin-bottom:10px;
    }

    .policy-link{
        display:flex;
        align-items:center;
        gap:10px;
        padding:9px 11px;
        border-radius:10px;
        color:#4b5563;
        text-decoration:none;
        margin-bottom:6px;
        font-size:14px;
        font-weight:500;
        transition:all .2s ease;
    }

    .policy-link i{
        font-size:15px;
        color:#6b7280;
        transition:all .2s ease;
    }

    .policy-link:hover{
        background:#eef5ff;
        color:#0d6efd;
    }

    .policy-link:hover i{
        color:#0d6efd;
    }

    .policy-link.active{
        background:linear-gradient(135deg, #0d6efd, #0b5ed7);
        color:#fff;
        box-shadow:0 6px 14px rgba(13,110,253,0.22);
    }

    .policy-link.active i{
        color:#fff;
    }

    .policy-card{
        background:#fff;
        border:1px solid #eef2f7;
        border-radius:16px;
        padding:18px 18px 16px;
        box-shadow:0 8px 22px rgba(15, 23, 42, 0.05);
    }

    .policy-card-header{
        display:flex;
        align-items:center;
        gap:12px;
        margin-bottom:12px;
    }

    .policy-card-icon{
        width:40px;
        height:40px;
        border-radius:12px;
        background:#eff6ff;
        color:#2563eb;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:18px;
        flex-shrink:0;
    }

    .policy-card-title{
        font-size:18px;
        font-weight:700;
        color:#111827;
        margin:0 0 2px;
        line-height:1.3;
    }

    .policy-card-subtitle{
        font-size:13px;
        color:#6b7280;
        line-height:1.4;
    }

    .policy-text{
        color:#4b5563;
        font-size:14px;
        line-height:1.65;
        margin-bottom:8px;
    }

    .policy-list{
        margin:0;
        padding-left:18px;
        color:#374151;
    }

    .policy-list li{
        margin-bottom:6px;
        line-height:1.6;
        font-size:14px;
    }

    .policy-alert{
        display:flex;
        gap:10px;
        border-radius:12px;
        padding:12px 14px;
        font-size:13.5px;
        line-height:1.6;
    }

    .policy-alert .alert-icon{
        font-size:17px;
        flex-shrink:0;
        margin-top:1px;
    }

    .policy-alert .alert-title{
        font-weight:700;
        margin-bottom:2px;
    }

    .policy-alert.warning{
        background:#fff8e1;
        border:1px solid #fde68a;
        color:#92400e;
    }

    .policy-alert.info{
        background:#eef6ff;
        border:1px solid #bfdbfe;
        color:#1d4ed8;
    }

    .membership-table-wrap{
        border:1px solid #eef2f7;
        border-radius:12px;
        overflow:hidden;
    }

    .membership-table thead th{
        background:#f8fafc;
        color:#111827;
        font-weight:700;
        border-bottom:1px solid #e5e7eb;
        padding:11px 12px;
        font-size:14px;
        white-space:nowrap;
    }

    .membership-table tbody td{
        padding:11px 12px;
        color:#374151;
        vertical-align:middle;
        font-size:14px;
        line-height:1.5;
    }

    .membership-table tbody tr:hover{
        background:#f8fbff;
    }

    .rank-badge{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
    }

    .rank-badge.bronze{
        background:#111827;
        color:#fff;
    }

    .rank-badge.silver{
        background:#e5e7eb;
        color:#374151;
    }

    .rank-badge.gold{
        background:#fef3c7;
        color:#92400e;
    }

    .rank-badge.diamond{
        background:#dbeafe;
        color:#1d4ed8;
    }

    .policy-note-box{
        background:#f8fafc;
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:12px 14px;
    }

    .note-title{
        font-weight:700;
        color:#111827;
        margin-bottom:4px;
        font-size:14px;
    }

    .note-text{
        color:#4b5563;
        font-size:13.5px;
        line-height:1.6;
    }

    @media (max-width: 991.98px){
        .policy-sidebar{
            position:static;
        }
    }

    @media (max-width: 767.98px){
        .policy-card{
            padding:15px;
            border-radius:14px;
        }

        .policy-card-header{
            align-items:flex-start;
            margin-bottom:10px;
        }

        .policy-card-title{
            font-size:16px;
        }

        .policy-card-subtitle,
        .policy-text,
        .policy-list li,
        .membership-table thead th,
        .membership-table tbody td,
        .note-text{
            font-size:13px;
        }

        .policy-sidebar-box{
            border-radius:14px;
            padding:12px;
        }

        .policy-link{
            padding:8px 10px;
            font-size:13px;
        }

        .membership-table thead th,
        .membership-table tbody td{
            padding:10px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const links = document.querySelectorAll('.policy-link');
    const sections = document.querySelectorAll('.policy-card');

    links.forEach(link => {
        link.addEventListener('click', function () {
            links.forEach(item => item.classList.remove('active'));
            this.classList.add('active');
        });
    });

    window.addEventListener('scroll', function () {
        let current = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop - 120;
            if (window.scrollY >= sectionTop) {
                current = section.getAttribute('id');
            }
        });

        if (current) {
            links.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        }
    });
});
</script>
@endpush