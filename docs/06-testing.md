# Testing Knowledge

## 1. Tujuan
Testing memastikan sistem berjalan sesuai requirement dan perubahan baru tidak merusak fitur lama.

## 2. Jenis Testing

### Functional Testing
Memastikan fungsi sesuai requirement.

### Integration Testing
Memastikan beberapa bagian sistem bekerja bersama.

### Database Testing
Memastikan data tersimpan, diperbarui, dan dihapus dengan benar.

### API Testing
Memastikan endpoint menghasilkan response yang benar.

### UI Testing
Memastikan tampilan dan interaksi berjalan.

### Responsive Testing
Uji desktop, tablet, dan mobile.

### Security Testing
Uji validasi dan akses tanpa otorisasi.

### Performance Testing
Uji waktu respons dan penggunaan resource.

## 3. Contoh Test Case Login

| ID | Skenario | Expected |
|---|---|---|
| LOGIN-01 | Credential benar | Login berhasil |
| LOGIN-02 | Password salah | Login ditolak |
| LOGIN-03 | User tidak ditemukan | Login ditolak |
| LOGIN-04 | Form kosong | Validasi tampil |

## 4. Contoh Test Case Produk

| ID | Skenario | Expected |
|---|---|---|
| PROD-01 | Membuka detail produk | Detail tampil |
| PROD-02 | Produk tidak tersedia | Status unavailable |
| PROD-03 | Filter kategori | Produk sesuai kategori |
| PROD-04 | Search produk | Hasil relevan |

## 5. Contoh Test Case Penyewaan

| ID | Skenario | Expected |
|---|---|---|
| RENT-01 | Alat tersedia | Bisa disewa |
| RENT-02 | Periode bentrok | Sistem menolak |
| RENT-03 | Tanggal tidak valid | Validasi tampil |
| RENT-04 | Checkout sewa | Pesanan dibuat |

## 6. Bug Report
Format:
```text
Bug ID:
Judul:
Tahap:
Environment:
Langkah reproduksi:
Expected:
Actual:
Error message:
Screenshot/log:
Status:
```

## 7. Definition of Done
Sebuah tahap dianggap selesai jika:
- implementasi selesai;
- tidak ada error yang diketahui pada scope tersebut;
- test case utama berhasil;
- hasil sesuai requirement;
- user mengonfirmasi tahap selesai.
