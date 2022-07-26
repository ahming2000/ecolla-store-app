<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function estimatePagination(int $totalCount, int $minItem = 40, int $maxPage = 9, int $itemPerRow = 4): int
    {
        if ($totalCount <= $minItem) return $minItem;

        while (floor($totalCount / $minItem) >= $maxPage) {
            $minItem = $minItem + $itemPerRow;
        }

        return $minItem;
    }
}
