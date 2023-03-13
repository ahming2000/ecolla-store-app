<div class="col-12 mb-3 cart-item" data-barcode="{{ $cartItem->variation->barcode }}">
    <div class="row">
        <div class="col-4 col-lg-3">
            <a href="{{ route('listing.item.view', ['item' => $cartItem->variation->item->id]) }}">
                <img class="img-fluid rounded-3" alt="Variation Image"
                     src="{{ $cartItem->variation->image ?? $cartItem->variation->item->getCoverImage() }}">
            </a>
        </div>

        <div class="col-8 col-lg-9">
            <div class="d-flex justify-content-between align-content-center">
                <div class="h4 fw-bold text-truncate">
                    <a href="{{ route('listing.item.view', ['item' => $cartItem->variation->item->id]) }}">
                        @if(session('lang') == 'en')
                            {{ $cartItem->variation->item->name_en }}
                        @else
                            {{ $cartItem->variation->item->name }}
                        @endif
                    </a>
                </div>

                <button class="btn btn-danger btn-sm rounded-pill px-3" onclick="removeFromCart(event)">
                    <i class="bi bi-cart-dash"></i>
                </button>
            </div>

            <div class="h6">
                <span class="text-break">
                    @if(session('lang') == 'en')
                        {{ $cartItem->variation->name_en }}
                    @else
                        {{ $cartItem->variation->name }}
                    @endif
                </span>


                @if($cartItem->variation->getDiscountRate() != 1.0)
                    <span class="badge rounded-pill bg-danger me-1">
                        {{ $cartItem->variation->getDiscountLabel() }}
                    </span>
                @endif
            </div>

            <input type="hidden" class="variation-weight" value="{{ $cartItem->variation->weight }}" />
            <input type="hidden" class="variation-price" value="{{ $cartItem->variation->getPrice() }}" />

            <div class="h6 text-muted cart-item-weight">
                {{ number_format($cartItem->variation->weight * $cartItem->quantity, 3) . 'kg' }}
            </div>

            <div class="h6 cart-item-sub-price">
                RM{{ number_format($cartItem->subPrice(), 2, '.', '') }}
            </div>

            @include('common.quantity-control', ['barcode' => $cartItem->variation->barcode, 'min' => 1, 'max' => $cartItem->variation->stock, 'quantity' => $cartItem->quantity])
        </div>
    </div>
</div>
