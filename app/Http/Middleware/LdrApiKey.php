<?php

namespace App\Http\Middleware;

use Closure;

class LdrApiKey {
    public function handle($request, Closure $next) {
        $expected = (string) config('services.ldr.api_key');
        $provided = (string) $request->header('X-DMS-Key');

        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
