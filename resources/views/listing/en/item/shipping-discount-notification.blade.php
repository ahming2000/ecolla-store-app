@if(\App\Models\SystemConfig::shippingDiscountIsActivated())
    <div class="container my-3">
        <div class="card shadow bg-warning mb-3">
            <div class="card-body">
                <div class="h5 text-center">
                    Special Event! Free Shipping when purchase RM{{ \App\Models\SystemConfig::getShippingDiscountThreshold() }} and above!
                </div>
            </div>
        </div>
    </div>
@endif

