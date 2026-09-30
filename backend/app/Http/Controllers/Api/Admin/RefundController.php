<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RefundResource;
use App\Models\Order;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RefundController extends Controller
{
    /**
     * Get all refunds.
     */
    public function index(Request $request)
    {
        $query = Refund::with(['order.user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $refunds = $query->latest()->paginate(20);

        return RefundResource::collection($refunds);
    }

    /**
     * Admin create refund record.
     */
    public function store(Request $request, $orderId)
    {
        $request->validate([
            'category' => ['required', Rule::in(['owner_fault', 'item_mismatch', 'user_cancellation'])],
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string',
        ]);

        $order = Order::findOrFail($orderId);

        $refund = Refund::create([
            'order_id' => $order->id,
            'category' => $request->category,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Data refund berhasil dibuat.',
            'data' => new RefundResource($refund)
        ], 201);
    }

    /**
     * Approve/reject/complete a refund.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'completed'])],
        ]);

        $refund = Refund::findOrFail($id);
        $refund->status = $request->status;

        if (in_array($request->status, ['approved', 'completed', 'rejected'])) {
            $refund->processed_by = $request->user()->id;
        }

        $refund->save();

        return response()->json([
            'message' => 'Status refund berhasil diperbarui.',
            'data' => new RefundResource($refund)
        ]);
    }
}
