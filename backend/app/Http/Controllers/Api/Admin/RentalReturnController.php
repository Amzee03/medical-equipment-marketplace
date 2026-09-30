<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\RentalItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RentalReturnController extends Controller
{
    /**
     * Proses pengembalian unit sewa oleh admin.
     * Menghitung late_fee, memperbarui status unit, membuat DamageReport jika perlu,
     * dan menandai Order sebagai completed jika semua rental_items sudah returned.
     */
    public function processReturn(Request $request, $rentalItemId)
    {
        $request->validate([
            'condition_at_return' => ['required', Rule::in(['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])],
            'notes' => 'nullable|string',
        ]);

        $rentalItem = RentalItem::with([
            'orderItem.order',
            'orderItem.product',
            'equipmentUnit',
        ])->findOrFail($rentalItemId);

        if ($rentalItem->actual_return_date !== null) {
            return response()->json(['message' => 'Unit ini sudah diproses pengembaliannya.'], 422);
        }

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $dueDate = Carbon::parse($rentalItem->rental_due_date)->startOfDay();
            $actualDate = $now->startOfDay();

            // Hitung late_fee:
            // Selisih hari = max(0, actual_return_date - rental_due_date)
            // late_fee = late_days * rental_price_daily (dari produk terkait)
            $lateDays = 0;
            $lateFee = 0;

            if ($actualDate->gt($dueDate)) {
                // late_days = selisih hari antara tanggal kembali aktual dan tanggal jatuh tempo
                // Rumus: (actual_return_date - rental_due_date) dalam hari (Integer, selalu positif)
                $lateDays = (int) $dueDate->diffInDays($actualDate);
                $rentalPriceDaily = (float) $rentalItem->orderItem->product->rental_price_daily;
                $lateFee = $lateDays * $rentalPriceDaily;
            }

            // Update rental_item
            $rentalItem->actual_return_date = $now;
            $rentalItem->condition_at_return = $request->condition_at_return;
            $rentalItem->late_days = $lateDays;
            $rentalItem->late_fee = $lateFee;
            $rentalItem->rental_status = 'returned';
            $rentalItem->save();

            // Update equipment_unit status
            $unit = $rentalItem->equipmentUnit;
            $unit->status = match($request->condition_at_return) {
                'baik' => 'available',
                'rusak_ringan', 'rusak_berat' => 'maintenance',
                'hilang' => 'retired',
            };
            if ($request->filled('notes')) {
                $unit->notes = $request->notes;
            }
            $unit->save();

            // Buat DamageReport otomatis jika kondisi bukan 'baik'
            if ($request->condition_at_return !== 'baik') {
                DamageReport::create([
                    'rental_item_id' => $rentalItem->id,
                    'reported_by' => $request->user()->id,
                    'condition' => $request->condition_at_return,
                    'description' => $request->notes ?? 'Kondisi: ' . $request->condition_at_return,
                    'status' => 'pending',
                ]);
            }

            // Cek apakah semua rental_items dalam order ini sudah 'returned'
            $order = $rentalItem->orderItem->order;
            $allRentalItems = RentalItem::whereHas('orderItem', function ($q) use ($order) {
                $q->where('order_id', $order->id);
            })->get();

            $allReturned = $allRentalItems->every(fn($ri) => $ri->rental_status === 'returned');

            if ($allReturned) {
                $order->status = 'completed';
                $order->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Pengembalian berhasil diproses.',
                'late_days' => $lateDays,
                'late_fee' => $lateFee,
                'rental_status' => $rentalItem->rental_status,
                'unit_status' => $unit->status,
                'order_status' => $order->fresh()->status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses pengembalian: ' . $e->getMessage()], 500);
        }
    }
}
