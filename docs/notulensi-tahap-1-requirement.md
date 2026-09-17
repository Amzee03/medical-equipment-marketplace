# Notulensi Tahap 1 — Requirement
## Medical Equipment Marketplace

**Tanggal diskusi:** 12 Agustus 2026
**Tahap:** Tahap 1 — Requirement (mengacu `07-development-workflow.md`)
**Status:** Selesai, siap lanjut ke Tahap 2 — Technology Selection

---

## 1. Latar Belakang

Sesi ini membahas penyempurnaan requirement fungsional berdasarkan file baru yang diunggah, `medical_marketplace_business_requirements_policies.md`, yang berisi rekomendasi kebijakan bisnis (pembayaran, pengiriman, pembatalan, refund, keterlambatan sewa, kerusakan/kehilangan, deposit, verifikasi user, durasi sewa, model harga sewa, rental agreement). File ini menjadi acuan tambahan bersama 12 file knowledge base awal (`01`–`12`).

---

## 2. Functional Requirement Baru (FR-21 s/d FR-31)

Berikut requirement tambahan yang dikunci pada tahap ini, di luar FR-01 s/d FR-20 yang sudah ada sebelumnya:

| ID | Requirement | Catatan |
|---|---|---|
| FR-21 | Sistem terintegrasi dengan payment gateway (QRIS, VA); status pembayaran diverifikasi via callback/webhook, bukan input frontend | Dikunci |
| FR-22 | Produk memiliki konfigurasi metode pengiriman (owner delivery, express, regular, pickup) yang dapat dikombinasikan per produk | Dikunci |
| FR-23 | User dapat membatalkan pesanan sesuai status pesanan (Pending/Paid boleh, Processing perlu review, Shipped/Completed tidak) | Dikunci |
| FR-24 | Sistem mendukung proses refund dengan kategori berbeda (kesalahan owner, barang tidak sesuai, pembatalan user) | Dikunci |
| FR-25 | Sistem menghitung denda keterlambatan pengembalian sewa otomatis (tarif harian × hari telat) dan menandai status `Overdue` | Dikunci |
| FR-26 | Admin dapat mencatat kondisi alat saat pengembalian (baik/rusak ringan/rusak berat/hilang) dan sistem mendukung proses biaya perbaikan/penggantian | Dikunci |
| FR-27 | **(Direvisi)** Tidak ada mekanisme deposit sewa. User wajib membayar penuh (full payment) biaya sewa saat checkout. Biaya kerusakan/keterlambatan ditagih terpisah pasca-transaksi berdasarkan Rental Agreement (FR-31) | Direvisi & dikunci — lihat catatan risiko di §3 |
| FR-28 | **(Direvisi 2x)** Registrasi standar: email/nomor HP + OTP. **Khusus transaksi sewa**: user wajib mengunggah KTP sebagai jaminan identitas; admin melakukan screening manual sebelum rental disetujui | Direvisi & dikunci — lihat catatan kepatuhan data di §3 |
| FR-29 | Produk sewa memiliki `min_rental_days` dan `max_rental_days` yang dapat berbeda per produk | Dikunci |
| FR-30 | Produk sewa mendukung multi-tier harga (harian/mingguan/bulanan); harga paket tidak harus proporsional terhadap harga harian | Dikunci |
| FR-31 | Sebelum checkout sewa dikonfirmasi, user wajib menyetujui Rental Agreement eksplisit (periode, kewajiban, keterlambatan, kerusakan/kehilangan, penegasan tidak ada deposit, mekanisme komplain, kebijakan pembatalan/refund) — bukan checkbox generik | Dikunci |

---

## 3. Riwayat Perubahan (Perdebatan Keputusan)

### FR-27 — Deposit Sewa
- **Awal:** Direkomendasikan ada deposit bertingkat berdasarkan risiko produk (low/medium/high value), dengan status deposit (`Pending/Held/Partially Deducted/Refunded`).
- **Keputusan user:** Deposit dihapus. User membayar penuh biaya sewa di muka.
- **Catatan risiko yang ditandai:** Tanpa deposit, sistem tidak memiliki dana tertahan untuk otomatis menutup biaya kerusakan/keterlambatan. Penagihan menjadi bersifat *post-facto* dan bergantung pada kekuatan Rental Agreement (FR-31) serta screening manual admin, bukan mekanisme finansial otomatis. User menyetujui risiko ini secara sadar.

### FR-28 — Verifikasi User
- **Versi 1 (awal):** Direkomendasikan tanpa perlu KTP; cukup email + password + verifikasi email/HP; verifikasi tambahan hanya untuk sewa berisiko tinggi.
- **Versi 2:** User memutuskan tidak perlu verifikasi lanjutan sama sekali, cukup screening manual admin.
- **Versi 3 (final/ralat):** User meralat — **KTP tetap diperlukan** sebagai jaminan saat transaksi sewa, diverifikasi manual oleh admin.
- **Catatan kepatuhan data yang ditandai:** Karena melibatkan dokumen kependudukan, hal ini bersinggungan dengan **UU PDP No. 27/2022**. Perlu dipastikan nanti di tahap backend: penyimpanan file KTP aman (bukan folder publik), akses terbatas admin only, dan kebutuhan upload file khusus dokumen identitas (terpisah dari upload foto produk).

---

## 4. Keputusan Technology Stack

### Proses Diskusi
1. Rekomendasi awal: Next.js (App Router) + PostgreSQL + Prisma — dengan pertimbangan pengalaman user yang baru saja menyelesaikan project besar berbasis Next.js.
2. User mempertanyakan opsi kombinasi **Laravel 12 + Next.js** (Laravel sebagai backend API, Next.js sebagai frontend/headless architecture).
3. Dianalisis trade-off: kombinasi dua stack terpisah cocok untuk tim, namun menambah overhead signifikan (dua codebase, dua deployment, auth lintas domain via Sanctum, CORS) tanpa manfaat paralelisasi kerja jika dikerjakan solo.
4. User mengonfirmasi akan mengerjakan project **sendirian**, dan **juga punya pengalaman dengan Laravel 12** (selain Next.js).
5. Dengan informasi ini, direkomendasikan untuk **tidak** menggabungkan dua stack, dan memilih satu stack full-stack saja karena solo developer paling diuntungkan oleh satu codebase/satu bahasa/satu deployment.

### Keputusan Final
✅ **Laravel 12 (full-stack, monolith)** — dipilih oleh user.

**Alasan kecocokan dengan kebutuhan proyek:**
- File upload aman bawaan (relevan untuk upload KTP — FR-28)
- Job scheduler bawaan (`php artisan schedule`) — relevan untuk reminder jatuh tempo sewa & perhitungan denda otomatis (FR-25)
- Eloquent ORM matang untuk relasi data kompleks (rental, order, agreement, dsb)
- Auth & authorization built-in kuat
- Satu project, satu bahasa (PHP), satu deployment — cocok untuk solo developer

**Catatan untuk Tahap 2:** Detail lebih lanjut (Blade murni vs Laravel + Inertia.js untuk UI lebih reaktif, pilihan database MySQL vs PostgreSQL, tools pendukung lain) akan dibahas resmi di **Tahap 2 — Technology Selection**.

---

## 5. Hal Lain yang Disepakati

- **Lampiran desain frontend** akan diberikan satu per satu oleh user. Claude wajib memberi tahu secara eksplisit saat project sudah memasuki **Tahap 7 — Frontend**, sebagai penanda user bisa mulai melampirkan gambar referensi desain per halaman.
- `medical_marketplace_business_requirements_policies.md` dijadikan **referensi utama** untuk business logic backend (state machine status order/rental, perhitungan denda, validasi cancellation/refund, dsb) di Tahap 5 (Database Design) dan Tahap 6 (Backend).
- Scope MVP: seluruh FR-01 s/d FR-31 dianggap masuk dalam versi pertama pengerjaan (tidak ada fitur yang ditunda ke fase berikutnya), kecuali dinyatakan lain oleh user di kemudian hari.

---

## 6. Status Akhir Tahap 1

| Item | Status |
|---|---|
| Requirement fungsional dasar (FR-01–FR-20) | ✅ Dikonfirmasi (tidak berubah) |
| Requirement fungsional tambahan (FR-21–FR-31) | ✅ Dikunci (termasuk 2 revisi: FR-27, FR-28) |
| 10 poin keputusan bisnis (payment, shipping, cancellation, refund, late return, damage/loss, deposit, verifikasi user, durasi sewa, pricing sewa) | ✅ Terjawab via `medical_marketplace_business_requirements_policies.md` |
| Technology stack | ✅ Diputuskan: **Laravel 12 (full-stack)** |
| Referensi desain frontend | 🔜 Menyusul per halaman, ditandai saat Tahap 7 |

**Tahap 1 dinyatakan selesai.** Project siap lanjut ke **Tahap 2 — Technology Selection** untuk membahas detail stack pendukung (frontend approach dalam Laravel, database, tools tambahan).
