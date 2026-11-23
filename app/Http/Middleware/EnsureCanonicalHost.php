<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanonicalHost
{
    /**
     * Pastikan host di lingkungan lokal selaras antara 127.0.0.1 dan localhost sesuai config('app.url').
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local')) {
            $configuredHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            $currentHost = $request->getHost();

            $localHosts = ['localhost', '127.0.0.1'];

            if ($configuredHost && in_array($configuredHost, $localHosts, true) && in_array($currentHost, $localHosts, true)) {
                if ($currentHost !== $configuredHost) {
                    $scheme = $request->getScheme();
                    $port = $request->getPort();
                    $portSuffix = ($port && ! in_array($port, [80, 443], true)) ? ':'.$port : '';

                    $targetOrigin = $scheme.'://'.$configuredHost.$portSuffix;
                    $newUrl = str_replace($request->getSchemeAndHttpHost(), $targetOrigin, $request->fullUrl());

                    return redirect()->to($newUrl);
                }
            }
        }

        return $next($request);
    }
}
