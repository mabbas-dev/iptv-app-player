<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\Reseller;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key') ?? $request->query('api_key');

        if (! $key) {
            return response()->json([
                'success' => false,
                'message' => 'API key is missing. Send it in the X-API-Key header.',
            ], 401);
        }

        $apiKey = ApiKey::with('reseller')->where('key', $key)->where('is_active', true)->first();

        if (! $apiKey || $apiKey->reseller?->status !== Reseller::STATUS_ACTIVE) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive API key.',
            ], 401);
        }

        $request->attributes->set('api_key', $apiKey);
        $request->attributes->set('reseller', $apiKey->reseller);

        $response = $next($request);

        $apiKey->update(['last_used_at' => now()]);
        $apiKey->usageLogs()->create([
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'status_code' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
