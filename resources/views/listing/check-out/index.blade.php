@extends('listing.layout.app')

@section('title')
    @if(session('lang') == 'en')
        Check Out | Ecolla e口乐
    @else
        付款 | Ecolla e口乐
    @endif
@endsection

@section('content')
    <div class="container py-3">
        <div class="row">
            <div class="col-12 col-lg-6 offset-lg-3 col-md-8 offset-md-2">
                <div class="h1">
                    @if(session('lang') == 'en')
                        Check Out
                    @else
                        付款
                    @endif
                </div>

                <div class="card shadow mb-3">
                    <div class="card-body">
                        <div class="h4 fw-bold">
                            @if(session('lang') == 'en')
                                Cart's Items
                            @else
                                购物车的商品
                            @endif
                        </div>

                        <div class="row">
                            @foreach($cart->cartItems as $cartItem)
                                @include('listing.check-out.cart-item')
                            @endforeach
                        </div>

                        @if($cart->shippingFee() != 0.0)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">
                                    @if(session('lang') == 'en')
                                        Shipping Fee
                                    @else
                                        运费
                                    @endif
                                </span>

                                <span class="text-muted">
                                    RM{{ number_format($cart->shippingFee(), 2, '.', '') }}
                                </span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">
                                @if(session('lang') == 'en')
                                    Total
                                @else
                                    总计
                                @endif
                            </span>

                            <span class="fw-bold">
                                RM{{ number_format($cart->subtotal() + $cart->shippingFee(), 2, '.', '') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-body">
                        @include('listing.check-out.form')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(() => {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            let forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }

                        if ($('#receipt-image-input').hasClass('is-invalid')) {
                            event.preventDefault()
                            event.stopPropagation()
                        } else {
                            form.classList.add('was-validated')
                        }
                    }, false)
                })
        })

        const fileOnChange = async (event) => {
            if (await verifyImage(event)) {
                $(event.target).removeClass('is-invalid')
            } else {
                $('form').removeClass('was-validated')
                $(event.target).addClass('is-invalid')
            }
        }
    </script>
@endsection
