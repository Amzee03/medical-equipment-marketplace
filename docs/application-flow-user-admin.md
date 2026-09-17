# Application Flow — User & Admin
## Medical Equipment Marketplace

Dokumen ini menjabarkan seluruh alur penggunaan aplikasi, sisi **User** dan **Admin**, berdasarkan FR-01–FR-31 dan business rules yang sudah dikunci. Digunakan sebagai acuan saat membangun Backend (Tahap 7), Frontend (Tahap 8), dan Admin Panel (Tahap 9).

---

## BAGIAN A — Alur Sisi User

### A1. Registrasi & Verifikasi Akun (FR-01, FR-02, FR-28 — revisi)
1. User mendaftar melalui salah satu jalur:
   - **Email + Password**: isi nama, email, password → sistem kirim OTP ke **email** → user masukkan OTP → akun aktif (`email_verified_at` terisi).
   - **Google OAuth**: klik "Daftar/Masuk dengan Google" → email otomatis terverifikasi oleh Google (`email_verified_at` langsung terisi, `google_id` disimpan) → tidak perlu OTP tambahan.
   - Jika email dari Google OAuth **sudah terdaftar** sebelumnya (akun manual email+password), sistem **menautkan** akun tersebut (isi `google_id` ke akun yang sudah ada), bukan membuat akun baru/duplikat.
2. **Nomor HP tidak diminta di tahap registrasi.** User bisa langsung browsing dengan akun yang baru aktif.
3. User bisa login/logout kapan saja (via email+password atau Google, sesuai jalur pendaftarannya).
4. **KTP belum diminta di tahap ini** — baru diminta saat user pertama kali mencoba menyewa (lihat A5).
5. **Nomor HP baru wajib diisi & diverifikasi (OTP SMS/WhatsApp) saat user checkout pertama kali** (pembelian maupun penyewaan) — lihat A6.

### A2. Browsing & Pencarian Produk (FR-03–FR-06)
1. User membuka homepage → melihat kategori & produk unggulan.
2. User bisa: browse per kategori, search by keyword, filter (kategori, harga, status sewa/beli).
3. User membuka detail produk → melihat deskripsi, spesifikasi, kondisi, harga jual & harga sewa (jika tersedia), opsi pembelian dan/atau penyewaan.

### A3. Alur Pembelian (FR-07)
1. Di halaman detail produk, user pilih **"Beli"**, tentukan jumlah.
2. Masuk ke keranjang sebagai item bertipe `purchase`.
3. Lanjut ke Checkout (lihat A6).

### A4. Alur Penyewaan — Pemilihan Periode (FR-08, FR-09, FR-29, FR-30)
1. Di halaman detail produk, user pilih **"Sewa"**.
2. User pilih tanggal mulai & tanggal selesai (dibatasi `min_rental_days`/`max_rental_days` produk tersebut).
3. Sistem cek ketersediaan: query `equipment_units` milik produk ini yang **tidak** memiliki `rental_items` aktif/confirmed yang overlap dengan periode dipilih.
   - Jika tersedia → tampilkan harga (dihitung dari tier daily/weekly/monthly, FR-30) → lanjut ke keranjang.
   - Jika tidak tersedia → tampilkan pesan konflik, sarankan periode lain.
4. Masuk ke keranjang sebagai item bertipe `rental`.

### A5. Verifikasi KTP (Khusus Sebelum Sewa Dikonfirmasi) (FR-28)
1. Saat checkout dan ada item `rental` di keranjang, sistem cek `users.ktp_status`.
2. Jika `not_submitted`/`rejected` → user diarahkan upload KTP dulu sebelum lanjut checkout.
3. Status berubah jadi `pending_review` → **order rental tidak bisa lanjut ke "confirmed"** sampai admin approve (lihat B4).
4. Jika `approved` → user bisa langsung checkout tanpa upload ulang (verifikasi per-akun, bukan per-transaksi).

### A6. Keranjang & Checkout (FR-10, FR-11, FR-22, FR-28, FR-31)
1. Keranjang bisa berisi campuran item `purchase` dan `rental`.
2. User bisa ubah jumlah/hapus item.
3. Saat user klik **"Checkout"**:
   - Sistem cek `phone_verified_at` — jika masih kosong (checkout pertama kali), user diminta mengisi nomor HP → sistem kirim OTP (SMS/WhatsApp) → user verifikasi → `phone` & `phone_verified_at` tersimpan di akun (berlaku seterusnya, tidak diminta ulang di transaksi berikutnya).
4. Lanjutkan checkout:
   - Pilih/tambah alamat pengiriman (dari `addresses` tersimpan, atau input baru).
   - Pilih metode pengiriman **per item** sesuai konfigurasi produk (owner delivery/express/regular/pickup) — FR-22.
   - **Jika ada item rental**: tampilkan **Rental Agreement** eksplisit (FR-31) — periode, kewajiban, denda keterlambatan, kerusakan/kehilangan, penegasan tidak ada deposit, mekanisme komplain, kebijakan pembatalan/refund. User harus klik **"Setuju"** secara eksplisit (bukan checkbox generik).
   - Sistem membuat **1 atau 2 order** (dipisah otomatis by `order_type`: 1 `purchase` order + 1 `rental` order jika campuran), keduanya terhubung ke **1 `payment`** yang sama.
   - Jika ada item rental, sistem juga membuat 1 baris `rental_agreements` per rental order (snapshot isi perjanjian yang disetujui).

### A7. Pembayaran (FR-21)
1. Sistem redirect/tampilkan Midtrans Snap dengan total dari `payment.amount`.
2. User bayar via QRIS/VA/e-wallet.
3. Midtrans mengirim **webhook/callback** ke backend → backend update `payments.status` jadi `paid`, isi `paid_at`, simpan `raw_response`.
4. Backend otomatis update `orders.status` untuk semua order yang terhubung ke `payment_id` tersebut menjadi status berikutnya:
   - Purchase order → `paid` → (nanti admin proses ke `processing`)
   - Rental order → `paid` → **jika KTP belum approved, status jadi `awaiting_ktp_verification`**; jika KTP sudah approved, status jadi `confirmed` (equipment_units terkait langsung ditandai `rented` untuk periode tersebut)

### A8. Tracking & Riwayat Pesanan (FR-12, FR-13)
1. User bisa lihat status pesanan real-time (purchase: `Pending → Paid → Processing → Shipped → Completed`; rental: `Pending → Paid → (Awaiting KTP) → Confirmed → Active → Awaiting Return → Overdue (jika telat) → Returned → Completed`).
2. User bisa lihat riwayat seluruh transaksi (beli & sewa terpisah tapi ditampilkan dalam satu riwayat).

### A9. Pembatalan (FR-23)
1. User bisa ajukan cancel dari halaman detail pesanan, tergantung status saat ini:
   - `Pending Payment` → langsung dibatalkan
   - `Paid` (belum diproses) → langsung dibatalkan
   - `Processing` → masuk antrian review admin
   - `Shipped`/`Active` (sewa berjalan)/`Completed` → tidak bisa cancel biasa, arahkan ke mekanisme komplain

### A10. Pengembalian Alat Sewa (FR-25, FR-26)
1. Mendekati `rental_due_date`, sistem kirim reminder (notifikasi/email).
2. User mengembalikan alat (fisik, sesuai metode pengiriman/pickup).
3. Admin memproses pengembalian → catat kondisi alat (lihat B5).
4. Jika ada keterlambatan, `late_days` & `late_fee` dihitung otomatis oleh scheduled job harian, `rental_status` jadi `Overdue` sampai admin konfirmasi retur.

### A11. Komplain & Refund (FR-24)
User bisa ajukan komplain (barang tidak sesuai, kesalahan owner, dsb) yang memicu proses refund kategori terkait — diproses oleh admin.

### A12. Kontak WhatsApp (FR-14)
Floating button pojok kiri bawah, klik langsung ke WhatsApp nomor 085894744507, tersedia di semua halaman.

---

## BAGIAN B — Alur Sisi Admin

### B1. Login Admin
Login sama seperti user biasa, dibedakan oleh `role = admin`, mengarah ke Admin Panel bukan storefront.

### B2. Manajemen Produk & Kategori (FR-15, FR-16)
CRUD kategori dan produk, termasuk upload foto produk, atur harga jual/sewa (multi-tier), konfigurasi shipping method per produk, atur `min_rental_days`/`max_rental_days`.

### B3. Manajemen Stok & Unit (FR-17)
- Produk beli: kelola `stock_purchase` (angka).
- Produk sewa: kelola `equipment_units` — tambah/nonaktifkan unit fisik, lihat status tiap unit (available/rented/maintenance/retired), lihat riwayat kondisi.

### B4. Verifikasi KTP (FR-28)
1. Admin melihat daftar user dengan `ktp_status = pending_review`.
2. Admin buka file KTP (dari disk private, hanya admin yang bisa akses).
3. Admin **approve** (→ semua rental order user tersebut yang menunggu berubah dari `awaiting_ktp_verification` ke `confirmed`) atau **reject** (isi `ktp_rejection_reason`, user diminta upload ulang).
4. Admin bisa **reset ke `pending_review`** kapan pun untuk memicu re-verifikasi (mis. sewa alat bernilai tinggi, kecurigaan, jeda lama sejak verifikasi terakhir).

### B5. Manajemen Pesanan (FR-18)
1. Admin lihat semua order (purchase & rental), filter by status/type.
2. Update status order secara manual sesuai progres nyata (mis. `Paid → Processing → Shipped` untuk purchase).
3. **Proses pengembalian sewa**: saat alat kembali, admin input kondisi (`baik/rusak_ringan/rusak_berat/hilang`) di `rental_items.condition_at_return` → jika bukan `baik`, sistem otomatis buat entri `damage_reports` untuk diisi estimasi biaya perbaikan/penggantian (FR-26).
4. Review permintaan cancel yang butuh review (status `Processing`).

### B6. Manajemen Refund (FR-24)
Admin proses pengajuan refund dari user: pilih kategori, tentukan jumlah, approve/reject, catat alasan.

### B7. Manajemen User (FR-19)
Admin lihat daftar user, bisa lihat riwayat transaksi per user, kelola akses (nonaktifkan akun jika perlu — di luar FR eksplisit tapi bagian wajar dari user management).

### B8. Dashboard
Ringkasan: order masuk hari ini, produk stok menipis, unit sewa jatuh tempo/overdue, user menunggu verifikasi KTP.

---

## Ringkasan State Machine Status Order

### Purchase Order
```
Pending Payment → Paid → Processing → Shipped → Completed
                      ↘ Cancelled (dari Pending/Paid, atau via review dari Processing)
```

### Rental Order
```
Pending Payment → Paid → Awaiting KTP Verification* → Confirmed → Active 
    → Awaiting Return → (Overdue jika telat) → Returned → Completed
                      ↘ Cancelled (dari Pending/Paid, atau via review)

*hanya jika KTP belum approved saat checkout
```
