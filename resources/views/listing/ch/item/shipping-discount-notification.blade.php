@if(\App\Models\SystemConfig::shippingDiscountIsActivated())
    <div class="container my-3">
        <div class="card shadow bg-warning mb-3">
            <div class="card-body">
                <div class="h5 text-center">
                    限时优惠！买上RM{{ \App\Models\SystemConfig::getShippingDiscountThreshold() }}免邮！
                </div>
            </div>
        </div>
    </div>
@endif

