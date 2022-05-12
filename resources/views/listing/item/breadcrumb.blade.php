<nav style="--bs-breadcrumb-divider: '/';" aria-label="breadcrumb">
    <ol class="breadcrumb bg-white p-2 px-3" style="border-radius: 10px;">
        <li class="breadcrumb-item">
            <a href="{{ url('/item') }}">
                <i class="bi bi-list"></i>
                @if(session('lang') == 'en')
                    Item List
                @else
                    商品列表
                @endif
            </a>
        </li>

        <li class="breadcrumb-item" aria-current="page">
            <a href="{{ url('/item?origin=' . $item->origin->id) }}">
                <i class="bi bi-globe2"></i>
                @if(session('lang') == 'en')
                    From {{ $item->origin->name_en }}
                @else
                    {{ $item->origin->name }}出产
                @endif
            </a>
        </li>

        <li class="breadcrumb-item active" aria-current="page">
            <i class="bi bi-bag"></i>
            @if(session('lang') == 'en')
                {{ $item->name_en }}
            @else
                {{ $item->name }}
            @endif
        </li>
    </ol>
</nav>
