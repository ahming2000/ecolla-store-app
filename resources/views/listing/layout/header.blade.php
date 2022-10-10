@if(session('lang') == 'en')
    <div class="header">
        Welcome to
        <div class="header-highlighted-text">Ecolla</div>
        Snack Shop

        <br>

        <a href="{{ url('/item') }}" class="btn btn-success">
            <i class="bi bi-arrow-right-circle"></i>
            Browse Products Now
        </a>
    </div>
@else
    <div class="header">
        欢迎来到
        <span class="header-highlighted-text">Ecolla e口乐</span>
        零食店

        <br>

        <a href="{{ url('/item') }}" class="btn btn-success">
            <i class="bi bi-arrow-right-circle"></i>
            点击浏览商品
        </a>
    </div>
@endif


