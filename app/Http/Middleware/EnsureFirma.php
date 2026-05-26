<?php

namespace App\Http\Middleware;

use App\Support\FirmaContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFirma
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! FirmaContext::firma()) {
            return redirect()
                ->to('/')
                ->with('error', 'Jūsu konts nav piesaistīts uzņēmumam. Sazinieties ar administratoru.');
        }

        return $next($request);
    }
}
