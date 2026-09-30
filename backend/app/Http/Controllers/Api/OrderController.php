<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Get user's orders.
     */
    public function index(Request $request)
    {
        $query = $request->user()->orders()->with(['orderItems.product']);

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15);
        return OrderResource::collection($orders);
    }

    /**
     * Get order details.
     */
    public function show(Request $request, $id)
    {
        $order = Order::with(['orderItems.product', 'orderItems.rentalItem.equipmentUnit', 'payment'])->findOrFail($id);
        
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        return new OrderResource($order);
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        $user = $request->user();
        $order = Order::findOrFail($id);

        if ($order->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        if (in_array($order->status, ['shipped', 'active', 'completed', 'cancelled', 'awaiting_return', 'overdue', 'returned'])) {
            return response()->json(['message' => 'Pesanan tidak dapat dibatalkan pada tahap ini. Silakan hubungi layanan pelanggan atau ajukan komplain.'], 422);
        }

        try {
            DB::beginTransaction();

            if (in_array($order->status, ['pending_payment', 'paid', 'awaiting_ktp_verification', 'confirmed'])) {
                $order->status = 'cancelled';
                $order->cancelled_at = now();
                $order->cancelled_by = $user->id;
                $order->cancellation_reason = $request->cancellation_reason;
                $order->save();
            } elseif ($order->status === 'processing') {
                $order->status = 'cancellation_requested';
                $order->cancellation_reason = $request->cancellation_reason;
                $order->save();
            } else {
                return response()->json(['message' => 'Status pesanan tidak valid untuk pembatalan.'], 422);
            }

            DB::commit();

            return response()->json([
                'message' => 'Permintaan pembatalan berhasil diproses.',
                'data' => new OrderResource($order)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membatalkan pesanan.'], 500);
        }
    }
}
