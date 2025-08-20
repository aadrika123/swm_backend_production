<?php

namespace App\Pipelines;

use Closure;


class SearchByConsumer
{
    public function handle($request, Closure $next)
    {
        if (!request()->has('consumerNo')) {
            return $next($request);
        }
        return $next($request)
            ->where('consumer_no', 'ilike', '%' . request()->input('consumerNo') . '%');
    }
}
