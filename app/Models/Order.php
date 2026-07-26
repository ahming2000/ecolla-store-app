<?php

namespace App\Models;

use App\Enums\DeliveryMode;
use App\Enums\Status;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_num',
        'delivery_mode',
        'status',
        'tracking_no',
        'shipping_fee',
        'payment_method_id',
        'receipt_image_id',
        'note',
        'cus_name',
        'cus_phone',
        'cus_address',
    ];

    protected $casts = [
        'delivery_mode' => DeliveryMode::class,
        'status' => Status::class,
    ];

    /**
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * @return HasOne<Image, $this>
     */
    public function receiptImage(): HasOne
    {
        return $this->hasOne(Image::class, 'id', 'receipt_image_id');
    }

    /**
     * @return HasMany<OrderedItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderedItem::class);
    }
}
