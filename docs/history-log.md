# History Log — Medical Equipment Marketplace

## Tahap 1 — Requirement
**Status:** Selesai
**Ringkasan:** Menganalisis kebutuhan proyek marketplace jual-beli & sewa alat medis. Mengunci FR-01 s/d FR-31 (termasuk 11 FR tambahan dari analisis kebijakan bisnis), dengan revisi pada FR-27 (deposit sewa dihapus, full payment) dan FR-28 (verifikasi KTP wajib untuk sewa, per-akun, admin manual screening).
**Keputusan penting:** Tidak ada sistem deposit sewa; KTP wajib untuk transaksi sewa dengan verifikasi per-akun oleh admin; keranjang mendukung campuran item beli & sewa.
**File/struktur utama yang dihasilkan:** functional-requirements-lengkap.md, notulensi-tahap-1-requirement.md, medical_marketplace_business_requirements_policies.md

## Tahap 2 — Technology Selection
**Status:** Selesai (direvisi total setelah keputusan awal)
**Ringkasan:** Keputusan awal sempat memilih Laravel 12 monolith (Inertia+React+MySQL), namun kemudian di-pivot penuh ke arsitektur decoupled: Laravel 12 API-only + Next.js frontend terpisah, PostgreSQL menggantikan MySQL.
**Keputusan penting:** Stack final -- Backend: Laravel 12 API-only + Sanctum (token-based/Bearer auth). Frontend: Next.js (App Router, TypeScript strict) + TanStack Query + Zustand + React Hook Form + Zod + Tailwind CSS. Database: PostgreSQL. Payment: Midtrans. Repo: monorepo (/frontend, /backend).
**File/struktur utama yang dihasilkan:** pipeline-v2-revisi.md, ai-agent-manifest.md, skills.md

## Tahap 3 — Repository Setup
**Status:** Selesai
**Ringkasan:** Repository GitHub monorepo dibuat, struktur folder /frontend, /backend, /docs disiapkan, seluruh 19 file knowledge base dimasukkan ke docs/, commit pertama berhasil di-push ke branch main.
**Keputusan penting:** .gitignore hanya di root repo (bukan per-folder frontend/backend).
**File/struktur utama yang dihasilkan:** Struktur repo dasar, README.md root, .gitignore root, docs/ (19 file)

## Tahap 4 — Environment Installation
**Status:** Selesai
**Ringkasan:** PostgreSQL 16 diinstall di Windows (terpisah dari XAMPP yang sudah ada untuk PHP/MySQL sebelumnya), ekstensi pdo_pgsql & pgsql diaktifkan di php.ini, database medical_equipment_marketplace dibuat.
**Keputusan penting:** Port PostgreSQL dikoreksi dari 5433 (default installer) ke 5432 (standar) untuk konsistensi konfigurasi ke depan.
**File/struktur utama yang dihasilkan:** Database medical_equipment_marketplace (PostgreSQL, port 5432)

## Tahap 5 — Project Architecture
**Status:** Selesai
**Ringkasan:** Project Laravel 12 (API-only) di-generate di /backend dengan Sanctum token-based auth, CORS, koneksi PostgreSQL (migration bawaan sukses), storage privat untuk KTP, dan routing modular. Project Next.js di-generate di /frontend (App Router, TypeScript strict, Tailwind) dengan TanStack Query, Zustand, React Hook Form+Zod, axios terpasang, plus skeleton folder & API client dasar.
**Keputusan penting:** Sanctum menggunakan token-based (Bearer) auth, bukan cookie-based SPA, supaya CORS antar origin (frontend:3000, backend:8000) tetap sederhana tanpa perlu stateful domain/CSRF.
**File/struktur utama yang dihasilkan:** backend/ (Laravel API-only lengkap), frontend/ (Next.js lengkap), backend/routes/api/*.php, frontend/src/lib/api-client.ts, frontend/src/app/providers.tsx

## Tahap 6 — Database Design
**Status:** Selesai
**Ringkasan:** Seluruh tabel bisnis inti telah diterjemahkan menjadi file migration dan Eloquent Model (dengan properties $fillable, $casts, dan pendefinisian relasi) sesuai skema PostgreSQL. Tambahan kolom bisnis untuk tabel users digabungkan melalui satu file migration terpisah. Seeder awal untuk Category dan Admin juga berhasil dibuat serta database telah di-seed dengan sukses.
**Keputusan penting:** Constraint `cascadeOnDelete` diterapkan secara ketat hanya pada entitas anak langsung (misal: `product_images`, `cart_items`), dan `restrictOnDelete` pada tabel transaksional (`orders`, `rental_items`, dll) mencegah insiden penghapusan data. Tipe data menggunakan fitur native Postgres seperti `jsonb` untuk data statis spesifikasi.
**File/struktur utama yang dihasilkan:** 15 file di `backend/database/migrations/`, 14 file di `backend/app/Models/`, `CategorySeeder.php`, `AdminUserSeeder.php`

## Tahap 7.1 — Auth & Account
**Status:** Selesai
**Ringkasan:** Semua 9 endpoint auth dibangun di `routes/api/auth.php` via `AuthController`: register (OTP email via Laravel Notification + Cache database, TTL 10 menit), verify-email-otp (validasi OTP → issue Sanctum token), login (email+password + cek email_verified_at), google (verifikasi id_token ke Google tokeninfo API, link/buat akun), logout (revoke token aktif), me (UserResource), phone/request-otp (stub SMS via Log::info), phone/verify-otp, dan ktp/upload (disk privat 'ktp', status pending_review).
**Keputusan penting:** OTP disimpan di Laravel Cache (driver: database) bukan tabel terpisah — lebih simpel, expiry otomatis ditangani framework. UserResource mengekspos ktp_status tanpa ktp_file_path mentah. Autentikasi Google menggunakan tokeninfo endpoint (server-side validation) tanpa library OAuth tambahan. Phone OTP stub via Log::info dengan komentar TODO Tahap 10.
**File/struktur utama yang dihasilkan:** `app/Http/Controllers/Auth/AuthController.php`, `app/Http/Requests/Auth/` (6 Form Request), `app/Http/Resources/UserResource.php`, `app/Notifications/EmailOtpNotification.php`, `routes/api/auth.php` (9 routes), `.env` + `.env.example` (GOOGLE_CLIENT_ID)
