<?php

// app/Http/Middleware/EnsureUserIsAdmin.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memastikan user yang login memiliki role 'admin'.
 * Harus digunakan bersamaan dengan middleware auth:sanctum (yang memastikan user sudah login).
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()?->role !== 'admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya admin yang dapat mengakses endpoint ini.',
            ], 403);
        }

        return $next($request);
    }
}
