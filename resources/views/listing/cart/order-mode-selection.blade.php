<div class="card shadow mb-3">
    <div class="card-body">
        <div class="form-floating">
            <select class="form-select" name="orderMode" id="order-mode-input" onchange="updateShippingFee(event)">
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
    </div>
</div>
