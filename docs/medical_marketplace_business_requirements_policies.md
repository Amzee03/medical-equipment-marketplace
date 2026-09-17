# Business Requirements & Policies — Medical Equipment Marketplace

Dokumen ini berisi rekomendasi awal untuk keputusan bisnis yang perlu ditetapkan sebelum backend dan database difinalkan.

> **Catatan:** Dokumen ini merupakan rekomendasi desain kebijakan produk, bukan pendapat hukum formal. Untuk operasional komersial, terutama karena menyangkut alat kesehatan, kebijakan final sebaiknya ditinjau oleh pihak yang memahami hukum, perizinan usaha, perlindungan konsumen, dan regulasi alat kesehatan di Indonesia.

## 1. Metode Pembayaran

### Rekomendasi

Gunakan **payment gateway** dengan beberapa metode pembayaran, terutama:

- QRIS
- Virtual Account / Bank Transfer
- Metode pembayaran lain yang relevan melalui payment gateway

Transfer manual dapat digunakan sebagai opsi sementara untuk MVP/fallback.

### Alasan

Alur payment gateway:

`Checkout → Payment → Payment Gateway → Callback/Webhook → Order otomatis menjadi Paid`

Lebih mudah diotomatisasi daripada:

`User Checkout → Transfer → Upload Bukti → Admin Cek → Admin Approve`

Status pembayaran tidak boleh ditentukan hanya berdasarkan input frontend. Backend harus melakukan validasi melalui mekanisme pembayaran yang digunakan.

---

## 2. Metode & Biaya Pengiriman

Gunakan beberapa metode pengiriman:

### A. Owner Delivery

Jika alamat masih berada dalam area yang dapat dijangkau owner:

- Pengantaran oleh owner
- Gratis untuk radius tertentu, atau
- Biaya berdasarkan jarak

### B. Instant / Express

Untuk kebutuhan segera:

- Kurir instant/express yang tersedia di area tersebut
- Dapat digunakan untuk kebutuhan mendesak

### C. Regular Shipping

Untuk daerah yang lebih jauh:

- Ekspedisi reguler

### Catatan Penting

Tidak semua alat medis harus otomatis dikirim menggunakan ekspedisi biasa. Beberapa alat mungkin:

- berat;
- sensitif;
- mudah rusak;
- membutuhkan handling khusus;
- membutuhkan instalasi;
- memiliki dimensi besar.

Karena itu, produk sebaiknya memiliki konfigurasi pengiriman seperti:

```text
shipping_available
owner_delivery_available
express_delivery_available
regular_shipping_available
pickup_available
```

---

## 3. Kebijakan Pembatalan Pesanan

Jangan menggunakan kebijakan bahwa semua pesanan tidak dapat dibatalkan.

Rekomendasi berdasarkan status:

| Status | Kebijakan |
|---|---|
| Pending Payment | User dapat membatalkan |
| Paid | Dapat dibatalkan selama belum diproses/dikirim |
| Processing | Pembatalan perlu review |
| Shipped | Tidak dianggap sebagai cancellation biasa; gunakan mekanisme retur/komplain jika memenuhi syarat |
| Completed | Tidak dapat dibatalkan |

### Alur

`Pending → dapat cancel`

`Paid → dapat cancel sebelum diproses`

`Processing → review`

`Shipped → bukan cancellation biasa`

`Completed → tidak dapat cancel`

PP 80/2019 mengatur mekanisme pembatalan dan pengembalian dana dalam PMSE. Kebijakan implementasi harus tetap disesuaikan dengan kondisi transaksi dan ketentuan perlindungan konsumen yang berlaku.

---

## 4. Kebijakan Refund

Refund sebaiknya tidak menggunakan satu aturan untuk semua kondisi.

### A. Kesalahan Owner

Contoh:

- Barang tidak tersedia padahal sistem menyatakan tersedia
- Barang yang diterima salah
- Barang rusak sebelum dikirim
- Pesanan dibatalkan oleh owner
- Pembayaran berhasil tetapi transaksi tidak dapat dipenuhi

Rekomendasi:

**Refund penuh sesuai kondisi transaksi dan ketentuan yang berlaku.**

### B. Barang Tidak Sesuai

Contoh:

User memesan produk A tetapi menerima produk B.

Rekomendasi:

- User dapat mengajukan komplain
- Jika terbukti kesalahan penjual, dapat diberikan refund atau replacement

### C. Pembatalan oleh User

Jika pembatalan dilakukan sebelum pesanan diproses:

- Refund dapat diberikan sesuai kebijakan pembatalan

Jika pesanan sudah masuk proses pengiriman:

- Tidak otomatis dianggap sebagai pembatalan biasa
- Dapat menggunakan mekanisme retur/komplain
- Biaya yang memang sudah dikeluarkan dan tidak dapat dikembalikan harus memiliki dasar dan diinformasikan secara jelas

Jangan membuat kebijakan absolut seperti:

> "Refund tidak pernah tersedia."

---

## 5. Keterlambatan Pengembalian Alat Sewa

Denda saja sebaiknya tidak menjadi satu-satunya mekanisme.

Gunakan sistem eskalasi.

### Tahapan

1. Mendekati jatuh tempo → sistem memberikan reminder
2. Melewati jatuh tempo → status menjadi `Overdue`
3. Denda mulai dihitung
4. Admin melakukan follow-up jika keterlambatan berlanjut
5. Jika keterlambatan serius → penyelesaian berdasarkan perjanjian sewa

### Perhitungan Dasar

Contoh konsep:

`Biaya keterlambatan = tarif sewa harian × jumlah hari keterlambatan`

Besaran denda final perlu ditentukan secara wajar dan transparan dalam perjanjian sewa.

### Data yang Disarankan

```text
rental_due_date
actual_return_date
late_days
late_fee
rental_status
```

---

## 6. Kerusakan / Kehilangan Alat Sewa

Jangan menggunakan aturan sederhana:

> "Rusak = harga alat + penalti."

Lebih baik membedakan kondisi kerusakan.

### A. Kerusakan Ringan

Jika masih dapat diperbaiki:

**User membayar biaya perbaikan yang wajar sesuai hasil pemeriksaan.**

### B. Kerusakan Berat

Jika alat tidak dapat digunakan sebagaimana mestinya dan membutuhkan biaya besar:

**User dapat dikenakan biaya perbaikan atau penggantian sesuai ketentuan perjanjian dan kerugian yang dapat dipertanggungjawabkan.**

### C. Kehilangan

Jika alat hilang:

**User dapat dikenakan nilai penggantian sesuai ketentuan perjanjian sewa, dengan memperhatikan kondisi/umur alat dan kebijakan yang telah diberitahukan sebelumnya.**

Hindari penalti arbitrer atau tidak proporsional.

---

## 7. Deposit Sewa

### Rekomendasi: Ya, tetapi tidak wajib untuk semua produk.

Deposit dapat ditentukan berdasarkan nilai dan risiko alat.

### Low Risk

Alat sederhana dan bernilai relatif rendah:

- Deposit rendah, atau
- Tanpa deposit

### Medium Risk

Alat dengan nilai lebih tinggi:

- Deposit diperlukan

### High Value

Alat mahal/sensitif:

- Deposit lebih besar
- Verifikasi user lebih ketat
- Perjanjian sewa lebih jelas

### Data Produk yang Disarankan

```text
rental_available
rental_price_daily
rental_price_weekly
rental_price_monthly
deposit_required
deposit_amount
```

### Status Deposit

Deposit bukan pendapatan otomatis owner.

Gunakan status:

```text
Pending
Held
Partially Deducted
Refunded
```

Jika alat dikembalikan dalam kondisi baik:

**Deposit dikembalikan.**

Jika terdapat kerusakan yang menjadi tanggung jawab penyewa:

**Sebagian atau seluruh deposit dapat digunakan sesuai ketentuan perjanjian.**

---

## 8. Verifikasi User

Tidak disarankan hanya menggunakan:

`Email + Password`

Untuk marketplace dengan rental, gunakan:

`Email + Password + Email/Phone Verification`

Untuk rental tertentu yang memiliki risiko lebih tinggi, dapat digunakan verifikasi tambahan.

### Rekomendasi Registrasi

```text
Nama
Email
Password
Nomor HP
```

Kemudian:

**Email verification atau OTP.**

### Berdasarkan Risiko

```text
Purchase biasa
→ Account verified

Rental murah
→ Account + phone verified

Rental alat mahal
→ Additional verification
```

Tidak perlu langsung meminta KTP kepada semua user. Data pribadi sebaiknya hanya dikumpulkan jika memang diperlukan dan harus diproses dengan perlindungan yang sesuai.

Selain itu, hindari mengumpulkan data kesehatan user jika memang tidak diperlukan untuk transaksi.

---

## 9. Minimum & Maximum Masa Sewa

### Minimum

Rekomendasi awal:

**1 hari**

### Maximum

Tidak disarankan satu angka untuk semua produk.

Gunakan konfigurasi per produk:

```text
min_rental_days
max_rental_days
```

Contoh:

```text
Tensimeter
→ 1–30 hari

Patient Monitor
→ 1–7 hari
```

Jika diperlukan, produk tertentu dapat memiliki masa sewa khusus berdasarkan persetujuan admin.

---

## 10. Model Harga Sewa

Rekomendasi:

- Harian
- Mingguan
- Bulanan

Contoh:

```text
Harian
Rp50.000

Mingguan
Rp300.000

Bulanan
Rp900.000
```

Harga paket tidak harus sama dengan hasil perkalian harga harian.

Tujuannya adalah memberikan harga yang lebih menarik untuk penyewaan jangka panjang.

---

# Rekomendasi Final Baseline

```text
PAYMENT
────────────────────
QRIS
Virtual Account / Bank Transfer
Payment Gateway
Manual transfer hanya sebagai fallback/MVP


SHIPPING
────────────────────
Owner Delivery
Instant / Express Courier
Regular Expedition
Pickup


CANCELLATION
────────────────────
Pending Payment  → Cancel
Paid             → Cancel sebelum processing
Processing       → Review
Shipped          → Tidak dianggap cancellation biasa
Completed        → Tidak dapat dibatalkan


REFUND
────────────────────
Kesalahan owner          → Refund penuh sesuai kondisi
Barang tidak sesuai      → Refund / replacement
Cancel sebelum diproses  → Refund sesuai kebijakan
Masalah setelah diterima → Complaint / return policy


RENTAL
────────────────────
Minimum             → 1 hari
Maximum             → Per product
Pricing              → Daily / Weekly / Monthly
Availability         → Berdasarkan tanggal


LATE RETURN
────────────────────
Reminder
→ Late status
→ Daily late fee
→ Admin escalation
→ Penyelesaian sesuai perjanjian


DAMAGE
────────────────────
Minor damage  → Repair cost
Major damage  → Repair/replacement sesuai ketentuan
Lost          → Replacement value sesuai perjanjian


DEPOSIT
────────────────────
Tidak wajib untuk semua produk
Berdasarkan nilai/risiko alat


USER VERIFICATION
────────────────────
Email + Password
+ Email/Phone verification
+ Additional verification untuk rental berisiko tinggi
```

---

# 11. Requirement Tambahan: Rental Agreement

Salah satu requirement penting yang perlu ditambahkan adalah **Perjanjian Sewa / Rental Agreement**.

Sebelum rental dikonfirmasi, user sebaiknya menyetujui ketentuan yang mencakup:

- periode sewa;
- harga;
- deposit;
- kewajiban mengembalikan alat;
- keterlambatan;
- kerusakan;
- kehilangan;
- penggunaan alat;
- tanggung jawab user;
- mekanisme komplain;
- pembatalan;
- refund.

### Flow Rental

`Pilih Alat → Pilih Periode → Cek Availability → Lihat Biaya → Setujui Ketentuan Sewa → Checkout → Pembayaran → Rental Aktif`

Rental Agreement lebih baik daripada hanya menggunakan checkbox umum "Saya setuju dengan syarat dan ketentuan", karena transaksi rental memiliki kewajiban setelah alat berada di tangan user.

---

# 12. Dampak terhadap Backend & Database

Keputusan bisnis di atas harus diterjemahkan menjadi business rules sebelum database final dibuat.

Urutan yang disarankan:

`Business Rules → Order/Rental Status → ERD → Database Fields → Backend Logic → UI`

Jangan memfinalkan database hanya berdasarkan asumsi awal.

Beberapa field yang kemungkinan diperlukan:

```text
# Product / Rental
rental_available
rental_price_daily
rental_price_weekly
rental_price_monthly
deposit_required
deposit_amount
min_rental_days
max_rental_days

# Rental
rental_due_date
actual_return_date
late_days
late_fee
rental_status

# Deposit
deposit_status
deposit_amount
deposit_deducted_amount
deposit_refund_amount

# Shipping
shipping_available
owner_delivery_available
express_delivery_available
regular_shipping_available
pickup_available
```

Struktur final tetap harus ditentukan setelah seluruh business rules disepakati.

---

# 13. Regulasi dan Referensi Awal

Regulasi yang perlu diperhatikan antara lain:

- **PP No. 80 Tahun 2019 tentang Perdagangan Melalui Sistem Elektronik (PMSE)** — relevan untuk mekanisme transaksi dan pembatalan/pengembalian dana dalam PMSE.
- **UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)** — relevan untuk pengumpulan dan pengelolaan data user.
- **Permenkes No. 11 Tahun 2025** — relevan untuk standar kegiatan usaha dan produk/jasa subsektor kesehatan, termasuk konteks alat kesehatan.

Status dan penerapan regulasi harus diverifikasi kembali sebelum website digunakan untuk kegiatan komersial.

---

# 14. Status Keputusan

Dokumen ini adalah **baseline recommendation**, bukan seluruhnya keputusan bisnis final.

Sebelum Tahap Backend/Database difinalkan, owner/project stakeholder harus menyetujui minimal:

1. Payment method
2. Shipping method & pricing
3. Cancellation policy
4. Refund policy
5. Late return policy
6. Damage/loss policy
7. Deposit policy
8. User verification
9. Minimum/maximum rental duration
10. Rental pricing model
11. Rental agreement
