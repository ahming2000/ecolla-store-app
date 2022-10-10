<nav class="fixed-top navbar navbar-expand-md navbar-light bg-white shadow">
    <div class="container" id="nav-container">

        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('/images/ecolla.png') }}" width="30" height="30"
                 class="d-inline-block align-top" alt="Logo" loading="lazy">
            @if(session('lang') == 'en')
                Ecolla
            @else
                e口乐
            @endif
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/') }}">
                        <i class="bi bi-house"></i>
                        @if(session('lang') == 'en')
                            Home
                        @else
                            主页
                        @endif
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/item') }}">
                        <i class="bi bi-list"></i>
                        @if(session('lang') == 'en')
                            Item List
                        @else
                            商品列表
                        @endif
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/payment-method') }}">
                        <i class="bi bi-wallet"></i>
                        @if(session('lang') == 'en')
                            Payment Method
                        @else
                            付款方式
                        @endif
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/cart') }}">
                        <i class="bi bi-cart"></i>
                        @if(session('lang') == 'en')
                            Cart
                        @else
                            购物车
                        @endif
                        <span class="badge bg-danger text-white" id="cart-count">{{ session('cart')->count() }}</span>
                    </a>
                </li>

                <li class="nav-item">
                    @if(session('lang') == 'en')
                        <a class="nav-link"
                           href="{{ url('/lang/ch' . '?redirectTo=' . $_SERVER['REQUEST_URI']) }}">
                            <i class="bi bi-translate"></i> 中文
                        </a>
                    @else
                        <a class="nav-link"
                           href="{{ url('/lang/en' . '?redirectTo=' . $_SERVER['REQUEST_URI']) }}">
                            <i class="bi bi-translate"></i> English
                        </a>
                    @endif
                </li>
            </ul>
        </div>
    </div>
</nav>
