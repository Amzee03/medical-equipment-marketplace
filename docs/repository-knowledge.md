# Repository Knowledge — Medical Equipment Marketplace

Dokumen ini adalah acuan struktur repository monorepo untuk AI copilot. Dibuat **sebelum** repo benar-benar diinisialisasi (Tahap 3) — jadi ini blueprint yang harus **diikuti** saat setup repo pertama kali, bukan deskripsi kondisi yang sudah ada.

---

## Struktur Root

```text
medical-equipment-marketplace/          (root repo — 1 repo GitHub, monorepo)
├── frontend/                           (Next.js — App Router, TypeScript)
├── backend/                            (Laravel 12 — API-only)
├── docs/                               (seluruh knowledge base .md proyek)
├── .gitignore                          (root, cover kedua sisi)
├── README.md
└── .github/                            (opsional, untuk CI/CD nanti di Tahap Deployment)
```

---

## Struktur `/frontend`

```text
frontend/
├── src/
│   ├── app/                            (routing Next.js App Router)
│   │   ├── (storefront)/               (route group: halaman publik user)
│   │   │   ├── page.tsx                (homepage)
│   │   │   ├── produk/
│   │   │   ├── kategori/
│   │   │   ├── keranjang/
│   │   │   ├── checkout/
│   │   │   ├── akun/
│   │   │   └── pesanan/
│   │   ├── (admin)/                    (route group: admin panel)
│   │   │   └── admin/
│   │   ├── (auth)/                     (login, register)
│   │   └── layout.tsx
│   ├── components/                     (komponen reusable, PascalCase, mis. ProductCard.tsx)
│   ├── features/                       (opsional: grouping per domain — cart/, rental/, order/)
│   ├── lib/
│   │   ├── api-client.ts               (axios/fetch wrapper, base URL backend + Bearer token)
│   │   └── utils.ts
│   ├── store/                          (Zustand stores, camelCase, mis. cartStore.ts)
│   ├── hooks/                          (custom hooks, mis. useCart.ts)
│   └── types/                          (TypeScript interfaces/types, mis. product.ts, order.ts)
├── public/
├── package.json
├── tsconfig.json (strict: true)
├── tailwind.config.ts
└── .env.local                          (NEXT_PUBLIC_API_URL, dsb — TIDAK di-commit)
```

**Konvensi penamaan frontend:**
- Komponen React → `PascalCase.tsx` (mis. `ProductCard.tsx`)
- Utility/hook/store → `camelCase.ts` (mis. `useCart.ts`, `cartStore.ts`)
- Tidak boleh pakai tipe `any` di TypeScript (strict mode wajib)

---

## Struktur `/backend`

```text
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/            (semua controller versi API, mis. ProductController.php)
│   │   ├── Requests/                   (Form Request untuk validasi, mis. StoreProductRequest.php)
│   │   └── Resources/                  (API Resource untuk format response JSON)
│   ├── Models/                         (Eloquent models, sesuai database-schema-final.md)
│   └── Services/                       (business logic kompleks, mis. RentalAvailabilityService.php, 
│                                         LateFeeCalculatorService.php — supaya controller tetap ringkas)
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php                         (entry point, include file modular di bawah)
│   ├── api/
│   │   ├── auth.php
│   │   ├── product.php
│   │   ├── cart.php
│   │   ├── order.php
│   │   └── admin.php
├── storage/app/private/ktp/            (dokumen KTP, TIDAK public)
├── composer.json
└── .env                                (DB credential, Midtrans key, dsb — TIDAK di-commit)
```

**Konvensi penamaan backend:**
- Tabel database → `snake_case` (mis. `equipment_units`)
- Model → `PascalCase` singular (mis. `EquipmentUnit.php`)
- Route API → `kebab-case` di URL (mis. `/api/equipment-units`)

---

## `.gitignore` Root (Wajib)

```text
# Frontend
frontend/node_modules/
frontend/.next/
frontend/.env.local

# Backend
backend/vendor/
backend/.env
backend/storage/app/private/*
!backend/storage/app/private/.gitignore
backend/storage/logs/*
```

---

## Branch Strategy

- `main` → selalu stabil, hanya menerima merge setelah tahap diuji & dikonfirmasi user.
- `feature/tahap-{N}-{nama-singkat}` → branch kerja per tahap, mis. `feature/tahap-7-backend-auth`.
- Commit message: prefix sederhana `feat:`, `fix:`, `chore:`, `docs:` (mis. `feat: tambah endpoint rental availability check`).

---

## Lokasi Dokumen Knowledge Base

Seluruh file `.md` (requirement, schema, flow, business policy) disimpan di folder `docs/` di root repo, supaya AI copilot bisa selalu mengaksesnya sebagai referensi tanpa perlu di-upload ulang tiap sesi baru (jika tools copilot mendukung membaca file dari workspace).
