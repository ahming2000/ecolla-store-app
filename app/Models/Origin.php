<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;

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

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function delete(): ?bool
    {
        $this->setAttribute('deleted_at', Date::now());
        return $this->save();
    }
}
