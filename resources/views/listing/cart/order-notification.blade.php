<div class="card shadow mb-3">
    <div class="card-body bg-info">
        <span class="h5">
            @if(session('lang') == 'en')
                Self Pick-up Service
            @else
                预购取货
            @endif
        </span>

        <p class="text-light">
            @if(session('lang') == 'en')
                Please fill in the phone number for in-store verification purpose
            @else
                请填写电话号码以便能在店里领取您的商品
            @endif
        </p>

        <span class="h5">
            @if(session('lang') == 'en')
                Delivery Service
            @else
                外送
            @endif
        </span>

        @if(session('lang') == 'en')
            <p class="text-light">
                Maximum delivery distance: Within 5KM, delivery service not available for more than 5KM distance from store

                <br />

                Shipping Fee: RM{{ \App\Models\SystemConfig::getShippingFee() }}

                <br />

                <span class="text-danger">
                    **Cash on Delivery is not available
                </span>

                <br /><br />

                Delivery Period Detail:<br />
                Order before 3PM - Deliver in 3PM~4PM<br />
                Order after 3PM - Deliver in 7PM~8PM<br />
            </p>
        @else
            <p class="text-light">
                运送距离：距离本店5公里以内，暂不开放给5公里外的外送

                <br />

                外送价钱：RM{{ \App\Models\SystemConfig::getShippingFee() }}

                <br />

                <span class="text-danger">
                    **不支持货到付款
                </span>

                <br /><br />

                配送时段详情：<br />
                3PM之前订单的配送时间 - 3PM~4PM<br />
                3PM之后订单的配送时间 - 7PM~8PM<br />
            </p>
        @endif
    </div>
</div>
