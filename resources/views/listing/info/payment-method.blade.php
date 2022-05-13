@extends('listing.layout.app')

@section('title')
    @if(session('lang') == 'en')
        Payment Method | Ecolla e口乐
    @else
        付款方式 | Ecolla e口乐
    @endif
@endsection

@section('content')
    <div class="container py-3">
        @include('listing.shared.shipping-discount-notification')

        <div class="row">
            <div class="col-sm-12 col-md-8 offset-md-2">
                <span class="h1 mb-3">
                    @if(session('lang') == 'en')
                        Payment Method
                    @else
                        付款方式
                    @endif
                </span>

                <p>
                    @if(session('lang') == 'en')
                        We accept these payments method
                    @else
                        这是我们能接受的付款通道
                    @endif
                </p>

                @foreach($payments as $payment)
                    <div class="bg-white p-3 m-2 mx-auto" onclick="openQRCode(event, '{{ asset($payment->qr_code) }}')">
                        <img src="{{ asset($payment->icon) }}"
                             alt="Payment Method" height="100" width="100" loading="lazy">

                        <span class="ms-5 h5">
                            {{ $payment->name }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

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
    </div>
@endsection

@section('script')
    <script>
        const openQRCode = (event, qrCode) => {
            $('#qr-code-image').attr('src', qrCode)
            let QRCodeModal = new bootstrap.Modal($('#qr-code-modal'))
            QRCodeModal.show()
        }
    </script>
@endsection
