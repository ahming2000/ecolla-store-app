<div class="card shadow mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <div class="h4">
                @if(session('lang') == 'en')
                    Cart
                @else
                    购物车
                @endif
            </div>

            @if(session('cart')->count() != 0)
                <button class="btn btn-outline-danger" id="cart-reset-button" onclick="resetCart()">
                    @if(session('lang') == 'en')
                        <i class="bi bi-cart-x"></i> Clear
                    @else
                        <i class="bi bi-cart-x"></i> 清空
                    @endif
                </button>
            @endif
        </div>

        <div class="row p-3 gy-2" id="cart-empty-icon" {{ session('cart')->count() == 0 ? '' : 'hidden' }}>
            <div class="col-12 text-center">
                <img src="{{ asset('images/cart-empty.png') }}" width="150" height="150" alt="Cart Empty Image">
            </div>

            <div class="col-12 text-center">
                    <span class="h5">
                        @if(session('lang') == 'en')
                            Your Cart Is Empty
                        @else
                            您的购物车为空
                        @endif
                    </span>
            </div>
        </div>

        <div>
            @foreach(session('cart')->cartItems as $cartItem)
                @include('listing.cart.cart-item')
            @endforeach
        </div>
    </div>
</div>
