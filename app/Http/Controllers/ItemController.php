<?php

namespace App\Http\Controllers;

use App\Enum\Arrangement;
use App\Enum\AttributeName;
use App\Models\Category;
use App\Models\Item;
use App\Models\Origin;
use App\Util\Helper;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function listingPage(): Factory|View|Application
    {
        $search = Helper::param('search');
        $origin = Helper::param('origin', -1, true);
        $category = Helper::param('category', -1, true);
        $orderBy = Helper::param('orderBy', 0, true);
        $arrangement = Helper::param('arrangement', 0, true);

        $ids = $this->search($search);
        $ids = $this->filterOrigin($ids, $origin);
        $ids = $this->filterCategory($ids, $category);

        $items = Item::query()
            ->whereIn('id', $ids)
            ->where('is_listed', '=', true)
            ->orderByRaw(AttributeName::getName($orderBy) . ' ' . Arrangement::getArrangement($arrangement))
            ->paginate(5);

        $categories = Category::all();

        $origins = Origin::all();

        $items->appends([
            'search' => $search,
            'origin' => $origin,
            'category' => $category,
            'orderBy' => $orderBy,
            'arrangement' => $arrangement,
        ]);

        return view('listing.item.index', compact('items', 'categories', 'origins'));
    }

    public function singleListingPage(Item $item): Factory|View|Application
    {
        return view('listing.item.show', compact('item'));
    }

    private function search(string $keyword = ""): array
    {
        if ($keyword == "") return array_column(Item::all('id')->toArray(), 'id');

        return array_column(
            Item::query()
                ->select('items.id')
                ->join('variations', 'variations.item_id', '=', 'items.id')
                ->where('items.name', 'LIKE', DB::raw("'%$keyword%'"))
                ->orWhere('items.name_en', 'LIKE', DB::raw("'%$keyword%'"))
                ->orWhere('items.desc', 'LIKE', DB::raw("'%$keyword%'"))
                ->orWhere('variations.name', 'LIKE', DB::raw("'%$keyword%'"))
                ->orWhere('variations.name_en', 'LIKE', DB::raw("'%$keyword%'"))
                ->orWhere('variations.barcode', 'LIKE', DB::raw("'%$keyword%'"))
                ->distinct('items.id')
                ->get()
                ->toArray()
            , 'id');
    }

    private function filterOrigin(array $item_ids, int $origin_id): array
    {
        if ($origin_id == -1) return $item_ids;

        return array_column(
            Item::query()
                ->select('items.id')
                ->join('origins', 'items.origin_id', '=', 'origins.id')
                ->whereIn('items.id', $item_ids)
                ->where('origins.id', '=', $origin_id)
                ->get()
                ->toArray()
            , 'id');
    }

    private function filterCategory(array $item_ids, int $category_id): array
    {
        if ($category_id == -1) return $item_ids;

        return array_column(
            DB::table('category_item')
                ->select('item_id')
                ->whereIn('item_id', $item_ids)
                ->where('category_id', '=', $category_id)
                ->get()
                ->toArray()
            , 'item_id');
    }
}
