<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // $roles adalah array dari peran yang diizinkan (misal: 'sdm', 'pengurus')
        if (! $request->user() || ! in_array($request->user()->role->value, $roles)) {
            abort(403, 'ANDA TIDAK PUNYA AKSES.');
        }

        return $next($request);
    }
}