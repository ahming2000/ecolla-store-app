<div class="card mb-1">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div class="flex-grow-1 me-1">
                <div class="h5 card-title">
                    <span class="text-break">
                        @if(session('lang') == 'en')
                            {{ $variation->name_en }}
                        @else
                            {{ $variation->name }}
                        @endif
                    </span>

                    @if($variation->getDiscountRate() != 1.0)
                        <span class="badge rounded-pill bg-danger me-1">
                            {{ $variation->getDiscountLabel() }}
                        </span>
                    @endif

                    @if($variation->stock == 0)
                        <span class="badge rounded-pill bg-info mx-1">
                            已售完
                        <span>
                    @endif
                </div>

                <div class="card-text">
                    @if($variation->getDiscountRate() == 1.0)
                        <span>
                            <strong>
                                RM{{ $variation->getPrice() }}
                            </strong>
                        </span>
                    @else
                        <span class="me-1">
                            <del>
                                RM{{ number_format($variation->price, 2, '.', '') }}
                            </del>
                        </span>

                        <span>
                            <strong>
                                RM{{ $variation->getPrice() }}
                            </strong>
                        </span>
                    @endif
                </div>
            </div>

            @include('common.quantity-control', ['barcode' => $variation->barcode, 'min' => 0, 'max' => $variation->stock])
        </div>
    </div>
</div>
