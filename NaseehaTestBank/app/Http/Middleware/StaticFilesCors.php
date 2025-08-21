<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StaticFilesCors
{
    public function handle(Request $request, Closure $next)
    {
        // التحقق من أن الطلب للملفات الثابتة
        if ($request->is('storage/*')) {
            $response = $next($request);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin');
            
            return $response;
        }

        return $next($request);
    }
}