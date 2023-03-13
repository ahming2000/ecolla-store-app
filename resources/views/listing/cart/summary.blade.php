<div class="card shadow mb-3">
    <div class="card-body">
        <div class="form-floating mb-3">
            <select class="form-select" name="orderMode" id="order-mode-input" onchange="orderModeOnChange(event)">
                <option value="{{ \App\Enum\OrderMode::$SELF_PICKUP }}" @selected(session('cart')->orderMode == \App\Enum\OrderMode::$SELF_PICKUP)>
                    @if(session('lang') == 'en')
                        Pick-up
                    @else
                        预购取货
                    @endif
                </option>

                <option value="{{ \App\Enum\OrderMode::$DELIVERY }}" @selected(session('cart')->orderMode == \App\Enum\OrderMode::$DELIVERY)>
                    @if(session('lang') == 'en')
                        Delivery (Within 5km from store)
                    @else
                        外送（距离本店5公里内）
                    @endif
                </option>
            </select>

            <label for="order-mode-input">
                @if(session('lang') == 'en')
                    Order Mode
                @else
                    订单模式
                @endif
            </label>
        </div>

        <div class="h5 card-title fw-bold">
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

        <a class="btn btn-primary w-100 @disabled(session('cart')->count() == 0)"
           href="{{ route('listing.cart.checkOut') }}" id="check-out-button">
            @if(session('lang') == 'en')
                Check Out
            @else
                前往付款
            @endif
        </a>
    </div>
</div>
