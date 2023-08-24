<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('sanctum')->user();

        // Store all the resources that can be checked for ownership
        $resources = [
            'store',
            'product',
        ];


        // Check injected dependency's ownership
        if (in_array($request->route()->parameterNames()[0], $resources)) {
            $resource = $request->route()->parameterNames()[0];
            $resource = $request->route()->parameter($resource);
            if ($resource->user_id !== $user->id) {
                return response()->json([
                    'message' => 'This resource does not belong to the authenticated user.'
                ], 403);
            }
        }

        return $next($request);
    }
}
