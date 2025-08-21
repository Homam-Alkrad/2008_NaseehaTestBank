<?php
// app/Http/Middleware/ApiMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // إعداد headers للـ CORS
        $response = $next($request);
        
        // التأكد من أن الاستجابة JSON
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        }
        
        // إضافة CORS headers
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        $response->headers->set('Access-Control-Max-Age', '86400');
        
        // التعامل مع طلبات OPTIONS
        if ($request->getMethod() === 'OPTIONS') {
            $response = response()->json([], 200);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        }
        
        return $response;
    }
}

// لا تنس إضافة هذا إلى app/Http/Kernel.php في $middlewareGroups['api']:
// \App\Http\Middleware\ApiMiddleware::class,