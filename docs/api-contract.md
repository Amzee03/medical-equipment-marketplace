# API Contract — Medical Equipment Marketplace

> **Sumber kebenaran untuk bentuk (shape) response API.** Isi di bawah ini **wajib** diambil langsung dari kode `Resource` class / `Controller` yang sungguhan — bukan dari asumsi, dokumentasi lain, atau skema database. Dibuat untuk mencegah mismatch field name antara frontend & backend (lihat insiden Tahap 8.2: `token` vs `access_token`, `id_token` vs `credential`).

**Status:** ✅ Auth, Category, Product — sudah diisi dari source code aktual (Tahap 8.2 follow-up).

---

## Cara Menjaga Dokumen Ini Tetap Akurat

- Setiap kali sebuah `Resource` class dibuat/diubah, **update bagian terkait di file ini di commit yang sama**.
- Isi harus **field name persis** seperti yang benar-benar dikembalikan (cek method `toArray()` di tiap Resource), bukan nama kolom database mentah kalau Resource me-rename/transform.
- Sertakan: method HTTP + path, auth yang dibutuhkan (publik/`auth:sanctum`/admin), contoh shape response sukses, dan field request (body) yang diharapkan untuk endpoint POST/PATCH/PUT.

---

## Auth (Tahap 7.1)

> Source: `app/Http/Controllers/Auth/AuthController.php` + `app/Http/Resources/UserResource.php`

### `UserResource` — shape dipakai di semua endpoint auth

**Skenario A — user tanpa Google:**
```json
{
  "id": 1,
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "email_verified_at": "2026-09-22T03:32:04+00:00",
  "phone": null,
  "phone_verified_at": null,
  "role": "user",
  "has_google_linked": false,
  "ktp_status": null,
  "ktp_submitted_at": null,
  "created_at": "2026-09-22T03:32:04+00:00"
}
```

**Skenario B — user dengan Google linked:**
```json
{
  "id": 2,
  "name": "Budi Santoso",
  "email": "budi@gmail.com",
  "email_verified_at": "2026-09-22T03:32:04+00:00",
  "phone": null,
  "phone_verified_at": null,
  "role": "user",
  "google_id": true,
  "has_google_linked": true,
  "ktp_status": null,
  "ktp_submitted_at": null,
  "created_at": "2026-09-22T03:32:04+00:00"
}
```

> **`has_google_linked`**: selalu hadir, boolean `true`/`false`.  
> **`google_id`**: key ini **absen sama sekali** untuk user tanpa Google (bukan `null` — benar-benar tidak ada di response karena `$this->when(...)`). Ketika ada (user Google), nilainya `true` (bool cast dari ID string) — bukan ID aslinya. **Gunakan `has_google_linked` saja** di frontend untuk cek status Google linkage.  
> **`ktp_rejection_reason`**: key ini **hanya muncul** jika `ktp_status === "rejected"` (conditional via `$this->when(...)`).

---

### POST `/api/auth/register`

**Auth:** Publik  
**Request body:**
```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response 201 (sukses):**
```json
{
  "message": "Registrasi berhasil. Silakan cek email Anda untuk mendapatkan kode OTP."
}
```

> Tidak ada `token` atau `user` di response ini. Token diterbitkan setelah verifikasi OTP.

---

### POST `/api/auth/verify-email-otp`

**Auth:** Publik  
**Request body:**
```json
{
  "email": "budi@example.com",
  "otp": "123456"
}
```

**Response 200 (sukses):**
```json
{
  "message": "Email berhasil diverifikasi.",
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": { "...UserResource..." }
}
```

> ⚠️ Field token adalah **`token`**, bukan `access_token`. (Dikonfirmasi dari insiden Tahap 8.2.)

**Response 422 — OTP kedaluwarsa:**
```json
{ "message": "Kode OTP sudah kedaluwarsa atau tidak ditemukan. Silakan daftar ulang." }
```

**Response 422 — OTP salah:**
```json
{
  "message": "Kode OTP tidak valid.",
  "errors": { "otp": ["Kode OTP yang Anda masukkan salah."] }
}
```

---

### POST `/api/auth/login`

**Auth:** Publik  
**Request body:**
```json
{
  "email": "budi@example.com",
  "password": "password123"
}
```

**Response 200 (sukses):**
```json
{
  "message": "Login berhasil.",
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": { "...UserResource..." }
}
```

> ⚠️ Field token adalah **`token`**, bukan `access_token`.

**Response 401 — email/password salah:**
```json
{ "message": "Email atau password salah." }
```

**Response 401 — akun Google (tidak punya password):**
```json
{ "message": "Akun ini terdaftar via Google. Silakan login menggunakan Google." }
```

**Response 403 — email belum diverifikasi:**
```json
{ "message": "Email Anda belum diverifikasi. Silakan cek email untuk kode OTP." }
```

---

### POST `/api/auth/google`

**Auth:** Publik  
**Request body:**
```json
{
  "id_token": "<Google ID token dari GSI callback>"
}
```

> ⚠️ Field request adalah **`id_token`**, bukan `credential`. (Dikonfirmasi dari insiden Tahap 8.2.)

**Response 200 (sukses):**
```json
{
  "message": "Login dengan Google berhasil.",
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": { "...UserResource..." }
}
```

**Response 401 — token Google tidak valid/kedaluwarsa:**
```json
{ "message": "Token Google tidak valid atau sudah kedaluwarsa." }
```

**Response 401 — audience tidak cocok dengan GOOGLE_CLIENT_ID:**
```json
{ "message": "Token Google tidak ditujukan untuk aplikasi ini." }
```

**Response 401 — email Google belum terverifikasi:**
```json
{ "message": "Email Google Anda belum terverifikasi." }
```

---

### POST `/api/auth/logout`

**Auth:** `auth:sanctum` (Bearer token wajib)  
**Response 200:**
```json
{ "message": "Logout berhasil." }
```

---

### GET `/api/auth/me`

**Auth:** `auth:sanctum` (Bearer token wajib)  
**Response 200:**
```json
{
  "user": { "...UserResource..." }
}
```

---

## Category (Tahap 7.2)

> Source: `app/Http/Controllers/Api/CategoryController.php` + `app/Http/Resources/CategoryResource.php`

### `CategoryResource` — shape

```json
{
  "id": 1,
  "name": "Alat Diagnostik",
  "slug": "alat-diagnostik",
  "description": "Peralatan untuk keperluan diagnosis medis.",
  "status": "active",
  "created_at": "2026-09-22T03:32:04+00:00",
  "updated_at": "2026-09-22T03:32:04+00:00"
}
```

---

### GET `/api/categories`

**Auth:** Publik  
**Keterangan:** Hanya mengembalikan kategori dengan `status = "active"`, diurutkan by `name` ASC. Tidak dipaginasi.

**Response 200:**
```json
{
  "data": [
    { "...CategoryResource..." },
    { "...CategoryResource..." }
  ]
}
```

> Response adalah **flat array** di key `data` — tidak ada `meta` atau `links`.

---

## Product (Tahap 7.2)

> Source: `app/Http/Controllers/Api/ProductController.php` + `app/Http/Resources/ProductResource.php` + `app/Http/Resources/ProductImageResource.php`

### `ProductImageResource` — shape

```json
{
  "id": 1,
  "product_id": 10,
  "url": "http://localhost:8000/storage/products/image.jpg",
  "path": "products/image.jpg",
  "is_primary": true,
  "sort_order": 1
}
```

> **`url`**: dihasilkan dari `Storage::disk('public')->url($this->path)` — bukan path mentah.

---

### `ProductResource` — shape lengkap

```json
{
  "id": 10,
  "category_id": 1,
  "category": { "...CategoryResource..." },
  "name": "Ventilator ICU Pro",
  "slug": "ventilator-icu-pro",
  "sku": "VNT-ICU-001",
  "description": "Ventilator untuk pasien ICU.",
  "function": "Membantu pernapasan pasien kritis.",
  "brand": "Philips",
  "model": "V60",
  "specifications": { "berat": "12kg", "dimensi": "40x30x50cm" },
  "condition": "baru",
  "purchase_available": true,
  "sale_price": "45000000.00",
  "stock_purchase": 5,
  "rental_available": true,
  "rental_price_daily": "500000.00",
  "rental_price_weekly": "3000000.00",
  "rental_price_monthly": "10000000.00",
  "min_rental_days": 1,
  "max_rental_days": 365,
  "shipping_owner_delivery": true,
  "shipping_express": true,
  "shipping_regular": true,
  "shipping_pickup": false,
  "status": "active",
  "available_units_count": 3,
  "images": [
    { "...ProductImageResource..." }
  ],
  "created_at": "2026-09-22T03:32:04+00:00",
  "updated_at": "2026-09-22T03:32:04+00:00"
}
```

> **`category`**: hanya muncul jika relasi di-eager load (`with('category')`). Di endpoint list & show publik, selalu ada.  
> **`available_units_count`**: hanya muncul jika relasi `equipmentUnits` di-eager load. Di endpoint `GET /api/products/{slug}` (show) selalu ada; di `GET /api/products` (index) **tidak ada**.  
> **`images`**: hanya muncul jika relasi `images` di-eager load. Di list & show publik, selalu ada.  
> **`condition`**: enum — nilai valid: `"baru"` | `"bekas_baik"` | `"perlu_pemeriksaan"`. Default DB: `"baru"`. (Dikonfirmasi dari migration & tinker audit Tahap 8.2.)  
> **`specifications`**: tipe JSON/object (JSONB di PostgreSQL), bisa `null`. Shape bergantung pada data masing-masing produk.  
> **`sale_price`**, **`stock_purchase`**, **`rental_price_*`**, **`min/max_rental_days`**: semua nullable di DB — bisa `null` jika produk tidak mendukung mode tersebut.

---

### GET `/api/products`

**Auth:** Publik  
**Query params:**

| Param | Tipe | Keterangan |
|---|---|---|
| `category_id` | integer | Filter by kategori |
| `q` | string | Full-text search ILIKE ke `name`, `description`, `function`, `brand`, `model` |
| `min_price` | numeric | Harga minimum (`sale_price` OR `rental_price_daily`) |
| `max_price` | numeric | Harga maksimum (`sale_price` OR `rental_price_daily`) |
| `purchase_available` | boolean | Jika `true`, filter hanya produk bisa dibeli |
| `rental_available` | boolean | Jika `true`, filter hanya produk bisa disewa |
| `sort` | string | `name_asc` \| `name_desc` \| `price_asc` \| `price_desc` \| `newest` (default: `newest`) |

**Response 200:**
```json
{
  "data": [
    { "...ProductResource (tanpa available_units_count)..." }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

> Paginasi: **15 item per halaman**. Tidak ada key `links` — hanya `meta`.  
> `available_units_count` **tidak ada** di response list (relasi `equipmentUnits` tidak di-load di `index()`).

---

### GET `/api/products/{slug}`

**Auth:** Publik  
**Param route:** `slug` (string) — bukan `id` integer.

**Response 200:**
```json
{
  "data": { "...ProductResource lengkap (termasuk available_units_count & images)..." }
}
```

> Eager loads: `category`, `images` (sorted: `is_primary DESC`, `sort_order ASC`), `equipmentUnits` (untuk `available_units_count`).  
> **Response 404** jika slug tidak ditemukan atau `status` bukan `active`.

---

### GET `/api/products/{id}/availability`

**Auth:** Publik  
**Param route:** `id` (integer)  
**Query params (semua wajib):**

| Param | Format | Validasi |
|---|---|---|
| `start_date` | `Y-m-d` | `required`, `after_or_equal:today` |
| `end_date` | `Y-m-d` | `required`, `after_or_equal:start_date` |

**Response 200 (sukses):**
```json
{
  "data": {
    "available": true,
    "available_units_count": 3
  }
}
```

> **`available`**: boolean — `true` jika ada ≥ 1 unit yang tidak konflik di periode tersebut.  
> **`available_units_count`**: jumlah unit tersedia di periode tersebut (dari `RentalAvailabilityService::checkAvailability()`).  
> **Response 422** jika `rental_available = false`: `{ "message": "Produk tidak tersedia untuk disewa." }`

---

## Cart (Tahap 7.3)

_TODO — akan diisi saat Tahap 8.3 frontend cart dimulai._

---

## Checkout & Order (Tahap 7.4, 7.5)

_TODO — akan diisi saat sub-tahap terkait tiba._

---

## Catatan

Bagian `_TODO_` di atas akan diisi bertahap sesuai kebutuhan sub-tahap frontend berikutnya. Jangan isi dari asumsi — selalu baca kode Resource/Controller aktual terlebih dahulu.