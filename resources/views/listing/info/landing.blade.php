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
        <section id="about-company">
            <div class="row g-2">
                <div class="col-8 col-md-8 my-auto">
                    <div class="h1 fw-bold">
                        @if(session('lang') == 'en')
                            About Ecolla
                        @else
                            关于e口乐
                        @endif
                    </div>

                    <p class="pt-3">
                        @if(session('lang') == 'en')
                            Ecolla is a shop that located at Perak, Kampar which selling international snacks such as
                            snacks from Malaysia, China, Thailand, Taiwan, Japan, Korea and others.
                        @else
                            本店是位于霹雳金宝的一间外国零食店，售卖来自马来西亚，中国，泰国，台湾，日本，韩国等等的零食。
                        @endif
                    </p>
                </div>

                <div class="col-4 col-md-4 my-auto">
                    <img class="img-fluid" src="{{ asset('/images/shop-clipart.jpeg') }}" alt="Shop Clipart">
                </div>
            </div>
        </section>

        @if(sizeof($highestViewItems) != 0)
            <section id="highest-view" class="px-1">
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
                                @include('common.item-card')
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if(sizeof($highestSoldItems) != 0)
            <section id="highest-sold" class="px-1">
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
                                @include('common.item-card')
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section id="links">
            <div class="h2">
                @if(session('lang') == 'en')
                    Links
                @else
                    各种链接
                @endif
            </div>

            <div class="row g-2">
                <div class="col-6 col-lg-3">
                    <a href="{{ url('/payment-method') }}">
                        <div class="d-flex justify-content-center align-items-center rounded-3"
                             style="background-color: #1c713e;height: 100px">
                            <span class="fw-bold text-white">
                                <i class="bi bi-wallet2 me-1"></i>

                                @if(session('lang') == 'en')
                                    Payment Methods
                                @else
                                    付款方式浏览
                                @endif
                            </span>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-lg-3">
                    <a href="{{ url('/item') }}">
                        <div class="d-flex justify-content-center align-items-center rounded-3"
                             style="background-color: #e968a8;height: 100px">
                            <span class="fw-bold text-white">
                                <i class="bi bi-list me-1"></i>

                                @if(session('lang') == 'en')
                                    All Items
                                @else
                                    浏览所有商品
                                @endif
                            </span>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-lg-3">
                    <a href="{{ url('/cart') }}">
                        <div class="d-flex justify-content-center align-items-center rounded-3"
                             style="background-color: #8c9126;height: 100px">
                            <span class="fw-bold text-white">
                                <i class="bi bi-cart me-1"></i>

                                @if(session('lang') == 'en')
                                    Your Cart
                                @else
                                    您的购物车
                                @endif
                            </span>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-lg-3">
                    <a href="https://wa.link/fcfum1" target="_blank">
                        <div class="d-flex justify-content-center align-items-center rounded-3"
                             style="background-color: #2fe577;height: 100px">
                            <span class="fw-bold">
                                <i class="bi bi-whatsapp me-1"></i>

                                @if(session('lang') == 'en')
                                    Contact Us!
                                @else
                                    联系我们！
                                @endif
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('script')
    <script>
        useTinySliderCarousel('.highest-view-count-item-container')
        useTinySliderCarousel('.highest-sold-item-container')
    </script>
@endsection
