<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory, ReturnRegionDateTime;

    protected $primaryKey = 'order_id';

    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'name',
        'phone',
        'address',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
