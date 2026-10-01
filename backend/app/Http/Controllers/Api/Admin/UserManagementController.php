<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * List semua user, filter by role atau ktp_status.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('ktp_status')) {
            $query->where('ktp_status', $request->ktp_status);
        }

        $users = $query->select('id', 'name', 'email', 'role', 'ktp_status', 'is_active', 'created_at')
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * Detail user + ringkasan riwayat order.
     */
    public function show(Request $request, $id)
    {
        $user = User::with([
            'orders' => fn($q) => $q->select('id', 'user_id', 'order_number', 'order_type', 'status', 'total', 'created_at')->latest()->take(10)
        ])->findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'ktp_status' => $user->ktp_status,
            'is_active' => $user->is_active,
            'phone' => $user->phone,
            'created_at' => $user->created_at,
            'recent_orders' => $user->orders,
        ]);
    }

    /**
     * Toggle is_active user (nonaktifkan/aktifkan akun).
     */
    public function toggleActive(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent admin from deactivating their own account
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Anda tidak dapat menonaktifkan akun sendiri.'], 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => $user->is_active ? 'Akun berhasil diaktifkan.' : 'Akun berhasil dinonaktifkan.',
            'is_active' => $user->is_active,
        ]);
    }
}
