<?php

// app/Http/Controllers/Api/ProductImageController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\UploadProductImagesRequest;
use App\Http\Resources\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    // =========================================================================
    // ADMIN — POST /api/admin/products/{id}/images
    // =========================================================================

    /**
     * Upload satu atau lebih gambar produk ke disk 'public'.
     * Gambar disimpan di folder products/{product_id}/.
     * Gambar pertama yang diupload otomatis menjadi primary jika belum ada primary.
     */
    public function store(UploadProductImagesRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $savedImages = [];

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $maxSortOrder = $product->images()->max('sort_order') ?? 0;

        DB::transaction(function () use ($request, $product, $hasPrimary, $maxSortOrder, &$savedImages) {
            foreach ($request->file('images') as $index => $file) {
                $filename = 'products/' . $product->id . '/' . uniqid('img_', true) . '.' . $file->getClientOriginalExtension();
                Storage::disk('public')->put($filename, $file->get());

                $isPrimary = (! $hasPrimary && $index === 0);

                $image = ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $filename,
                    'is_primary' => $isPrimary,
                    'sort_order' => $maxSortOrder + $index + 1,
                ]);

                $savedImages[] = $image;
            }
        });

        return response()->json([
            'message' => count($savedImages) . ' gambar berhasil diunggah.',
            'data'    => ProductImageResource::collection(collect($savedImages)),
        ], 201);
    }

    // =========================================================================
    // ADMIN — DELETE /api/admin/products/{productId}/images/{imageId}
    // =========================================================================

    /**
     * Hapus satu gambar produk dari storage dan database.
     */
    public function destroy(int $productId, int $imageId): JsonResponse
    {
        $image = ProductImage::where('product_id', $productId)->findOrFail($imageId);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        // Jika gambar yang dihapus adalah primary, otomatis set gambar pertama yang tersisa sebagai primary
        if ($image->is_primary) {
            $next = ProductImage::where('product_id', $productId)->orderBy('sort_order')->first();
            $next?->update(['is_primary' => true]);
        }

        return response()->json([
            'message' => 'Gambar berhasil dihapus.',
        ]);
    }

    // =========================================================================
    // ADMIN — PATCH /api/admin/products/{productId}/images/{imageId}/primary
    // =========================================================================

    /**
     * Set satu gambar sebagai primary. Otomatis unset is_primary=false untuk semua
     * gambar lain di produk yang sama — dilakukan dalam satu transaction.
     */
    public function setPrimary(int $productId, int $imageId): JsonResponse
    {
        $image = ProductImage::where('product_id', $productId)->findOrFail($imageId);

        DB::transaction(function () use ($productId, $image) {
            // Reset semua gambar produk ini ke non-primary
            ProductImage::where('product_id', $productId)->update(['is_primary' => false]);
            // Set yang dipilih sebagai primary
            $image->update(['is_primary' => true]);
        });

        return response()->json([
            'message' => 'Gambar utama berhasil diperbarui.',
            'data'    => new ProductImageResource($image->fresh()),
        ]);
    }
}
