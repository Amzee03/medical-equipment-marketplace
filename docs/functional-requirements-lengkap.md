# Functional Requirements — Medical Equipment Marketplace
## FR-01 s/d FR-31 (Final)

**Status:** Seluruh FR di bawah ini sudah dikunci pada Tahap 1 — Requirement.
**Referensi pendukung:** `medical_marketplace_business_requirements_policies.md` (acuan utama business logic untuk Tahap 5 & 6)

---

## A. User — Akun & Autentikasi

### FR-01 User Registration *(Direvisi)*
User dapat membuat akun melalui **email + password** (dengan verifikasi OTP email), atau melalui **Google OAuth** (email otomatis terverifikasi oleh Google). Jika email dari Google OAuth sudah terdaftar secara manual sebelumnya, akun ditautkan (bukan duplikat).

### FR-02 User Login
User dapat login dan logout, melalui email+password atau Google OAuth sesuai jalur pendaftarannya.

### FR-28 Verifikasi User & KTP untuk Sewa *(Direvisi ke-3)*
Registrasi awal hanya memerlukan **email** (via password+OTP email, atau Google OAuth). **Nomor HP tidak diminta saat registrasi** — baru wajib diisi dan diverifikasi (OTP SMS/WhatsApp) saat user melakukan **checkout pertama kali** (pembelian maupun penyewaan), demi mengurangi friksi pendaftaran dan biaya OTP untuk user yang belum tentu bertransaksi. Setelah terverifikasi sekali, nomor HP berlaku untuk seluruh transaksi berikutnya (tidak diminta ulang).

**Khusus untuk transaksi sewa**, user tetap wajib mengunggah **KTP** sebagai jaminan identitas sebelum rental disetujui. Admin melakukan **screening manual** terhadap KTP dan data user sebelum status rental dikonfirmasi (bukan verifikasi otomatis oleh sistem). Verifikasi KTP bersifat **per-akun** (sekali disetujui, berlaku seterusnya), dengan admin dapat memicu re-verifikasi kapan pun diperlukan.

> Catatan kepatuhan data: dokumen KTP berkaitan dengan UU PDP No. 27/2022 — penyimpanan file harus di disk privat, akses terbatas admin only.

---

## B. Produk & Katalog

### FR-03 Product Browsing
User dapat melihat daftar produk.

### FR-04 Product Search
User dapat mencari produk berdasarkan kata kunci.

### FR-05 Product Filtering
User dapat memfilter produk berdasarkan kategori dan parameter relevan lainnya.

### FR-06 Product Detail
User dapat melihat detail produk (deskripsi, fungsi, spesifikasi, kondisi, harga jual, harga sewa, opsi pembelian/penyewaan).

---

## C. Transaksi — Pembelian

### FR-07 Purchase
User dapat membeli produk yang tersedia.

---

## D. Transaksi — Penyewaan

### FR-08 Rental
User dapat menyewa produk yang tersedia untuk disewa.

### FR-09 Rental Availability
Sistem memeriksa ketersediaan unit berdasarkan periode sewa yang dipilih, dan **wajib mencegah konflik periode sewa** pada unit yang sama.

### FR-27 Full Payment untuk Sewa (Tanpa Deposit)
User wajib membayar **penuh** biaya sewa (sesuai tarif harian/mingguan/bulanan yang dipilih) saat checkout, tanpa mekanisme deposit terpisah. Tidak ada status deposit (`Pending/Held/Partially Deducted/Refunded`) di sistem. Biaya kerusakan/keterlambatan (jika terjadi) ditagih terpisah pasca-transaksi berdasarkan Rental Agreement (FR-31).

### FR-29 Rental Duration Configuration
Produk sewa memiliki `min_rental_days` dan `max_rental_days` yang dapat berbeda per produk (contoh: Tensimeter 1–30 hari, Patient Monitor 1–7 hari).

### FR-30 Multi-tier Rental Pricing
Produk sewa mendukung multi-tier harga (harian/mingguan/bulanan). Harga paket tidak harus proporsional terhadap harga harian (boleh didiskon untuk sewa jangka panjang).

### FR-31 Rental Agreement
Sebelum checkout sewa dikonfirmasi, user wajib menyetujui **Rental Agreement** eksplisit (bukan checkbox generik) yang mencakup: periode sewa, kewajiban pengembalian, konsekuensi keterlambatan (denda), konsekuensi kerusakan/kehilangan, **penegasan tidak ada deposit dan biaya tambahan ditagih terpisah jika terjadi kerusakan/keterlambatan**, mekanisme komplain, serta kebijakan pembatalan/refund.

---

## E. Keranjang & Checkout

### FR-10 Cart
User dapat menambah, mengubah, dan menghapus item di keranjang. Keranjang dapat berisi **campuran item pembelian dan penyewaan** sekaligus; sistem membedakan jenis transaksi dan menghitung harga sesuai aturan masing-masing.

### FR-11 Checkout
User dapat melakukan checkout untuk item beli, sewa, atau campuran keduanya.

### FR-21 Payment Gateway Integration
Sistem terintegrasi dengan payment gateway (Midtrans — QRIS, Virtual Account, e-wallet). Status pembayaran diverifikasi melalui **callback/webhook** dari payment gateway, bukan berdasarkan input dari frontend.

### FR-22 Shipping Configuration per Produk
Produk memiliki konfigurasi metode pengiriman (owner delivery, express/instant, regular shipping, pickup) yang dapat dikombinasikan per produk, karena tidak semua alat medis cocok dikirim dengan metode yang sama (berat, sensitif, butuh instalasi khusus, dsb).

---

## F. Pesanan — Tracking & Riwayat

### FR-12 Order Tracking
User dapat melihat status pesanan secara real-time.

### FR-13 Order History
User dapat melihat riwayat transaksi (pembelian maupun penyewaan).

### FR-23 Order Cancellation
User dapat membatalkan pesanan sesuai status pesanan saat ini:
- **Pending Payment** → dapat dibatalkan
- **Paid** → dapat dibatalkan selama belum diproses/dikirim
- **Processing** → pembatalan memerlukan review admin
- **Shipped** → tidak dianggap cancellation biasa, gunakan mekanisme retur/komplain
- **Completed** → tidak dapat dibatalkan

### FR-24 Refund Processing
Sistem mendukung proses refund dengan kategori berbeda:
- Kesalahan owner (barang tidak tersedia, rusak sebelum kirim, dibatalkan owner) → refund penuh
- Barang tidak sesuai pesanan → komplain, refund/replacement jika terbukti kesalahan penjual
- Pembatalan oleh user sebelum diproses → refund sesuai kebijakan pembatalan

---

## G. Penyewaan — Pengembalian & Pasca-Transaksi

### FR-25 Late Return Fee Calculation
Sistem menghitung denda keterlambatan pengembalian sewa secara otomatis (tarif harian × jumlah hari keterlambatan), dan menandai status pesanan menjadi `Overdue` ketika melewati jatuh tempo. Sistem memberikan reminder mendekati jatuh tempo.

### FR-26 Damage/Loss Handling
Admin dapat mencatat kondisi alat saat pengembalian (baik / rusak ringan / rusak berat / hilang). Sistem mendukung proses pencatatan biaya perbaikan atau penggantian sesuai kondisi yang dicatat, mengacu pada Rental Agreement (FR-31).

---

## H. Kontak

### FR-14 WhatsApp Contact
User dapat mengakses WhatsApp melalui floating button (posisi pojok kiri bawah), mengarah ke nomor **085894744507**.

---

## I. Admin — Manajemen

### FR-15 Admin Product Management
Admin dapat melakukan CRUD (Create, Read, Update, Delete) produk.

### FR-16 Admin Category Management
Admin dapat melakukan CRUD kategori.

### FR-17 Admin Stock Management
Admin dapat mengelola stok produk (untuk beli maupun ketersediaan unit sewa).

### FR-18 Admin Order Management
Admin dapat melihat dan memperbarui status pesanan, termasuk review pembatalan (FR-23) dan proses refund (FR-24).

### FR-19 Admin User Management
Admin dapat mengelola data user, termasuk **screening manual KTP** untuk approval transaksi sewa (FR-28).

---

## J. Validasi Sistem

### FR-20 Validation
Seluruh input user harus divalidasi di sisi frontend **dan** backend.

---

## Ringkasan Status

| Rentang FR | Kategori | Status |
|---|---|---|
| FR-01 – FR-20 | Requirement dasar (dari knowledge base awal) | ✅ Dikonfirmasi, tidak berubah |
| FR-21 – FR-26 | Payment, shipping, cancellation, refund, late fee, damage/loss | ✅ Dikunci |
| FR-27 | Full payment sewa (deposit dihapus) | ✅ Direvisi & dikunci |
| FR-28 | Verifikasi user (Google OAuth, HP ditunda ke checkout) + KTP wajib untuk sewa | ✅ Direvisi 3x & dikunci (final) |
| FR-29 – FR-31 | Durasi sewa, multi-tier pricing, rental agreement | ✅ Dikunci |

**Total: 31 Functional Requirements.** Seluruh FR-01–FR-31 masuk dalam scope MVP (tidak ada yang ditunda), kecuali dinyatakan lain oleh user di kemudian hari.
