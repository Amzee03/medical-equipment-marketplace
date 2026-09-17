# Marketplace Business Rules

## 1. Alur Pembelian

User:
1. membuka katalog;
2. memilih kategori atau mencari produk;
3. membuka detail produk;
4. memilih jumlah;
5. memasukkan produk ke keranjang;
6. checkout;
7. memilih metode pembayaran;
8. pesanan dibuat;
9. pembayaran diproses;
10. pesanan diproses admin;
11. barang dikirim/diambil;
12. pesanan selesai.

## 2. Alur Penyewaan

User:
1. memilih produk yang dapat disewa;
2. membuka detail produk;
3. memilih tanggal mulai;
4. memilih tanggal selesai;
5. sistem memeriksa ketersediaan;
6. user memasukkan ke keranjang;
7. checkout;
8. pembayaran diproses;
9. pesanan sewa diproses;
10. alat dikirim/diambil;
11. periode sewa berjalan;
12. alat dikembalikan;
13. kondisi alat dapat diperiksa;
14. transaksi selesai.

## 3. Ketersediaan Penyewaan
Sistem harus mencegah konflik periode sewa.

Contoh:
Jika unit A disewa tanggal 10–15 Agustus, sistem tidak boleh memberikan unit yang sama pada periode yang bentrok.

Jika stok terdiri dari beberapa unit, ketersediaan harus dihitung berdasarkan jumlah unit yang sedang digunakan pada periode tersebut.

## 4. Status Pesanan
Contoh status:
- Pending
- Menunggu pembayaran
- Dibayar
- Diproses
- Dikirim
- Siap diambil
- Sedang disewa
- Menunggu pengembalian
- Dikembalikan
- Selesai
- Dibatalkan

Status final harus disesuaikan dengan implementasi.

## 5. Pembelian vs Penyewaan
### Pembelian
Barang menjadi milik user setelah transaksi selesai sesuai kebijakan bisnis.

### Penyewaan
Kepemilikan tetap pada pihak marketplace/penyedia dan user memiliki hak penggunaan selama periode sewa.

## 6. Aturan yang Perlu Ditentukan
- metode pembayaran;
- metode pengiriman;
- biaya pengiriman;
- pembatalan;
- refund;
- keterlambatan;
- kerusakan;
- kehilangan;
- deposit;
- verifikasi user;
- batas minimum/maksimum masa sewa.

Jika belum ditentukan, AI harus menandainya sebagai keputusan bisnis yang perlu dikonfirmasi.

## 7. Keranjang
Keranjang dapat berisi:
- item pembelian;
- item penyewaan.

Sistem harus membedakan jenis transaksi dan menghitung harga sesuai aturan masing-masing.

## 8. Harga Sewa
Model harga dapat menggunakan:
- per hari;
- per minggu;
- per bulan;
- atau periode lain.

Jangan menentukan model final tanpa persetujuan jika belum ditetapkan.

## 9. Stok
Pembelian mengurangi stok tersedia.
Penyewaan mengurangi jumlah unit yang tersedia selama periode sewa, lalu mengembalikannya ke stok tersedia setelah pengembalian diproses.
