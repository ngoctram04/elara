@extends('layouts.admin')

@section('title', 'Thông tin cá nhân')

@section('content')
<style>
    .profile-page{
        font-size:14px;
        color:#334155;
    }

    .profile-card{
        border-radius:16px;
        overflow:hidden;
        border:1px solid #edf2f7;
    }

    .profile-main-title{
        font-size:18px;
        font-weight:600;
        color:#1e293b;
    }

    .profile-subtext{
        font-size:13px;
        color:#64748b;
    }

    .profile-header{
        display:flex;
        align-items:center;
        gap:24px;
        flex-wrap:wrap;
    }

    .profile-avatar-wrap{
        flex-shrink:0;
        text-align:center;
    }

    .profile-avatar{
        width:130px;
        height:130px;
        object-fit:cover;
        border-radius:50%;
        border:4px solid #fff;
        box-shadow:0 8px 24px rgba(15, 23, 42, 0.08);
    }

    .profile-avatar-placeholder{
        width:130px;
        height:130px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:46px;
        font-weight:700;
        color:#fff;
        background:linear-gradient(135deg, #3b82f6, #2563eb);
        border:4px solid #fff;
        box-shadow:0 8px 24px rgba(15, 23, 42, 0.08);
    }

    .profile-info{
        flex:1;
        min-width:260px;
    }

    .profile-name{
        font-size:22px;
        font-weight:700;
        color:#1e293b;
        margin-bottom:6px;
    }

    .profile-email{
        font-size:14px;
        color:#64748b;
        margin-bottom:14px;
        word-break:break-word;
    }

    .profile-badges{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-bottom:16px;
    }

    .profile-badge{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:7px 12px;
        border-radius:999px;
        font-size:12px;
        font-weight:600;
        border:1px solid transparent;
    }

    .badge-role{
        background:#eff6ff;
        color:#1d4ed8;
        border-color:#dbeafe;
    }

    .badge-verified{
        background:#dcfce7;
        color:#15803d;
        border-color:#bbf7d0;
    }

    .badge-unverified{
        background:#fef3c7;
        color:#b45309;
        border-color:#fde68a;
    }

    .profile-btn{
        font-size:13px;
        font-weight:500;
        border-radius:10px;
        padding:8px 14px;
    }

    .profile-divider{
        margin:22px 0;
        border-color:#e2e8f0;
    }

    .profile-section-title{
        font-size:16px;
        font-weight:600;
        color:#1e293b;
        margin-bottom:14px;
    }

    .profile-info-box{
        background:#f8fafc;
        border:1px solid #e9eef5;
        border-radius:12px;
        padding:15px 16px;
        height:100%;
        transition:all .2s ease;
    }

    .profile-info-box:hover{
        background:#ffffff;
        border-color:#dbe5f0;
    }

    .profile-info-label{
        font-size:12px;
        color:#64748b;
        margin-bottom:5px;
        display:flex;
        align-items:center;
        gap:7px;
    }

    .profile-info-value{
        font-size:14px;
        font-weight:600;
        color:#1e293b;
        word-break:break-word;
        line-height:1.6;
    }

    .profile-muted{
        color:#94a3b8;
        font-weight:500;
    }

    .profile-age{
        font-size:13px;
        font-weight:500;
        color:#64748b;
        margin-left:6px;
    }

    @media (max-width: 768px){
        .profile-header{
            flex-direction:column;
            align-items:center;
            text-align:center;
        }

        .profile-info{
            min-width:100%;
        }

        .profile-badges{
            justify-content:center;
        }

        .profile-name{
            font-size:20px;
        }

        .profile-avatar,
        .profile-avatar-placeholder{
            width:110px;
            height:110px;
        }

        .profile-avatar-placeholder{
            font-size:40px;
        }
    }
</style>

<div class="profile-page">
    <div class="card shadow-sm border-0 profile-card">
        <div class="card-body p-3 p-md-4">

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="profile-main-title mb-1">Thông tin cá nhân</h5>
                    <div class="profile-subtext">Thông tin tài khoản quản trị viên</div>
                </div>
            </div>

            <div class="profile-header">
                <div class="profile-avatar-wrap">
                    @if ($admin->avatar)
                        <img
                            src="{{ asset('storage/' . $admin->avatar) }}"
                            class="profile-avatar"
                            alt="Avatar"
                        >
                    @else
                        <div class="profile-avatar-placeholder">
                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div class="profile-info">
                    <div class="profile-name">{{ $admin->name }}</div>

                    <div class="profile-email">
                        <i class="bi bi-envelope me-1"></i>{{ $admin->email }}
                    </div>

                    <div class="profile-badges">
                        <span class="profile-badge badge-role">
                            <i class="bi bi-shield-lock"></i> Admin
                        </span>

                        @if ($admin->email_verified_at)
                            <span class="profile-badge badge-verified">
                                <i class="bi bi-check-circle"></i> Email đã xác thực
                            </span>
                        @else
                            <span class="profile-badge badge-unverified">
                                <i class="bi bi-exclamation-circle"></i> Email chưa xác thực
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('admin.profile.edit') }}" class="btn btn-outline-primary profile-btn">
                        <i class="bi bi-pencil-square me-1"></i> Chỉnh sửa thông tin
                    </a>
                </div>
            </div>

            <hr class="profile-divider">

            {{-- THÔNG TIN CHI TIẾT --}}
            <h6 class="profile-section-title">Thông tin chi tiết</h6>

            <div class="row g-3">
                {{-- SĐT --}}
                <div class="col-md-6">
                    <div class="profile-info-box">
                        <div class="profile-info-label">
                            <i class="bi bi-telephone"></i> Số điện thoại
                        </div>
                        <div class="profile-info-value">
                            {{ $admin->phone ?: 'Chưa cập nhật' }}
                        </div>
                    </div>
                </div>

                {{-- Ngày tạo --}}
                <div class="col-md-6">
                    <div class="profile-info-box">
                        <div class="profile-info-label">
                            <i class="bi bi-calendar-event"></i> Ngày tạo tài khoản
                        </div>
                        <div class="profile-info-value">
                            {{ $admin->created_at ? $admin->created_at->format('d/m/Y') : 'Chưa cập nhật' }}
                        </div>
                    </div>
                </div>

                {{-- Ngày sinh --}}
                <div class="col-md-6">
                    <div class="profile-info-box">
                        <div class="profile-info-label">
                            <i class="bi bi-cake"></i> Ngày sinh
                        </div>
                        <div class="profile-info-value">
                            @if($admin->date_of_birth)
                                {{ \Carbon\Carbon::parse($admin->date_of_birth)->format('d/m/Y') }}
                                <span class="profile-age">
                                    ({{ \Carbon\Carbon::parse($admin->date_of_birth)->age }} tuổi)
                                </span>
                            @else
                                Chưa cập nhật
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Giới tính --}}
                <div class="col-md-6">
                    <div class="profile-info-box">
                        <div class="profile-info-label">
                            <i class="bi bi-gender-ambiguous"></i> Giới tính
                        </div>
                        <div class="profile-info-value">
                            @if($admin->gender == 'male')
                                Nam
                            @elseif($admin->gender == 'female')
                                Nữ
                            @elseif($admin->gender == 'other')
                                Khác
                            @else
                                Chưa cập nhật
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection