<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
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
                ->join('category_item', 'items.id', 'category_item.item_id')
                ->where('category_item.category_id', '=', $this->id)
                ->count('items.id');
        } else {
            return Item::query()
                ->join('category_item', 'items.id', 'category_item.item_id')
                ->where('category_item.category_id', '=', $this->id)
                ->where('items.is_listed', '=', true)
                ->count('items.id');
        }
    }
}
