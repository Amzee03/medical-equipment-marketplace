<?php

// app/Http/Controllers/Api/CategoryController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    // =========================================================================
    // PUBLIC — GET /api/categories
    // =========================================================================

    /**
     * Daftar kategori aktif untuk ditampilkan di storefront (tanpa auth).
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    // =========================================================================
    // ADMIN — GET /api/admin/categories
    // =========================================================================

    /**
     * Daftar semua kategori (termasuk inactive) untuk admin panel.
     */
    public function adminIndex(): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    // =========================================================================
    // ADMIN — POST /api/admin/categories
    // =========================================================================

    /**
     * Buat kategori baru.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create([
            'name'        => $request->name,
            'slug'        => $request->slug, // sudah di-generate dari prepareForValidation jika kosong
            'description' => $request->description,
            'status'      => $request->status ?? 'active',
        ]);

        return response()->json([
            'message' => 'Kategori berhasil dibuat.',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    // =========================================================================
    // ADMIN — PUT/PATCH /api/admin/categories/{id}
    // =========================================================================

    /**
     * Update kategori yang ada.
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $category->fill($request->only(['name', 'slug', 'description', 'status']))->save();

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => new CategoryResource($category),
        ]);
    }

    // =========================================================================
    // ADMIN — DELETE /api/admin/categories/{id}
    // =========================================================================

    /**
     * Hapus kategori. Jika masih ada produk terkait, database constraint akan mencegah penghapusan.
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        // Cek apakah masih ada produk yang menggunakan kategori ini
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki produk terkait.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
