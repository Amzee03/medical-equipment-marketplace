Ambil prinsip umum (keamanan, git, env var) masih relevan pada file ini, tapi jangan dijadikan acuan pilihan stack (itu sudah final di ai-agent-manifest.md)

# Technical Knowledge

## 1. Prinsip Arsitektur
Gunakan arsitektur yang:
- ringan;
- sederhana;
- maintainable;
- aman;
- scalable secukupnya;
- mudah dipelajari dan dikembangkan.

Teknologi final harus dipilih berdasarkan requirement, kemampuan user, dan kebutuhan proyek.

## 2. Frontend
Konsep yang perlu dipahami:
- HTML;
- CSS;
- JavaScript;
- responsive design;
- component;
- form;
- validation;
- API consumption;
- state management bila diperlukan.

Framework/library boleh digunakan jika memberikan manfaat nyata. Jangan menambahkan dependency hanya karena populer.

## 3. Backend
Backend bertanggung jawab terhadap:
- authentication;
- authorization;
- business logic;
- CRUD;
- validation;
- database access;
- API;
- transaksi;
- upload file;
- error handling.

## 4. API
Gunakan API secara konsisten bila arsitektur membutuhkan frontend-backend terpisah.

Konsep:
- GET;
- POST;
- PUT/PATCH;
- DELETE;
- status code;
- request;
- response;
- authentication;
- validation;
- error response.

## 5. Database
Pilihan relasional seperti MySQL/PostgreSQL cocok untuk:
- user;
- kategori;
- produk;
- stok;
- pesanan;
- detail pesanan;
- pembayaran;
- penyewaan;
- periode sewa.

## 6. Relasi Database
Konsep penting:
- primary key;
- foreign key;
- one-to-one;
- one-to-many;
- many-to-many;
- indexing;
- normalization;
- transaction.

## 7. Keamanan
Wajib mempertimbangkan:
- password hashing;
- authentication;
- authorization;
- SQL injection;
- XSS;
- CSRF;
- input validation;
- output escaping;
- secure file upload;
- environment variables;
- access control;
- rate limiting bila diperlukan.

## 8. File Upload
Jika produk menggunakan foto:
- validasi tipe file;
- validasi ukuran;
- nama file aman;
- penyimpanan terkontrol;
- jangan mempercayai ekstensi file saja.

## 9. Git
Gunakan version control:
```text
git init
git add
git commit
git branch
git merge
git pull
git push
```

Gunakan commit yang jelas dan kecil agar perubahan mudah dilacak.

## 10. Environment
Data sensitif tidak boleh hard-code:
- password database;
- API key;
- secret;
- credential.

Gunakan environment variables.

## 11. Performance
Prioritaskan:
- ukuran asset kecil;
- image optimization;
- lazy loading;
- dependency minimal;
- query database efisien;
- indexing;
- caching jika dibutuhkan;
- menghindari JavaScript berlebihan.

## 12. Frontend-Backend Interaction
Contoh:
```text
Frontend
   ↓ HTTP Request
Backend/API
   ↓
Business Logic
   ↓
Database
   ↓
Backend/API
   ↓ JSON Response
Frontend
   ↓
UI
```

## 13. Tools
Tools yang mungkin diperlukan:
- VS Code;
- Git;
- GitHub;
- terminal;
- browser developer tools;
- database management tool;
- API testing tool;
- package manager;
- local server.

Pemilihan final dilakukan pada tahap perencanaan teknis.
