<?php

// routes/api/auth.php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    // -------------------------------------------------------------------------
    // Endpoint publik (tidak butuh token)
    // -------------------------------------------------------------------------

    /** POST /api/auth/register — Daftar dengan email+password, kirim OTP email */
    Route::post('/register', [AuthController::class, 'register']);

    /** POST /api/auth/verify-email-otp — Verifikasi OTP email, terbitkan token */
    Route::post('/verify-email-otp', [AuthController::class, 'verifyEmailOtp']);

    /** POST /api/auth/login — Login email+password */
    Route::post('/login', [AuthController::class, 'login']);

    /** POST /api/auth/google — Login/register via Google id_token */
    Route::post('/google', [AuthController::class, 'google']);

    // -------------------------------------------------------------------------
    // Endpoint terproteksi (butuh Bearer token Sanctum)
    // -------------------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {

        /** POST /api/auth/logout — Revoke token aktif */
        Route::post('/logout', [AuthController::class, 'logout']);

        /** GET /api/auth/me — Data user yang sedang login */
        Route::get('/me', [AuthController::class, 'me']);

        /** POST /api/auth/phone/request-otp — Minta OTP untuk verifikasi nomor HP */
        Route::post('/phone/request-otp', [AuthController::class, 'requestPhoneOtp']);

        /** POST /api/auth/phone/verify-otp — Verifikasi OTP nomor HP */
        Route::post('/phone/verify-otp', [AuthController::class, 'verifyPhoneOtp']);

        /** POST /api/auth/ktp/upload — Upload file KTP (jpg/png/pdf, maks 5MB) */
        Route::post('/ktp/upload', [AuthController::class, 'ktpUpload']);
    });
});

