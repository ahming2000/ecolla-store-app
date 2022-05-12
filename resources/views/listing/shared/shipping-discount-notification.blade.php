@if(\App\Models\SystemConfig::shippingDiscountIsActivated())
    <div class="card shadow bg-warning mb-3">
        <div class="card-body">
            <div class="h5 text-center">
                @if(session('lang') == 'en')
                    Special Event! Free Shipping when purchase RM{{ \App\Models\SystemConfig::getShippingDiscountThreshold() }} and above!
                @else
                    限时优惠！买上RM{{ \App\Models\SystemConfig::getShippingDiscountThreshold() }}免邮！
                @endif
            </div>
        </div>
    </div>
@endif

