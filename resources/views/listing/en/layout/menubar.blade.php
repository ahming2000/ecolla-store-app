<nav class="fixed-top navbar navbar-expand-md navbar-light bg-white shadow">
    <div class="container" id="nav-container">

        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('/images/ecolla.png') }}" width="30" height="30"
                 class="d-inline-block align-top" alt="Logo" loading="lazy">
            ecolla
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/item') }}">
                        <i class="bi bi-list"></i> Item List
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/payment-method') }}">
                        <i class="bi bi-wallet"></i> Payment Method
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/cart') }}">
                        <i class="bi bi-cart"></i> Cart
                        <span class="badge bg-danger text-white">{{ session('cart')->count() }}</span>
                    </a>
                </li>

                <li class="nav-item">
                    {{-- TODO: Implement language changing --}}
                    <a class="nav-link"
                       href="{{ url('/lang/' . \App\Enum\Language::$CH . '?redirectTo=' . $_SERVER['REQUEST_URI']) }}">
                        <i class="bi bi-translate"></i> 中文
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
