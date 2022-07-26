@extends('listing.layout.app')

@section('title')
    @if(session('lang') == 'en')
        Welcome to Ecolla Official Website!
    @else
        欢迎来到e口乐官方网站
    @endif
@endsection

@section('header')
    @include('listing.layout.header')
@endsection

@section('content')
    <div class="container py-3">
        <p>TODO: About company</p>

        @if(sizeof($highestViewItems) != 0)
            <div class="h2">
                @if(session('lang') == 'en')
                    Highest View Count Items
                @else
                    最多浏览次数的商品
                @endif
            </div>

            <div class="row mb-3">
                <div class="highest-view-count-item-container">
                    @foreach($highestViewItems as $item)
                        <div class="me-2">
                            @include('listing.common.item-card')
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(sizeof($highestSoldItems) != 0)
            <div class="h2">
                @if(session('lang') == 'en')
                    Highest Sold Count Items
                @else
                    销量最好的商品
                @endif
            </div>

            <div class="row mb-3">
                <div class="highest-sold-item-container">
                    @foreach($highestSoldItems as $item)
                        <div class="me-2">
                            @include('listing.common.item-card')
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <p>TODO: Create a link button for payment, item list, cart and contact us (whatsapp button)</p>
    </div>
@endsection

@section('script')
    <script>
        let highestViewCountItemsCarousel = tinySlider({
            container: '.highest-view-count-item-container',
            items: 2,
            responsive: {
                576: {
                    items: 3,
                },
                768: {
                    items: 4,
                },
                992: {
                    items: 5,
                },
                1200: {
                    items: 6,
                },
                1400: {
                    items: 7,
                },
            },

            mouseDrag: true,
            controls: false,
            nav: false,
            loop: false,
        });

        let highestSoldItemsCarousel = tinySlider({
            container: '.highest-sold-item-container',
            items: 2,
            responsive: {
                576: {
                    items: 3,
                },
                768: {
                    items: 4,
                },
                992: {
                    items: 5,
                },
                1200: {
                    items: 6,
                },
                1400: {
                    items: 7,
                },
            },

            mouseDrag: true,
            controls: false,
            nav: false,
            loop: false,
        });
    </script>
@endsection
