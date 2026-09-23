<?php

// app/Http/Controllers/Auth/AuthController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleAuthRequest;
use App\Http\Requests\Auth\KtpUploadRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PhoneOtpRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyEmailOtpRequest;
use App\Http\Requests\Auth\VerifyPhoneOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // -------------------------------------------------------------------------
    // Prefix key cache untuk OTP email
    // -------------------------------------------------------------------------
    private const EMAIL_OTP_CACHE_PREFIX = 'email_otp:';
    // Prefix key cache untuk OTP phone
    private const PHONE_OTP_CACHE_PREFIX = 'phone_otp:';
    // TTL OTP dalam menit
    private const OTP_TTL_MINUTES = 10;

    // =========================================================================
    // 1. POST /api/auth/register
    // =========================================================================

    /**
     * Mendaftarkan user baru dan mengirim OTP ke email.
     *
     * Strategi OTP: disimpan di Laravel Cache (driver: database) dengan key
     * "email_otp:{email}" dan TTL 10 menit. Pendekatan ini lebih simpel
     * dibanding tabel terpisah karena tidak butuh migration baru dan cache
     * sudah otomatis menangani expiry.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password, // model akan hash otomatis via cast 'hashed'
        ]);

        $otp = $this->generateAndCacheOtp(self::EMAIL_OTP_CACHE_PREFIX . $user->email);

        $user->notify(new EmailOtpNotification($otp));

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan cek email Anda untuk mendapatkan kode OTP.',
        ], 201);
    }

    // =========================================================================
    // 2. POST /api/auth/verify-email-otp
    // =========================================================================

    /**
     * Memverifikasi OTP email. Jika valid: set email_verified_at, hapus OTP dari
     * cache, dan buat Sanctum token.
     */
    public function verifyEmailOtp(VerifyEmailOtpRequest $request): JsonResponse
    {
        $cacheKey = self::EMAIL_OTP_CACHE_PREFIX . $request->email;
        $cached   = Cache::get($cacheKey);

        if ($cached === null) {
            return response()->json([
                'message' => 'Kode OTP sudah kedaluwarsa atau tidak ditemukan. Silakan daftar ulang.',
            ], 422);
        }

        if ($cached !== $request->otp) {
            return response()->json([
                'message' => 'Kode OTP tidak valid.',
                'errors'  => ['otp' => ['Kode OTP yang Anda masukkan salah.']],
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'Email tidak terdaftar.',
                'errors'  => ['email' => ['Email tidak ditemukan di sistem.']],
            ], 422);
        }

        // Set email_verified_at dan hapus OTP dari cache
        $user->email_verified_at = now();
        $user->save();
        Cache::forget($cacheKey);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email berhasil diverifikasi.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ]);
    }

    // =========================================================================
    // 3. POST /api/auth/login
    // =========================================================================

    /**
     * Login dengan email + password.
     * Hanya diizinkan jika email_verified_at sudah terisi.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // User yang daftar via Google OAuth tidak punya password — arahkan ke Google login
        if ($user->password === null) {
            return response()->json([
                'message' => 'Akun ini terdaftar via Google. Silakan login menggunakan Google.',
            ], 401);
        }

        if (! $user->email_verified_at) {
            return response()->json([
                'message' => 'Email Anda belum diverifikasi. Silakan cek email untuk kode OTP.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ]);
    }

    // =========================================================================
    // 4. POST /api/auth/google
    // =========================================================================

    /**
     * Login/register via Google Identity Services (id_token dari frontend).
     *
     * Alur verifikasi:
     * 1. Kirim id_token ke endpoint tokeninfo Google.
     * 2. Validasi field 'aud' agar cocok dengan GOOGLE_CLIENT_ID kita.
     * 3. Ambil data user dari response (email, name, sub, email_verified).
     * 4. Jika email sudah ada: link google_id ke akun existing (jangan buat duplikat).
     * 5. Jika belum ada: buat akun baru dengan email_verified_at terisi.
     */
    public function google(GoogleAuthRequest $request): JsonResponse
    {
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->id_token,
        ]);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Token Google tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $googleData = $response->json();

        // Validasi audience: pastikan token ini dibuat untuk aplikasi kita
        if (($googleData['aud'] ?? null) !== env('GOOGLE_CLIENT_ID')) {
            return response()->json([
                'message' => 'Token Google tidak ditujukan untuk aplikasi ini.',
            ], 401);
        }

        // Pastikan Google menyatakan email sudah diverifikasi
        if (($googleData['email_verified'] ?? 'false') !== 'true') {
            return response()->json([
                'message' => 'Email Google Anda belum terverifikasi.',
            ], 401);
        }

        $googleEmail = $googleData['email'];
        $googleSub   = $googleData['sub'];   // Google user ID unik
        $googleName  = $googleData['name'] ?? $googleEmail;

        $user = User::where('email', $googleEmail)->first();

        if ($user) {
            // Akun sudah ada — tautkan google_id jika belum ditautkan sebelumnya
            if (! $user->google_id) {
                $user->google_id = $googleSub;
                $user->save();
            }
        } else {
            // Akun belum ada — buat baru, email langsung terverifikasi (via Google)
            $user = User::create([
                'name'              => $googleName,
                'email'             => $googleEmail,
                'google_id'         => $googleSub,
                'email_verified_at' => now(),
                'password'          => null, // tidak ada password untuk user Google
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login dengan Google berhasil.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ]);
    }

    // =========================================================================
    // 5. POST /api/auth/logout (auth:sanctum)
    // =========================================================================

    /**
     * Revoke token Sanctum yang sedang digunakan saat ini.
     */
    public function logout(Request $request): JsonResponse
    {
        // currentAccessToken() mengembalikan token Bearer yang dipakai request ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    // =========================================================================
    // 6. GET /api/auth/me (auth:sanctum)
    // =========================================================================

    /**
     * Mengembalikan data user yang sedang login (dari token Sanctum yang aktif).
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    // =========================================================================
    // 7. POST /api/auth/phone/request-otp (auth:sanctum)
    // =========================================================================

    /**
     * Membangkitkan OTP untuk verifikasi nomor HP.
     *
     * TODO: Sambungkan gateway SMS/WA sungguhan di Tahap 10.
     * Saat ini hanya melakukan Log::info() sebagai stub — OTP tidak benar-benar dikirim via SMS.
     */
    public function requestPhoneOtp(PhoneOtpRequest $request): JsonResponse
    {
        $phone    = $request->phone;
        $cacheKey = self::PHONE_OTP_CACHE_PREFIX . $phone;

        $otp = $this->generateAndCacheOtp($cacheKey);

        // TODO: Sambungkan gateway SMS/WA sungguhan di Tahap 10.
        // Ganti Log::info di bawah ini dengan pemanggilan HTTP ke Twilio / Wablas / dll.
        Log::info("OTP untuk {$phone}: {$otp}");

        return response()->json([
            'message' => 'Kode OTP telah dikirim ke nomor HP Anda.',
        ]);
    }

    // =========================================================================
    // 8. POST /api/auth/phone/verify-otp (auth:sanctum)
    // =========================================================================

    /**
     * Memverifikasi OTP nomor HP. Jika valid: set phone dan phone_verified_at pada user.
     */
    public function verifyPhoneOtp(VerifyPhoneOtpRequest $request): JsonResponse
    {
        $phone    = $request->phone;
        $cacheKey = self::PHONE_OTP_CACHE_PREFIX . $phone;
        $cached   = Cache::get($cacheKey);

        if ($cached === null) {
            return response()->json([
                'message' => 'Kode OTP sudah kedaluwarsa atau tidak ditemukan. Silakan minta OTP baru.',
            ], 422);
        }

        if ($cached !== $request->otp) {
            return response()->json([
                'message' => 'Kode OTP tidak valid.',
                'errors'  => ['otp' => ['Kode OTP yang Anda masukkan salah.']],
            ], 422);
        }

        $user = $request->user();
        $user->phone              = $phone;
        $user->phone_verified_at  = now();
        $user->save();

        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'Nomor HP berhasil diverifikasi.',
            'user'    => new UserResource($user),
        ]);
    }

    // =========================================================================
    // 9. POST /api/auth/ktp/upload (auth:sanctum)
    // =========================================================================

    /**
     * Upload file KTP ke disk privat 'ktp'.
     * Nama file unik: ktp_{user_id}_{timestamp}.{ext}
     * Set ktp_file_path, ktp_status='pending_review', ktp_submitted_at.
     *
     * Review/approve KTP dilakukan admin — lihat Tahap 7.6.
     */
    public function ktpUpload(KtpUploadRequest $request): JsonResponse
    {
        $user = $request->user();
        $file = $request->file('ktp_file');

        // Nama file deterministik berdasarkan user id + timestamp untuk menghindari tebakan nama
        $filename = 'ktp_' . $user->id . '_' . now()->timestamp . '.' . $file->getClientOriginalExtension();

        // Hapus file KTP lama jika ada (user upload ulang setelah rejected)
        if ($user->ktp_file_path && Storage::disk('ktp')->exists($user->ktp_file_path)) {
            Storage::disk('ktp')->delete($user->ktp_file_path);
        }

        $path = $file->storeAs('', $filename, 'ktp');

        $user->ktp_file_path  = $path;
        $user->ktp_status     = 'pending_review';
        $user->ktp_submitted_at = now();
        $user->save();

        return response()->json([
            'message' => 'KTP berhasil diunggah dan sedang dalam proses verifikasi admin.',
            'user'    => new UserResource($user),
        ]);
    }

    // =========================================================================
    // Helper private
    // =========================================================================

    /**
     * Membangkitkan OTP 6 digit, menyimpan ke cache dengan TTL 10 menit,
     * dan mengembalikan nilai OTP sebagai string.
     *
     * @param string $cacheKey Key unik untuk cache (sudah termasuk prefix).
     * @return string Kode OTP 6 digit.
     */
    private function generateAndCacheOtp(string $cacheKey): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put($cacheKey, $otp, now()->addMinutes(self::OTP_TTL_MINUTES));
        return $otp;
    }
}
