<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->esAdmin()) {
            return redirect()->route('dashboard')->with('ok', 'No tenés permiso para ingresar a esa sección.');
        }

        return $next($request);
    }
}