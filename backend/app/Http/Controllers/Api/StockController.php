<?php

// app/Http/Controllers/Api/StockController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\UpdateEquipmentUnitRequest;
use App\Http\Resources\EquipmentUnitResource;
use App\Models\EquipmentUnit;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    // =========================================================================
    // ADMIN — PATCH /api/admin/products/{id}/stock
    // =========================================================================

    /**
     * Update stock_purchase produk.
     *
     * Dibungkus DB::transaction() sesuai ai-agent-manifest.md §3 — mutasi stok
     * rawan race condition di lingkungan concurrent request.
     */
    public function updateStock(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'stock_purchase' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::findOrFail($id);

        DB::transaction(function () use ($request, $product) {
            $product->update(['stock_purchase' => $request->integer('stock_purchase')]);
        });

        return response()->json([
            'message'        => 'Stok pembelian berhasil diperbarui.',
            'stock_purchase' => $product->fresh()->stock_purchase,
        ]);
    }

    // =========================================================================
    // ADMIN — GET /api/admin/products/{id}/equipment-units
    // =========================================================================

    /**
     * Daftar semua unit fisik (equipment_units) milik produk ini.
     */
    public function listUnits(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $units   = $product->equipmentUnits()->orderBy('unit_code')->get();

        return response()->json([
            'data' => EquipmentUnitResource::collection($units),
        ]);
    }

    // =========================================================================
    // ADMIN — POST /api/admin/products/{id}/equipment-units
    // =========================================================================

    /**
     * Tambah unit fisik baru ke produk.
     *
     * unit_code di-generate otomatis: {SKU}-U{nomor urut 3 digit, mis. U001}.
     * Dibungkus DB::transaction() untuk mencegah race condition pada hitungan urutan.
     */
    public function addUnit(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $unit = DB::transaction(function () use ($product) {
            // Hitung total unit yang pernah dibuat untuk produk ini (termasuk retired).
            // Query biasa sudah mencakup semua status — EquipmentUnit TIDAK pakai SoftDeletes,
            // "penghapusan" dilakukan dengan status='retired' (bukan kolom deleted_at).
            $nextNumber = $product->equipmentUnits()->count() + 1;
            $unitCode   = $product->sku . '-U' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            return EquipmentUnit::create([
                'product_id' => $product->id,
                'unit_code'  => $unitCode,
                'condition'  => 'baik',
                'status'     => 'available',
            ]);
        });

        return response()->json([
            'message' => 'Unit berhasil ditambahkan.',
            'data'    => new EquipmentUnitResource($unit),
        ], 201);
    }

    // =========================================================================
    // ADMIN — PATCH /api/admin/equipment-units/{id}
    // =========================================================================

    /**
     * Update condition, status, atau notes sebuah unit.
     * Status 'retired' tidak dapat di-set via endpoint ini (gunakan DELETE).
     */
    public function updateUnit(UpdateEquipmentUnitRequest $request, int $id): JsonResponse
    {
        $unit = EquipmentUnit::findOrFail($id);

        DB::transaction(function () use ($request, $unit) {
            $unit->fill($request->validated())->save();
        });

        return response()->json([
            'message' => 'Unit berhasil diperbarui.',
            'data'    => new EquipmentUnitResource($unit->fresh()),
        ]);
    }

    // =========================================================================
    // ADMIN — DELETE /api/admin/equipment-units/{id}
    // =========================================================================

    /**
     * Soft-retire unit: set status='retired' tanpa hard delete.
     *
     * Alasan tidak hard delete: equipment_units direferensikan oleh rental_items
     * (historical data sewa). Hard delete akan melanggar constraint FK dan
     * merusak riwayat transaksi. Status 'retired' berarti unit tidak lagi
     * tersedia untuk sewa baru namun riwayat lamanya tetap utuh.
     */
    public function retireUnit(int $id): JsonResponse
    {
        $unit = EquipmentUnit::findOrFail($id);

        // Cegah retire unit yang sedang aktif disewa
        if ($unit->status === 'rented') {
            return response()->json([
                'message' => 'Unit tidak dapat di-retire karena sedang dalam status disewa.',
            ], 422);
        }

        DB::transaction(function () use ($unit) {
            $unit->update(['status' => 'retired']);
        });

        return response()->json([
            'message' => 'Unit berhasil di-retire. Data riwayat sewa tetap tersimpan.',
        ]);
    }
}
