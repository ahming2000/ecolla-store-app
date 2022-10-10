@extends('listing.layout.app')

@inject('helper', \App\Util\Helper::class)

@section('title')
    @if(session('lang') == 'en')
        Ecolla Official Snack Shop
    @else
        Ecolla e口乐零食店官网
    @endif
@endsection

@section('content')
    <div class="container py-3">
        @include('listing.common.shipping-discount-notification')

        <form class="row my-2" action="{{ url('/item') }}" id="filterForm">
            <div class="col-12 col-md-6 mb-2">
                <div class="row">
                    <div class="d-flex justify-content-between">
                        <div class="flex-grow-1 me-2">
                            @if(session('lang') == 'en')
                                <input type="text" class="form-control shadow" maxlength="20" name="search"
                                       placeholder="Search name, barcode, variation, description"
                                       value="{{ $helper->param('search') }}">
                            @else
                                <input type="text" class="form-control shadow" maxlength="20" name="search"
                                       placeholder="搜索名称、货号、规格、商品描述" value="{{ $helper->param('search') }}">
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                            @if(session('lang') == 'en')
                                Search
                            @else
                                搜索
                            @endif
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 mb-2">
                <select class="form-select shadow" name="category" onchange="filter()">
                    <option value="-1">
                        @if(session('lang') == 'en')
                            All Categories' Item ({{ \App\Models\Item::getTotalItemListed() }})
                        @else
                            所有类别的商品 ({{ \App\Models\Item::getTotalItemListed() }})
                        @endif

                    </option>

                    @foreach($categories as $category)
                        @if($category->count() != 0)
                            <option value="{{ $category->id }}" {{ $helper->paramSelected('category', $category->id) }}>
                                @if(session('lang') == 'en')
                                    {{ $category->name_en }} ({{ $category->count() }})
                                @else
                                    {{ $category->name }} ({{ $category->count() }})
                                @endif
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6 mb-2">
                <select class="form-select shadow" name="origin" onchange="filter()">
                    <option value="-1">
                        @if(session('lang') == 'en')
                            From All Country
                        @else
                            全部出产地
                        @endif
                    </option>

                    @foreach($origins as $origin)
                        @if($origin->count() != 0)
                            <option value="{{ $origin->id }}" {{ $helper->paramSelected('origin', $origin->id) }}>
                                @if(session('lang') == 'en')
                                    {{ $origin->name_en }} ({{ $origin->count() }})
                                @else
                                    {{ $origin->name }} ({{ $origin->count() }})
                                @endif
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="flex-grow-1 me-2">
                        <select class="form-select shadow" name="orderBy" onchange="filter()">
                            @foreach(\App\Enum\AttributeName::all() as $attribute)
                                <option
                                        value="{{ $attribute }}" {{ $helper->paramSelected('orderBy', strval($attribute)) }}>
                                    {{ \App\Enum\AttributeName::getLabel($attribute, session('lang') == 'en') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content center align-items-center">
                        <input
                                value="{{ $helper->param('arrangement', 0, true) == \App\Enum\Arrangement::$DESC ? \App\Enum\Arrangement::$ASC : \App\Enum\Arrangement::$DESC }}"
                                type="hidden" name="arrangement">

                        <button class="btn btn-primary">
                            @if($helper->param('arrangement', 0, true) == 0)
                                <i class="bi bi-sort-down" onclick="filter()"></i>
                            @else
                                <i class="bi bi-sort-up" onclick="filter()"></i>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="row">
            @if(sizeof($items) == 0)
                <div class="d-flex justify-content-center align-items-center" style="margin: 150px 0">
                    <img class="img-fluid" src="{{ asset('/images/no-result.png') }}" alt="No Result Image"
                         width="300px" height="300px">
                </div>
            @endif

            @foreach($items as $item)
                <div class="col-6 col-md-4 col-lg-3 col-xxl-2 mb-3">
                    @include('listing.common.item-card')
                </div>
            @endforeach

            <div class="d-flex justify-content-center">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const filter = () => {
            $('#filterForm').submit()
        }
    </script>
@endsection
