<div class="dropdown user-menu">

    {{-- TOGGLE --}}
    <a href="#"
       class="user-toggle"
       data-bs-toggle="dropdown"
       aria-expanded="false">

        <i class="bi bi-person-circle"></i>
        <span class="user-name">
            {{ auth()->user()->name }}
        </span>
        <i class="bi bi-chevron-down user-caret"></i>
    </a>

    {{-- MENU --}}
    <ul class="dropdown-menu dropdown-menu-end shadow-sm user-dropdown">
        <li>
            <a class="dropdown-item" href="{{ route('profile.index') }}">
                <i class="bi bi-person"></i>
                <span>Tài khoản của tôi</span>
            </a>
        </li>

        <li>
            <a class="dropdown-item" href="{{ route('profile.orders') }}">
                <i class="bi bi-box-seam"></i>
                <span>Danh sách đơn hàng</span>
            </a>
        </li>

        <li>
            <a class="dropdown-item" href="{{ route('profile.address') }}">
                <i class="bi bi-geo-alt"></i>
                <span>Danh sách địa chỉ</span>
            </a>
        </li>

        <li><hr class="dropdown-divider"></li>

        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Đăng xuất</span>
                </button>
            </form>
        </li>
    </ul>

</div>
