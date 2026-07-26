<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Origin extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
    ];

    /**
     * @return HasMany<Item, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * @return HasMany<Item, $this>
     */
    public function listedItems(): HasMany
    {
        return $this->hasMany(Item::class)
            ->where('is_listed', '=', true);
    }
}
