<div class="col-12 col-xl-6 mb-2">
    <div class="row">
        <div class="col-4">
            <img class="img-fluid rounded-3"
                 src="{{ $cartItem->variation->image ?? $cartItem->variation->item->getCoverImage() }}"
                 alt="Variation Image"/>
        </div>

        <div class="col-8">
            <span class="fw-bold text-truncate">
                @if(session('lang') == 'en')
                    {{ $cartItem->variation->item->name_en }}
                @else
                    {{ $cartItem->variation->item->name }}
                @endif
            </span>

            <div class="d-flex justify-content-between">
                <span class="text-muted text-truncate">
                    @if(session('lang') == 'en')
                        {{ $cartItem->variation->name }}
                    @else
                        {{ $cartItem->variation->name_en }}
                    @endif
                </span>

                <span>
                    x{{ $cartItem->quantity }}
                </span>
            </div>

            <div class="d-flex justify-content-end">
                <span class="text-muted">
                    {{ $cartItem->subPriceLabel() }}
                </span>
            </div>
        </div>
    </div>
</div>
