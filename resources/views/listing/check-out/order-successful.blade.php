@extends('listing.layout.app')

@section('title')
    @if(session('lang') == 'en')
        Check Out Successfully! | Ecolla e口乐
    @else
        付款成功！ | Ecolla e口乐
    @endif
@endsection

@section('content')
    <div class="container py-3">
        <div class="row">
            <div class="col-12 col-sm-8 offset-sm-2 text-center">
                <img class="img-fluid my-3" width="200" height="200"
                     src="{{ asset('images/green-check-mark-circle.png') }}"
                     alt="Green Check Mark Circle">

                <div class="fw-bold">
                    @if(session('lang') == 'en')
                        Order Successfully!
                    @else
                        下单成功
                    @endif
                </div>

                <div class="text-muted">
                    @if(session('lang') == 'en')
                        Thank you for your support!
                    @else
                        谢谢您的支持！
                    @endif
                </div>

                <div class="text-muted">
                    @if(session('lang') == 'en')
                        We will process your order ASAP!
                    @else
                        我们会尽快完成您的订单！
                    @endif
                </div>

                <div class="mt-1 mb-5">
                    @if(session('lang') == 'en')
                        <a class="btn btn-primary" href="https://wa.link/2e1z4h" target="_blank">
                            <i class="bi bi-whatsapp"></i>
                            Confirm Order Detail
                        </a>
                    @else
                        <a class="btn btn-primary" href="https://wa.link/fcfum1" target="_blank">
                            <i class="bi bi-whatsapp"></i>
                            确认订单详情
                        </a>
                    @endif
                </div>


                <div class="h4 fw-bold">
                    {{ $orderId ?? 'N/A' }}
                </div>

                <div>
                    @if(session('lang') == 'en')
                        Order ID
                    @else
                        订单ID
                    @endif
                </div>

                <div class="mt-3 mb-3">
                    <a class="btn btn-secondary" href="{{ route('listing.item.index') }}">
                        <i class="bi bi-house"></i>
                        @if(session('lang') == 'en')
                            Back To Item Browsing
                        @else
                            返回商品浏览
                        @endif
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="h4 fw-bold">
                            @if(session('lang') == 'en')
                                Purchase History
                            @else
                                购买记录
                            @endif
                        </div>

                        <div class="row">
                            @foreach($cartItems as $cartItem)
                                @include('listing.check-out.cart-item')
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
