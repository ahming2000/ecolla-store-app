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

    public function count(bool $showUnlisted = false): int
    {
        if ($showUnlisted) {
            return Item::query()
                ->where('origin_id', '=', $this->id)
                ->count('id');
        } else {
            return Item::query()
                ->where('origin_id', '=', $this->id)
                ->where('is_listed', '=', true)
                ->count('id');
        }
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }
}
