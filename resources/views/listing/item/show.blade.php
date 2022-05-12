@extends('listing.layout.app')

@section('title')
    @if(session('lang') == 'en')
        {{ $item->name_en }} | Ecolla e口乐
    @else
        {{ $item->name }} | Ecolla e口乐
    @endif
@endsection

@section('content')
    <div class="container py-3">
        @include('listing.shared.shipping-discount-notification')

        @include('listing.item.breadcrumb')

        <div class="row">
            <div class="col-12 col-lg-5">
                @include('listing.item.item-carousel')
            </div>

            <div class="col-12 col-lg-7 p-3">
                <div class="row">
                    <div class="col-12 mb-3">
                        @foreach($item->categories as $category)
                            <a href="{{ url('/ch/item?category=' . $category->id) }}">
                                <span class="badge rounded-pill mr-1 p-2" style="background-color: mediumpurple">
                                    @if(session('lang') == 'en')
                                        {{ $category->name_en }}
                                    @else
                                        {{ $category->name }}
                                    @endif
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <div class="col-12 h2 font-weight-bold">
                        @if(session('lang') == 'en')
                            {{ $item->name_en }}
                        @else
                            {{ $item->name }}
                        @endif
                    </div>

                    <div class="col-12 h6 text-muted mb-3">
                        @if(session('lang') == 'en')
                            <span>{{ $item->sold }} sold</span> |
                            <span>{{ $item->view_count }} views</span>
                        @else
                            <span>已售出 {{ $item->sold }} 个</span> |
                            <span>{{ $item->view_count }} 次浏览</span>
                        @endif
                    </div>

                    <div class="col-12">
                        @foreach($item->variations as $variation)
                            @include('listing.item.variation-card')
                        @endforeach

                        <div class="d-flex justify-content-center mt-3">
                            <button class="btn btn-primary" onclick="addToCart()">
                                <i class="bi bi-cart-plus"></i>
                                @if(session('lang') == 'en')
                                    Add To Cart
                                @else
                                    加入购物车
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="h2" style="font-weight: bold">
            @if(session('lang') == 'en')
                Item Description
            @else
                商品描述
            @endif
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <textarea id="item-description" readonly hidden>
                    {{ $item->desc }}
                </textarea>

                <p></p>
            </div>
        </div>

    </div>
@endsection

@section('script')
    <script>
        useQuantityControl()

        const addToCart = () => {
            let quantityControls = $('.quantity-control')
            let addList = [];

            for (let i = 0; i < quantityControls.length; i++) {
                let barcode = quantityControls.eq(i).attr('id')
                let quantity = parseInt(quantityControls.eq(i).find('.quantity').val())

                if (quantity !== 0) {
                    addList.push(
                        {
                            barcode: barcode,
                            quantity: quantity,
                        }
                    )
                }
            }

            if (addList.length !== 0) {
                axios.post('/api/cart/add', {
                    addList: addList,
                }).then((res) => {
                    if (res.data.isAdded) {
                        @if(session('lang') == 'en')
                        addNotification('Cart', 'Add to cart successfully!', [
                                {
                                    buttonText: 'Go To Cart', redirectTo: '/cart'
                                }
                            ]
                        )
                        @else
                        addNotification('购物车', '成功加入购物车！', [
                                {
                                    buttonText: '前往购物车', redirectTo: '/cart'
                                }
                            ]
                        )
                        @endif

                        updateCartCount()
                    }
                }).catch((error) => {
                    console.error(error)
                })
            }
        }
    </script>
@endsection
