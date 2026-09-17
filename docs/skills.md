# Skills.md — Medical Equipment Marketplace

Kumpulan SOP modular yang boleh dipakai AI copilot. **Copilot hanya boleh bekerja dalam batas salah satu skill di bawah ini.** Jika sebuah task tidak cocok dengan skill manapun, copilot harus **berhenti dan bertanya** ke user sebelum melanjutkan — bukan berimprovisasi di luar skill yang terdaftar.

---

## SKILL 1: `setup-feature`
**Deskripsi & Kapan Dipicu:** Membangun fitur baru yang melibatkan perubahan dari Database hingga Frontend, sesuai FR yang sedang dikerjakan.

**Input Required:** Nomor FR terkait, tabel database yang terlibat (lihat `database-schema-final.md`), endpoint API yang dibutuhkan.

**Langkah Prosedural:**
1. Buat/perbarui migration di `backend/database/migrations` — HARUS sesuai `database-schema-final.md`, jangan menambah/ubah kolom sendiri.
2. Buat Model + Controller (`Api/`) Laravel. **Wajib** bungkus logic dalam `DB::transaction()` jika melibatkan mutasi data finansial (payment, refund) atau stok (equipment_units, stock_purchase) — untuk mencegah race condition (mis. dua user checkout unit sewa yang sama bersamaan).
3. Daftarkan endpoint di file route modular terkait (`routes/api/{domain}.php`), bukan langsung di `api.php`.
4. Definisikan TypeScript interface di `frontend/src/types/`.
5. Buat API service + TanStack Query hook di `frontend/src/lib/` atau `frontend/src/hooks/`.
6. Buat komponen UI React (Tailwind CSS) sesuai desain referensi yang diberikan (jika ada) atau `04-ui-ux.md`.
7. Tulis test (lihat `write-test`).

**Output Format:** Kode sumber lengkap per file + instruksi migrasi/uji coba + **Execution Log** (lihat `ai-agent-manifest.md`).

**Validasi & Cek:** Migration jalan tanpa error, endpoint merespons JSON sesuai `Http/Resources`, UI merender data dengan benar, tidak ada `any` di TypeScript.

---

## SKILL 2: `refactor-component`
**Deskripsi & Kapan Dipicu:** Membersihkan kode, memecah komponen besar, atau meningkatkan performa — baik di frontend (React) maupun backend (Controller/Service Laravel).

**Langkah Prosedural:**
1. Analisis dependency & props (frontend) atau dependency Service/Model (backend) pada kode lama.
2. Pecah jadi bagian modular kecil (`frontend/src/components/` atau `backend/app/Services/`).
3. Pindahkan logic kompleks ke custom hook/Zustand store (frontend) atau Service class (backend) — controller Laravel harus tetap ringkas.
4. Pastikan TypeScript tetap strict (tanpa `any`).
5. Verifikasi tidak ada fungsionalitas yang rusak (regression check).

**Output Format:** Kode refaktor bersih + TSDoc/PHPDoc + Execution Log.

**Validasi & Cek:** `npm run lint` (frontend) dan `php artisan test` terkait (backend) tidak error.

---

## SKILL 3: `debug-error`
**Deskripsi & Kapan Dipicu:** Menerima laporan bug, error stack trace, atau kegagalan test.

**Input Required:** Pesan error, log, file relevan.

**Langkah Prosedural:**
1. Identifikasi root cause dari stack trace/pesan error.
2. Lacak file & baris kode spesifik.
3. Evaluasi dampak perbaikan ke modul lain (ingat: monorepo, tapi backend & frontend tetap terpisah secara runtime — perubahan di satu sisi jangan asumsikan otomatis berdampak ke sisi lain tanpa update API contract).
4. Terapkan perbaikan presisi, minimal, tidak mengubah hal yang tidak terkait.
5. Tambahkan/perbarui test case untuk mencegah regresi.

**Output Format:** Penjelasan singkat root cause + kode perbaikan + Execution Log.

**Validasi & Cek:** Jalankan ulang test terkait, pastikan error hilang.

---

## SKILL 4: `write-test`
**Deskripsi & Kapan Dipicu:** Modul/fungsi/endpoint baru selesai dibuat dan butuh test coverage.

**Langkah Prosedural:**
1. Tentukan jenis test: Unit test (Vitest, frontend) atau Feature test (Pest, backend).
2. Cakup **Happy Path** + **Edge Cases** relevan konteks proyek ini, contoh wajib dipertimbangkan:
   - Konflik periode sewa pada unit yang sama (FR-09)
   - Stok tidak boleh negatif (purchase) / unit tidak boleh double-booked (rental)
   - Webhook Midtrans idempotent (tidak boleh double-process pembayaran yang sama — FR-21)
   - Transisi status KTP (`pending_review` → `approved`/`rejected`, dan reset admin)
   - Perhitungan late fee (FR-25): tepat di hari jatuh tempo, 1 hari telat, banyak hari telat
   - Cancellation ditolak untuk status `Shipped`/`Completed`/`Active` (FR-23)
3. Tulis test dengan penamaan deskriptif.
4. Jalankan test lokal.

**Output Format:** File test baru + Execution Log.

**Validasi & Cek:** Seluruh skenario `passed`.

---

## SKILL 5: `security-review`
**Deskripsi & Kapan Dipicu:** Sebelum merge ke `main`, atau sebelum sebuah modul dianggap "selesai" di suatu tahap.

**Langkah Prosedural:**
1. Pastikan endpoint sensitif (admin, checkout, payment) dilindungi middleware Sanctum + validasi `role`.
2. Pastikan tidak ada hardcoded secret (Midtrans key, DB credential) — harus lewat `.env`.
3. Validasi input server & client (Zod di frontend, Form Request/`validated()` di backend) untuk cegah SQL injection & mass assignment (`$fillable` wajib diisi eksplisit).
4. **Khusus proyek ini:** pastikan file KTP hanya bisa diakses oleh admin yang terautentikasi (disk private, endpoint download terlindungi, tidak ada URL publik langsung ke file).
5. Validasi status pembayaran **hanya** dipercaya dari webhook Midtrans, tidak pernah dari input frontend (FR-21).
6. Cek konsistensi transaksi stok/unit sewa untuk hindari race condition (dua checkout rebutan unit yang sama).

**Output Format:** Laporan audit singkat + rekomendasi perbaikan (jika ada) + Execution Log.

**Validasi & Cek:** Checklist di atas terpenuhi semua, tidak ada celah kritis/menengah.

---

## Kapan Copilot HARUS Berhenti dan Bertanya (Di Luar Skill)

- Task melibatkan mengubah keputusan bisnis yang sudah dikunci (FR-01–FR-31, business policy) — bukan wewenang copilot untuk mengubah, hanya user.
- Task melibatkan tahap pipeline yang belum dimulai (lihat `pipeline-v2-revisi.md`) — copilot tidak boleh mengerjakan di luar scope tahap aktif meski secara teknis bisa.
- Task butuh keputusan arsitektur baru yang belum ada di dokumen manapun.
