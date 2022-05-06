<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Origin extends Model
{
    use HasFactory, ReturnRegionDateTime;

    protected $fillable = [
        'name',
        'name_en',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }
}
