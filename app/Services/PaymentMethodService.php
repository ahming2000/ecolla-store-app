<?php

namespace App\Services;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

class PaymentMethodService
{
    /**
     * @return Collection<int, PaymentMethod>
     */
    public function getPaymentMethods(bool $showDisabled = false): Collection
    {
        if (! $showDisabled) {
            return PaymentMethod::query()
                ->where('is_enabled', '=', true)
                ->get();
        }

        return PaymentMethod::all();
    }
}
