bagian "Aturan Setiap Tahap" (gaya kerja bertahap) masih berlaku pada file ini, tapi daftar & penomoran tahapnya sudah digantikan oleh file pipeline-v2-revisi.md

# Development Workflow

## Prinsip Utama
Pengerjaan dilakukan satu tahap pada satu waktu.

AI agent tidak boleh langsung menghasilkan seluruh project sekaligus.

## Tahap 1 — Requirement
- memahami tujuan;
- mengidentifikasi user;
- mengidentifikasi fitur;
- mengidentifikasi batasan;
- mengidentifikasi keputusan yang belum ditentukan.

## Tahap 2 — Technology Selection
- membandingkan pilihan;
- memilih frontend;
- memilih backend;
- memilih database;
- menentukan tools.

## Tahap 3 — Environment Installation
- install runtime;
- install package manager;
- install database;
- setup project;
- verifikasi environment.

## Tahap 4 — Project Architecture
- struktur folder;
- konfigurasi;
- frontend-backend architecture;
- environment.

## Tahap 5 — Database Design
- ERD;
- tabel;
- relasi;
- migration/schema;
- seed data.

## Tahap 6 — Backend
- authentication;
- category;
- product;
- stock;
- cart;
- order;
- rental;
- API.

## Tahap 7 — Frontend
- layout;
- navigation;
- homepage;
- category;
- product;
- cart;
- checkout;
- account.

## Tahap 8 — Admin
- dashboard;
- product management;
- category;
- stock;
- orders;
- users.

## Tahap 9 — Integration
Hubungkan frontend dengan backend.

## Tahap 10 — Testing
Lakukan functional, integration, UI, responsive, security, dan performance testing sesuai scope.

## Tahap 11 — Deployment
Setelah local testing stabil:
- production environment;
- database production;
- environment variables;
- build;
- deployment;
- final testing.

## Aturan Setiap Tahap
AI harus:
1. menjelaskan tujuan tahap;
2. menjelaskan apa yang akan dilakukan;
3. memberikan langkah konkret;
4. meminta user menjalankan langkah;
5. membantu jika error;
6. melakukan verifikasi;
7. berhenti dan menunggu konfirmasi.

Jangan melompat ke tahap berikutnya tanpa konfirmasi.
