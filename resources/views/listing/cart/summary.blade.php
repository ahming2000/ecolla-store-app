<div class="card shadow mb-3">
    <div class="card-body">
        <div class="h5 card-title">
            @if(session('lang') == 'en')
                Order Summary
            @else
                购物车订单摘要
            @endif
        </div>

        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                @if(session('lang') == 'en')
                    Subtotal
                @else
                    小计
                @endif

                <span>
                    RM
                    <span id="subtotal">
                        {{ number_format(session('cart')->subtotal(), 2) }}
                    </span>
                </span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                @if(session('lang') == 'en')
                    Shipping Fee
                @else
                    邮费
                @endif

                <span>
                    RM
                    <span id="shipping-fee">
                        {{ number_format(session('cart')->shippingFee(), 2) }}
                    </span>
                </span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                <strong>
                    @if(session('lang') == 'en')
                        Total
                    @else
                        总计
                    @endif
                </strong>

                <span>
                    <strong>
                        RM
                        <span id="total">
                            {{ number_format(session('cart')->subtotal() + session('cart')->shippingFee(), 2) }}
                        </span>
                    </strong>
                </span>
            </li>
        </ul>

        <form action="{{ url('/cart/check-out') }}" method="post">
            @csrf

            <button type="submit" class="btn btn-primary w-100" @disabled(session('cart')->count() == 0)>
                @if(session('lang') == 'en')
                    Check Out
                @else
                    前往付款
                @endif
            </button>
        </form>
    </div>
</div>
