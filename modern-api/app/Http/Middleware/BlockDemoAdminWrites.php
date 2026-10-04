<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockDemoAdminWrites
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.admin_demo_read_only')) {
            return $next($request);
        }

        return response()->json([
            'code' => 'DEMO_READ_ONLY',
            'message' => config('app.admin_demo_read_only_message'),
        ], 403);
    }
}
