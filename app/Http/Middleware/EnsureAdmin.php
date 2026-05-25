<?php

namespace App\Http\Middleware;

use App\Support\FirmaContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! FirmaContext::isAdmin()) {
            abort(403, 'Šī darbība pieejama tikai administratoram.');
        }

        return $next($request);
    }
}
