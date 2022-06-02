<?php

namespace App\Models;

use App\Traits\FormatDateToSerialize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentMethod extends Model
{
    use HasFactory, FormatDateToSerialize;

    protected $fillable = [
        'name',
        'icon',
        'qr_code',
    ];

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class);
    }
}
