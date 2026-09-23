<?php

// app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk meng-expose data user ke frontend.
 * Field sensitif (password, ktp_file_path) TIDAK diekspos.
 * ktp_status cukup diekspos sebagai string enum untuk keperluan UI.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'phone'             => $this->phone,
            'phone_verified_at' => $this->phone_verified_at?->toIso8601String(),
            'role'              => $this->role,
            'google_id'         => $this->when($this->google_id !== null, (bool) $this->google_id),
            // Tampilkan keberadaan google linkage sebagai boolean — jangan ekspos id aslinya
            'has_google_linked' => $this->google_id !== null,
            // KTP: hanya status (bukan path mentah), supaya frontend bisa menampilkan progress verifikasi
            'ktp_status'        => $this->ktp_status,
            'ktp_submitted_at'  => $this->ktp_submitted_at?->toIso8601String(),
            // Alasan penolakan perlu diketahui user agar bisa upload ulang
            'ktp_rejection_reason' => $this->when(
                $this->ktp_status === 'rejected',
                $this->ktp_rejection_reason
            ),
            'created_at'        => $this->created_at->toIso8601String(),
        ];
    }
}
