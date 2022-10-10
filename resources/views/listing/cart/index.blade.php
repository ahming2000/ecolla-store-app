@extends('listing.layout.app')

@section('title')
    @if(session('lang') == 'en')
        Cart | Ecolla e口乐
    @else
        购物车 | Ecolla e口乐
    @endif
@endsection

@section('content')
    <div class="container py-3">
        @include('common.shipping-discount-notification')

        <div class="row">
            <div class="col-lg-8">
                @include('listing.cart.cart-item-section')

                @include('listing.cart.order-notification')
            </div>

            <div class="col-lg-4">
                @include('listing.cart.customer-service-notification')

                @include('listing.cart.summary')
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        useCartControl()
    </script>
@endsection
