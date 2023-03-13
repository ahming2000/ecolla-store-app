<nav class="fixed-top navbar navbar-expand-md navbar-light bg-white shadow">
    <div class="container">

        <a class="navbar-brand" href="{{ route('management.dashboard') }}">
            <img src="{{ asset('/images/ecolla.png') }}" width="30" height="30"
                 class="d-inline-block align-top" alt="Logo" loading="lazy">
            e口乐网页管理
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('management.login.index') }}">
                            <i class="bi bi-box-arrow-in-left"></i> 登录
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('management.changingLog.index') }}">
                            <i class="bi bi-journals"></i> 更新日志
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('management.dashboard') }}">
                            <i class="bi bi-house"></i> 主页
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('management.item.index') }}">
                            <i class="bi bi-list-ul"></i> 商品
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('management.order.index') }}">
                            <i class="bi bi-bag-check"></i> 订单
                        </a>
                    </li>


                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->username }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="{{ route('management.profile.index') }}">
                                <i class="bi bi-person"></i> 个人资料
                            </a>

                            @can('admin')
                                <a class="dropdown-item" href="{{ route('management.user.index') }}">
                                    <i class="bi bi-people"></i> 员工账户管理
                                </a>
                            @endcan

                            @can('manager')
                                <a class="dropdown-item" href="{{ route('management.setting.index') }}">
                                    <i class="bi bi-gear"></i> 网页设置
                                </a>
                            @endcan

                            <a class="dropdown-item" href="{{ route('management.changingLog.index') }}">
                                <i class="bi bi-journals"></i> 更新日志
                            </a>

                            <a class="dropdown-item"
                               href="#"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i> 登出
                            </a>

                            <form id="logout-form" action="{{ route('api.logout') }}" method="post" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
