# Pipeline Project v2 (REVISI) — Medical Equipment Marketplace

**Menggantikan:** `pipeline-project-medical-marketplace.md` (v1, berbasis Laravel monolith — sudah tidak berlaku)
**Alasan revisi:** Pivot arsitektur dari Laravel monolith (Inertia) ke **decoupled**: Laravel 12 API-only + Next.js frontend terpisah, MySQL → PostgreSQL.
**Progres v1 yang dibuang:** Seluruh setup Tahap 3–5 versi lama (project Laravel Inertia, migration MySQL) — dianggap tidak ada, mulai dari nol.
**Yang TETAP tidak berubah:** FR-01–FR-31, seluruh business rules di `medical_marketplace_business_requirements_policies.md`, keputusan KTP per-akun, no-deposit, Midtrans sebagai payment gateway.

---

## Stack Final (v2)

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12 — **API-only mode** |
| Auth | Laravel Sanctum — **token-based (Bearer)**, bukan cookie-based SPA |
| Database | **PostgreSQL** 16+ |
| Frontend | **Next.js** (App Router) + **TypeScript (strict mode)** |
| Data fetching/cache | TanStack Query |
| State management client-only | Zustand |
| Form + validasi | React Hook Form + Zod |
| Styling | Tailwind CSS |
| Payment Gateway | Midtrans |
| Repository | **Monorepo** — 1 repo GitHub, folder `/frontend` dan `/backend` |

---

## Pipeline Tahap (Renumbered)

```text
Tahap 1  ✅ Requirement                    (SELESAI — tidak berubah)
Tahap 2  ✅ Technology Selection            (SELESAI — direvisi ke stack v2 di atas)
Tahap 3  🔜 Repository Setup                (BARU — mulai dari sini)
Tahap 4     Environment Installation
Tahap 5     Project Architecture
Tahap 6     Database Design (ERD)
Tahap 7     Backend Development
Tahap 8     Frontend Development
Tahap 9     Admin Panel
Tahap 10    Integration (termasuk Midtrans)
Tahap 11    Testing
Tahap 12    Deployment
```

---

## Ringkasan Tiap Tahap (v2)

### Tahap 3 — Repository Setup *(BARU)*
Inisialisasi repo GitHub monorepo, struktur folder `/frontend` `/backend`, `.gitignore`, README, branch strategy. Copilot akan bekerja dengan acuan `repository-knowledge.md`.

### Tahap 4 — Environment Installation
Install PostgreSQL (baru, belum pernah diinstall — XAMPP tidak menyediakan ini), pastikan Node.js/PHP/Composer yang sudah ada tetap kompatibel. Setup `.env` untuk backend & frontend terpisah.

### Tahap 5 — Project Architecture
- Backend: setup Laravel API-only (hapus view/Blade default), konfigurasi Sanctum untuk token auth, CORS, struktur `routes/api.php` modular, disk storage privat untuk KTP.
- Frontend: setup Next.js App Router, struktur folder (`app/`, `components/`, `lib/`, `store/`, `types/`), konfigurasi TanStack Query provider, Zustand store awal, Tailwind, koneksi API client (axios/fetch wrapper) ke backend.

### Tahap 6 — Database Design (ERD)
Menggunakan `database-schema-final.md` (sudah final, tinggal migration ke PostgreSQL).

### Tahap 7 — Backend Development
Membangun REST API endpoints untuk seluruh FR-01–FR-31, mengikuti `skill: setup-feature` di `skills.md`.

### Tahap 8 — Frontend Development
Membangun UI Next.js sesuai `04-ui-ux.md` + desain referensi yang akan dilampirkan user per halaman (penanda akan diberikan saat tahap ini dimulai).

### Tahap 9 — Admin Panel
Dashboard, manajemen produk/kategori/stok/pesanan/user, review KTP.

### Tahap 10 — Integration
Menghubungkan Next.js ↔ Laravel API (auth token flow), integrasi Midtrans end-to-end (sandbox → guided setup bersama saat tahap ini tiba).

### Tahap 11 — Testing
Sesuai `06-testing.md`, ditambah unit test frontend (Vitest) dan feature test backend (Pest).

### Tahap 12 — Deployment
Deploy backend & frontend **terpisah** (2 layanan) — detail dibahas saat tahap ini tiba.

---

## Dokumen Pendukung Baru (v2)

| Dokumen | Fungsi |
|---|---|
| `ai-agent-manifest.md` | Aturan kerja utama untuk AI copilot (role, tech stack, coding standards, Execution Log) |
| `skills.md` | Daftar skill/SOP yang boleh dipakai copilot, disesuaikan konteks proyek ini |
| `repository-knowledge.md` | Struktur repo monorepo, konvensi penamaan, branch strategy |
| `database-schema-final.md` | ERD final (PostgreSQL) |
| `application-flow-user-admin.md` | Alur aplikasi lengkap sisi user & admin |

Dokumen lama yang **tetap berlaku** (tidak berubah): `09-functional-requirements.md` + notulensi FR-21–31, `medical_marketplace_business_requirements_policies.md`, `04-ui-ux.md`, `06-testing.md`.

---

## Aturan yang Tetap Berlaku

Sesuai kesepakatan sejak awal: **satu tahap pada satu waktu**, tidak lompat tanpa konfirmasi eksplisit user. Perubahan kali ini besar, tapi metodologi kerja bertahap tidak berubah.
