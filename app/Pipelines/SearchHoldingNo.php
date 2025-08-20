<?php

namespace App\Pipelines;

use Closure;

class SearchHoldingNo
{
    public function handle($query, Closure $next)
    {
        if (request()->filled('holdingNo')) {
            $query->where('holding_no', request()->holdingNo);
        }
        return $next($query);
    }
}
