<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $trustedProxies = env('TRUSTED_PROXIES', '*');
        if ($trustedProxies === '*') {
            $middleware->trustProxies(at: '*');
        } elseif (blank($trustedProxies)) {
            // Do not trust any proxy by default in production-like setups if not configured
            $middleware->trustProxies(at: null);
        } else {
            $proxies = array_filter(array_map('trim', explode(',', (string) $trustedProxies)));
            $middleware->trustProxies(at: $proxies);
        }

        // Tamu yang belum login: portal/* -> portal.login, selain itu -> admin.login
        $middleware->redirectGuestsTo(function () {
            if (str_starts_with(request()->path(), 'portal')) {
                return route('portal.login');
            }

            return route('admin.login');
        });

        // Pengguna yang sudah login membuka halaman tamu (daftar/masuk):
        // portal/* -> portal.dashboard, selain itu -> admin.dashboard
        $middleware->redirectUsersTo(function () {
            if (str_starts_with(request()->path(), 'portal')) {
                return route('portal.dashboard');
            }

            return route('admin.dashboard');
        });

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'superadmin' => EnsureUserIsSuperAdmin::class,
            'applicant' => \App\Http\Middleware\EnsureUserIsApplicant::class,
        ]);

        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->renderable(function (Throwable $e, $request) {

            if (
                $e instanceof HttpException
                || $e instanceof HttpResponseException
                || $e instanceof ValidationException
                || $e instanceof AuthenticationException
                || $e instanceof AuthorizationException
                || $e instanceof ModelNotFoundException
            ) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan server.',
                ], 500);
            }

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Terjadi kesalahan tak terduga. Silakan coba lagi atau hubungi admin.'
                );
        });
    })
    ->create();
