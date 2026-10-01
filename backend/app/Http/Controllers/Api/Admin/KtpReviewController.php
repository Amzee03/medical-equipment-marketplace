<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KtpReviewController extends Controller
{
    /**
     * List user dengan ktp_status = 'pending_review'.
     */
    public function pending(Request $request)
    {
        $users = User::where('ktp_status', 'pending_review')
            ->select('id', 'name', 'email', 'ktp_file_path', 'ktp_status', 'ktp_submitted_at')
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * Approve KTP user dan otomatis konfirmasi order awaiting_ktp_verification.
     */
    public function approve(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->ktp_status !== 'pending_review') {
            return response()->json(['message' => 'Status KTP user tidak dalam antrian review.'], 422);
        }

        DB::transaction(function () use ($user, $request) {
            $user->ktp_status = 'approved';
            $user->ktp_reviewed_at = now();
            $user->ktp_reviewed_by = $request->user()->id;
            $user->save();

            // Otomatis confirm semua rental order yang awaiting_ktp_verification
            Order::where('user_id', $user->id)
                ->where('status', 'awaiting_ktp_verification')
                ->update(['status' => 'confirmed']);
        });

        return response()->json([
            'message' => 'KTP berhasil disetujui. Order rental yang tertunda otomatis dikonfirmasi.',
        ]);
    }

    /**
     * Reject KTP user.
     */
    public function reject(Request $request, $userId)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $user = User::findOrFail($userId);

        if ($user->ktp_status !== 'pending_review') {
            return response()->json(['message' => 'Status KTP user tidak dalam antrian review.'], 422);
        }

        $user->ktp_status = 'rejected';
        $user->ktp_reviewed_at = now();
        $user->ktp_reviewed_by = $request->user()->id;
        $user->ktp_rejection_reason = $request->rejection_reason;
        $user->save();

        return response()->json(['message' => 'KTP ditolak.']);
    }

    /**
     * Reset KTP status ke pending_review untuk re-verifikasi manual.
     */
    public function resetVerification(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $user->ktp_status = 'pending_review';
        $user->ktp_rejection_reason = null;
        $user->ktp_reviewed_at = null;
        $user->ktp_reviewed_by = null;
        $user->save();

        return response()->json(['message' => 'Status KTP direset ke pending_review.']);
    }
}
