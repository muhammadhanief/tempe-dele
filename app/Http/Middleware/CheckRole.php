<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CheckRole
{
    /**
     * Menangani verifikasi wewenang peran (Role-Based Access Control).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Pastikan pengguna sudah terautentikasi / memiliki sesi login
        if (!Session::get('logged_in')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // 2. Ambil peran pengguna saat ini (dari session atau fallback database)
        $userRole = Session::get('role') ?? (Session::get('user')['role'] ?? null);

        if (!$userRole && Session::has('user.nip')) {
            $userRole = DB::table('m_pegawai')->where('nip', Session::get('user')['nip'])->value('role');
            if ($userRole) {
                Session::put('role', $userRole);
            }
        }

        // 3. Superadmin memiliki wewenang penuh ke semua level
        if ($userRole === 'superadmin') {
            return $next($request);
        }

        // 4. Periksa apakah peran pengguna termasuk salah satu yang diizinkan
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // 5. Jika tidak memiliki akses yang sesuai
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses fitur ini.'
            ], 403);
        }

        abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses halaman ini.');
    }
}
