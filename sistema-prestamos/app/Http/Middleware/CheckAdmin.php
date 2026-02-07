<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->esAdmin()) {
            return redirect()->route('dashboard')
                ->with('error', '❌ Acceso denegado. Solo administradores pueden realizar esta acción.');
        }

        return $next($request);
    }
}