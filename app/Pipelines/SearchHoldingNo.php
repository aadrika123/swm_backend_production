<?php

namespace App\Pipelines;

use Closure;

class SearchHoldingNo
{
    public function handle($request, Closure $next)
    {
        if (!request()->has('holdingNo')) {
            return $next($request);
        }
        return $next($request)
            ->where('holding_no', 'ilike', '%' . request()->input('holdingNo') . '%');
    }
}
