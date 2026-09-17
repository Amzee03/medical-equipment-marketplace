# Database Schema Final — Medical Equipment Marketplace
## v2 — PostgreSQL, Arsitektur Decoupled (Laravel API + Next.js)

**Status:** Final, siap diimplementasikan sebagai migration.
**Perubahan dari draf sebelumnya:** Engine database MySQL → **PostgreSQL**. Struktur tabel bisnis inti **tidak berubah** dari desain yang sudah disepakati; hanya menambahkan tabel bawaan Sanctum untuk auth berbasis token, dan catatan penyesuaian tipe data khusus PostgreSQL.

---

## Catatan Khusus PostgreSQL

- Primary key tetap pakai `bigserial` (setara `bigIncrements` di Laravel — Eloquent otomatis menyesuaikan berdasarkan driver, tidak perlu ubah kode migration).
- Kolom `json`/`jsonb` — gunakan `jsonb` untuk field seperti `specifications` dan `raw_response` (lebih efisien untuk query di PostgreSQL dibanding `json` biasa).
- Enum: Laravel migration `$table->enum()` tetap kompatibel dengan PostgreSQL (di-generate sebagai `CHECK` constraint secara otomatis oleh Laravel).

## Tabel Tambahan (Auth — Sanctum)

### `personal_access_tokens` *(auto-generate dari `php artisan install:api`)*
Tabel bawaan Laravel Sanctum untuk menyimpan token API. Tidak perlu didesain manual — akan otomatis dibuat saat instalasi Sanctum di Tahap 5.

---

## Entitas Bisnis Inti (Tidak Berubah dari Desain Sebelumnya)

### 1. `users`
| Field | Tipe | Keterangan |
|---|---|---|
| id | bigserial PK | |
| name | string | |
| email | string, unique | |
| email_verified_at | timestamp, nullable | otomatis terisi jika daftar via Google OAuth |
| google_id | string, unique, nullable | diisi jika user login/daftar via Google OAuth |
| phone | string, unique, nullable | wajib diisi & diverifikasi saat checkout pertama kali, bukan saat registrasi |
| phone_verified_at | timestamp, nullable | |
| password | string, **nullable** | nullable karena user yang daftar via Google OAuth tidak selalu set password |
| role | enum(user, admin) | default `user` |
| ktp_file_path | string, nullable | disk private |
| ktp_status | enum(not_submitted, pending_review, approved, rejected) | default `not_submitted`, **per-akun** |
| ktp_submitted_at | timestamp, nullable | |
| ktp_reviewed_at | timestamp, nullable | |
| ktp_reviewed_by | FK → users.id, nullable | |
| ktp_rejection_reason | text, nullable | |
| timestamps | | |

### 2. `addresses`
`id, user_id (FK), label, recipient_name, phone, full_address, city, province, postal_code, is_default (bool), timestamps`

### 3. `categories`
`id, name, slug (unique), description, status (active/inactive), timestamps`

### 4. `products`
| Field | Tipe | Keterangan |
|---|---|---|
| id | bigserial PK | |
| category_id | FK → categories.id | |
| name, slug (unique), sku (unique) | string | |
| description, function | text | |
| brand, model | string | |
| specifications | **jsonb** | |
| condition | enum(baru, bekas_baik, perlu_pemeriksaan) | |
| purchase_available | boolean | |
| sale_price | decimal, nullable | |
| stock_purchase | integer, nullable | |
| rental_available | boolean | |
| rental_price_daily/weekly/monthly | decimal, nullable | |
| min_rental_days, max_rental_days | integer, nullable | FR-29 |
| shipping_owner_delivery/express/regular/pickup | boolean (masing-masing) | FR-22 |
| status | enum(active, inactive) | |
| timestamps | | |

### 5. `product_images`
`id, product_id (FK), path, is_primary (bool), sort_order (int), timestamps`

### 6. `equipment_units`
`id, product_id (FK), unit_code (unique), condition (enum: baik/rusak_ringan/rusak_berat/hilang, default baik), status (enum: available/rented/maintenance/retired, default available), notes, timestamps`

### 7. `carts`
`id, user_id (FK, unique), timestamps`

### 8. `cart_items`
`id, cart_id (FK), product_id (FK), type (enum: purchase/rental), quantity (default 1), rental_start_date, rental_end_date (date, nullable), rental_pricing_tier (enum: daily/weekly/monthly, nullable), timestamps`

### 9. `payments`
`id, invoice_number (unique), user_id (FK), payment_gateway (default midtrans), gateway_transaction_id (nullable), payment_method (nullable), amount (decimal), status (enum: pending/paid/failed/expired/refunded, default pending), paid_at (nullable), raw_response (jsonb, nullable), timestamps`

### 10. `orders`
| Field | Tipe | Keterangan |
|---|---|---|
| id | bigserial PK | |
| user_id | FK → users.id | |
| payment_id | FK → payments.id, nullable | 1 payment bisa menaungi >1 order |
| order_number | string, unique | |
| order_type | enum(purchase, rental) | homogen per order |
| status | string | makna beda tergantung order_type |
| subtotal, shipping_cost, total | decimal | |
| shipping_method | enum(owner_delivery, express, regular, pickup) | |
| address_id | FK → addresses.id, nullable | referensi saja |
| shipping_recipient_name, shipping_phone | string | **snapshot** |
| shipping_full_address, shipping_city, shipping_province, shipping_postal_code | string/text | **snapshot** |
| cancelled_at | timestamp, nullable | |
| cancelled_by | FK → users.id, nullable | |
| cancellation_reason | text, nullable | |
| timestamps | | |

### 11. `order_items`
`id, order_id (FK), product_id (FK), quantity, unit_price, subtotal, timestamps`

### 12. `rental_items`
`id, order_item_id (FK), equipment_unit_id (FK), start_date, end_date, rental_due_date (date), actual_return_date (date, nullable), late_days (int, default 0), late_fee (decimal, default 0), condition_at_return (enum, nullable), rental_status (enum: confirmed/active/awaiting_return/overdue/returned/completed), timestamps`

### 13. `rental_agreements`
`id, order_id (FK, unique), agreed_at (timestamp), agreement_version (string), snapshot_content (text), timestamps`

### 14. `damage_reports`
`id, rental_item_id (FK), reported_by (FK users.id), condition (enum: rusak_ringan/rusak_berat/hilang), description (text), repair_cost, replacement_cost (decimal, nullable), status (enum: pending/charged/waived), timestamps`

### 15. `refunds`
`id, order_id (FK), category (enum: owner_fault/item_mismatch/user_cancellation), amount (decimal), status (enum: pending/approved/rejected/completed), reason (text), processed_by (FK users.id, nullable), timestamps`

---

## Diagram Relasi

```text
User ──┬── Cart ── CartItem ── Product
       ├── Address (banyak)
       ├── PersonalAccessToken (Sanctum, auto)
       ├── Order (banyak, purchase & rental terpisah) ── Payment (1:banyak)
       │        └── OrderItem ── Product
       │              └── RentalItem ── EquipmentUnit ── Product
       │                      └── DamageReport
       ├── Order ── RentalAgreement (khusus order_type=rental)
       └── Order ── Refund
```

---

## Keputusan Desain Kunci (Tidak Berubah)

1. Order dipisah per tipe (purchase vs rental) untuk state machine bersih.
2. 1 payment bisa menaungi >1 order (checkout campuran tetap 1x bayar).
3. Stok sewa per-unit tracking (`equipment_units`); stok beli tetap integer sederhana.
4. Alamat pengiriman disimpan sebagai **snapshot** di `orders`, bukan hanya referensi.
5. KTP diverifikasi **per-akun**, admin bisa memicu re-verifikasi kapan pun.
6. Tidak ada tabel/field deposit (FR-27).
7. **(Baru)** Auth memakai Sanctum **token-based**, bukan session/cookie SPA — cocok untuk frontend (Next.js) dan backend (Laravel API) yang berbeda origin/domain.
8. **(Baru)** Login/registrasi mendukung dua jalur: email+password (dengan OTP email) atau **Google OAuth** (email otomatis terverifikasi oleh Google). Pencocokan akun berdasarkan **email** — jika email dari Google OAuth sudah terdaftar manual sebelumnya, akun akan ditautkan (`google_id` diisi ke akun yang sudah ada), bukan membuat akun duplikat.
9. **(Baru)** Nomor HP **tidak** diminta saat registrasi — baru wajib diisi & diverifikasi (OTP SMS/WhatsApp) saat user melakukan checkout (pembelian atau penyewaan) pertama kali. Ini berlaku terpisah dari verifikasi KTP (yang tetap eksklusif untuk transaksi sewa).
