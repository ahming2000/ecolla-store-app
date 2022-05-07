@extends('listing.ch.layout.app')

@inject('helper', \App\Util\Helper::class)

@section('title')
    Ecolla e口乐零食店官网
@endsection

@section('content')
    <div class="container my-3">
        @include('listing.ch.item.shipping-discount-notification')

        <form class="row my-2" action="{{ url('/item') }}" id="filterForm">
            <div class="col-12 col-md-6 mb-2">
                <div class="row">
                    <div class="d-flex justify-content-between">
                        <div class="flex-grow-1 me-2">
                            <input type="text" class="form-control shadow" maxlength="20" name="search"
                                   placeholder="搜索名称、货号、规格、商品描述" value="{{ $helper->param('search') }}">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> 搜索
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 mb-2">
                <select class="form-select shadow" name="category" onchange="filter()">
                    <option value="-1">
                        全部商品 ({{ sizeof($items) }})
                    </option>

                    @foreach($categories as $category)
                        @if($category->count() != 0)
                            <option value="{{ $category->id }}" {{ $helper->paramSelected('category', $category->id) }}>
                                {{ $category->name }} ({{ $category->count() }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6 mb-2">
                <select class="form-select shadow" name="origin" onchange="filter()">
                    <option value="-1">
                        全部出产地 ({{ sizeof($origins) }})
                    </option>

                    @foreach($origins as $origin)
                        @if($origin->count() != 0)
                            <option value="{{ $origin->id }}" {{ $helper->paramSelected('origin', $origin->id) }}>
                                {{ $origin->name }} ({{ $origin->count() }})
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
                                    {{ \App\Enum\AttributeName::getLabel($attribute) }}
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
                    <img class="img-fluid" src="{{ asset('/images/no-result.png') }}" alt="No Result Image" width="300px" height="300px">
                </div>
            @endif

            @foreach($items as $item)
                @include('listing.ch.item.item-card')
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
