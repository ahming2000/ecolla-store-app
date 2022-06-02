<?php

namespace App\Models;

use App\Traits\FormatDateToSerialize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemImage extends Model
{
    use HasFactory, FormatDateToSerialize;

    protected $fillable = [
        'image',
        'item_id',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
