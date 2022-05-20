<div class="d-flex justify-content-center align-content-center">
    <div class="input-group quantity-control my-auto" style="width: 130px;" id="{{ $barcode }}">
        <button class="btn btn-primary quantity-decrease" type="button">
            <i class="bi bi-dash"></i>
        </button>

        <input type="number" class="form-control form-control-sm quantity" value="{{ $quantity ?? 0 }}"
               min="{{ $min }}" max="{{ $max }}" @disabled($disabled ?? false)/>

        <button class="btn btn-primary quantity-increase" type="button">
            <i class="bi bi-plus"></i>
        </button>
    </div>
</div>
