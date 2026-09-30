<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Get all orders for admin.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $orders = $query->latest()->paginate(20);

        return OrderResource::collection($orders);
    }

    /**
     * Update order status manually.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($id);

        $validPurchaseStatuses = ['pending_payment', 'paid', 'processing', 'shipped', 'completed', 'cancelled', 'cancellation_requested'];
        $validRentalStatuses = ['pending_payment', 'paid', 'awaiting_ktp_verification', 'confirmed', 'active', 'awaiting_return', 'overdue', 'returned', 'completed', 'cancelled', 'cancellation_requested'];
        
        $validStatuses = $order->order_type === 'purchase' ? $validPurchaseStatuses : $validRentalStatuses;
        
        if (!in_array($request->status, $validStatuses)) {
            return response()->json(['message' => 'Status tidak valid untuk tipe order ini.'], 422);
        }
        
        // Simple logic for admin update, in reality should validate transitions
        // e.g. from completed back to paid is not allowed
        if ($order->status === 'completed' && $request->status !== 'completed') {
            return response()->json(['message' => 'Pesanan yang sudah selesai tidak dapat diubah statusnya.'], 422);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui.',
            'data' => new OrderResource($order)
        ]);
    }

    /**
     * Approve or reject cancellation request.
     */
    public function reviewCancellation(Request $request, $id)
    {
        $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'reason' => 'nullable|string'
        ]);

        $order = Order::findOrFail($id);

        if ($order->status !== 'cancellation_requested') {
            return response()->json(['message' => 'Pesanan tidak dalam status pengajuan pembatalan.'], 422);
        }

        if ($request->action === 'approve') {
            $order->status = 'cancelled';
            $order->cancelled_at = now();
            $order->cancelled_by = $request->user()->id; // Admin ID
            $order->cancellation_reason = 'Disetujui Admin: ' . ($request->reason ?? $order->cancellation_reason);
        } else {
            // Reject cancellation, back to processing
            $order->status = 'processing';
            // Optionally log the rejection reason somewhere
        }

        $order->save();

        return response()->json([
            'message' => 'Review pembatalan berhasil diproses.',
            'data' => new OrderResource($order)
        ]);
    }
}
