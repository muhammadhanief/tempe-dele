<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        // 'login' dikecualikan: kredensial tetap diverifikasi ke SSO BPS, dan token yang
        // kedaluwarsa di halaman login hanya memunculkan pesan "Sesi Anda telah berakhir".
        $middleware->validateCsrfTokens(except: [
            'logout',
            'login',
        ]);

        $middleware->alias([
            'checksession' => \App\Http\Middleware\CheckSession::class,
            'role'         => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Token form tidak cocok (419). Jika pengguna masih login, kembalikan ke halaman sebelumnya
        // dengan isian utuh agar cukup kirim ulang; hanya sesi yang benar-benar hilang yang ke login.
        $tanganiTokenKedaluwarsa = function ($request) {
            $masihLogin = $request->hasSession() && $request->session()->get('logged_in');

            \Illuminate\Support\Facades\Log::warning('Token CSRF tidak cocok (419)', [
                'url'          => $request->path(),
                'masih_login'  => (bool) $masihLogin,
                'nip'          => $masihLogin ? ($request->session()->get('user')['nip'] ?? null) : null,
                'cookie_sesi'  => $request->cookies->has(config('session.cookie')),
                'user_agent'   => substr((string) $request->userAgent(), 0, 120),
            ]);

            $pesan = 'Halaman sudah kedaluwarsa sehingga data belum terkirim. Silakan kirim ulang.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $masihLogin ? $pesan : 'Sesi Anda telah berakhir. Silakan masuk kembali.'], 419);
            }

            if ($masihLogin) {
                return redirect()->back()
                    ->withInput($request->except(['_token', 'signature', 'password']))
                    ->withErrors(['token' => $pesan])
                    ->with('error', $pesan);
            }

            return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
        };

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) use ($tanganiTokenKedaluwarsa) {
            return $tanganiTokenKedaluwarsa($request);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) use ($tanganiTokenKedaluwarsa) {
            if ($e->getStatusCode() === 419) {
                return $tanganiTokenKedaluwarsa($request);
            }
        });
    })->create();
    