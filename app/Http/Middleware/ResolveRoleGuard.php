<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveRoleGuard
{
    /**
     * Resolusi authentication guard berdasarkan konteks request atau referer.
     * Memungkinkan sesi terpisah per role berjalan bersamaan di tab berbeda.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = $this->determineGuard($request);

        if ($guard) {
            Auth::shouldUse($guard);
            $request->setUserResolver(fn () => Auth::guard($guard)->user());
        }

        return $next($request);
    }

    /**
     * Tentukan guard yang relevan untuk request saat ini.
     */
    private function determineGuard(Request $request): ?string
    {
        $path = ltrim($request->path(), '/');

        // 1. Direct route path matching
        if (str_starts_with($path, 'kabag')) {
            return 'kabag';
        }

        if (str_starts_with($path, 'staff')) {
            if (Auth::guard('staff')->check()) {
                return 'staff';
            }
            if (Auth::guard('intern')->check()) {
                return 'intern';
            }
            return 'staff';
        }

        // 2. Request logout dengan spesifikasi role
        if ($path === 'logout') {
            $roleInput = $request->input('role');
            if (in_array($roleInput, ['kabag', 'staff', 'intern'], true)) {
                return $roleInput;
            }
        }

        // 3. Referer matching untuk Livewire update, AJAX, dan download file pengumpulan
        $referer = $request->header('referer');
        if ($referer) {
            $refererPath = parse_url($referer, PHP_URL_PATH) ?? '';
            $trimmedRefererPath = ltrim($refererPath, '/');

            if (str_starts_with($trimmedRefererPath, 'kabag')) {
                return 'kabag';
            }

            if (str_starts_with($trimmedRefererPath, 'staff')) {
                if (Auth::guard('staff')->check()) {
                    return 'staff';
                }
                if (Auth::guard('intern')->check()) {
                    return 'intern';
                }
                return 'staff';
            }
        }

        // 4. Fallback jika salah satu guard sedang aktif
        if (Auth::guard('kabag')->check()) {
            return 'kabag';
        }

        if (Auth::guard('staff')->check()) {
            return 'staff';
        }

        if (Auth::guard('intern')->check()) {
            return 'intern';
        }

        return null;
    }
}
