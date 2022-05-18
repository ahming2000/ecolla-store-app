<form action="{{ url('/cart/check-out') }}" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf

    <div class="h4 fw-bold">
        @if(session('lang') == 'en')
            Delivery Info
        @else
            资料填写
        @endif
    </div>

    @if($cart->orderMode == \App\Enum\OrderMode::$SELF_PICKUP)
        <div class="form-floating mb-3">
            <input type="text" class="form-control has-validation" name="phone" id="phone-input" placeholder="0121234567"
                   autofocus required>

            <label for="phone-input">
                @if(session('lang') == 'en')
                    Contact Number
                @else
                    电话号码
                @endif
            </label>

            <div class="invalid-feedback">
                @if(session('lang') == 'en')
                    Please fill up your contact number!
                @else
                    请填写您的电话号码！
                @endif
            </div>
        </div>
    @elseif($cart->orderMode == \App\Enum\OrderMode::$DELIVERY)
        <div class="form-floating mb-2">
            <input type="text" class="form-control has-validation" name="name" id="name-input" placeholder="John" autofocus required>

            <label for="name-input">
                @if(session('lang') == 'en')
                    Name
                @else
                    名字
                @endif
            </label>

            <div class="invalid-feedback">
                @if(session('lang') == 'en')
                    Please fill up your name!
                @else
                    请填写您的名字！
                @endif
            </div>
        </div>

        <div class="form-floating mb-2">
            <input type="text" class="form-control has-validation" name="phone" id="phone-input" placeholder="0121234567"
                   required>

            <label for="phone-input">
                @if(session('lang') == 'en')
                    Contact Number
                @else
                    电话号码
                @endif
            </label>

            <div class="invalid-feedback">
                @if(session('lang') == 'en')
                    Please fill up your contact number!
                @else
                    请填写您的电话号码！
                @endif
            </div>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control has-validation" name="address" id="address-input"
                   placeholder="10, Jalan Kampar" required>

            <label for="address-input">
                @if(session('lang') == 'en')
                    Address
                @else
                    地址
                @endif
            </label>

            <div class="invalid-feedback">
                @if(session('lang') == 'en')
                    Please fill up your address!
                @else
                    请填写您的地址！
                @endif
            </div>
        </div>
    @endif

    <div class="mt-1 mb-2">

        <input type="hidden" name="payment_method" value="{{ \App\Models\PaymentMethod::query()->find(1)->id }}" id="payment-method-input">

        <span class="h5">
            @if(session('lang') == 'en')
                Choose Payment Method
            @else
                选择付款方式
            @endif
        </span>

        <div class="payment-method-selection-container d-inline-block mb-1">
            @foreach($payments as $payment)
                <img class="img-fluid me-2 @if($loop->first) {{ 'payment-selected' }} @endif"
                     src="{{ asset($payment->icon) }}"
                     alt="{{ $payment->name }}"
                     style="width: 109px"
                     data-id="{{ $payment->id }}"
                     data-qrcode="{{ asset($payment->qr_code) }}"
                     onclick="selectPayment(event)">
            @endforeach
        </div>

        <div class="text-center">
            <button class="btn btn-primary" onclick="openPaymentQRCode(event)">
                <i class="bi bi-box-arrow-up-right"></i>
                @if(session('lang') == 'en')
                    Show QR Code
                @else
                    显示 QR Code
                @endif
            </button>
        </div>
    </div>

    <div class="h5">
        @if(session('lang') == 'en')
            Upload Payment Receipt
        @else
            上传收据
        @endif
    </div>

    <div>
        <input type="file" class="form-control has-validation" accept="image/png,image/jpeg"
               id="receipt-image-input" name="receipt_image" onchange="fileOnChange(event)" required>

        <div class="invalid-feedback">
            @if(session('lang') == 'en')
                You have not upload the receipt or the receipt selected cannot be read!
            @else
                您尚未上传收据或您上传的收据无法正常读取！
            @endif
        </div>
    </div>

    <div class="text-center mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-arrow-right-circle"></i>
            @if(session('lang') == 'en')
                Submit
            @else
                提交
            @endif
        </button>
    </div>
</form>

<div class="modal fade" tabindex="-1" id="qr-code-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="d-flex justify-content-center align-content-center">
                    <img class="img-fluid" src="{{ asset('/images/ecolla.png') }}" alt="QR Code" id="qr-code-image">
                </div>
            </div>
        </div>
    </div>
</div>
