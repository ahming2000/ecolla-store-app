<?php

namespace App\Models;

use App\Traits\FormatDateToSerialize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory, FormatDateToSerialize;

    protected $fillable = [
        'name',
        'name_en',
    ];

    public function count(bool $showUnlisted = false): int
    {
        if ($showUnlisted) {
            return $this->items()
                ->count();
        } else {
            return $this->items()
                ->where('is_listed', '=', true)
                ->count();
        }
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }
}
