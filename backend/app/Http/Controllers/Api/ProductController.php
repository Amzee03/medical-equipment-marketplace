<?php

// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // =========================================================================
    // PUBLIC — GET /api/products
    // =========================================================================

    /**
     * Daftar produk aktif dengan pagination, search ILIKE (PostgreSQL), dan filter.
     *
     * Query params:
     *   - category_id   : filter by kategori
     *   - q             : full-text search ILIKE ke name/description/function/brand/model
     *   - min_price     : harga minimum (sale_price atau rental_price_daily)
     *   - max_price     : harga maksimum
     *   - purchase_available : boolean
     *   - rental_available   : boolean
     *   - sort          : name_asc|name_desc|price_asc|price_desc|newest (default: newest)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'images' => fn ($q) => $q->orderBy('sort_order')])
            ->where('status', 'active');

        // Filter: kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        // Search ILIKE — native PostgreSQL case-insensitive LIKE
        // ILIKE dipakai karena DB adalah PostgreSQL (sesuai database-schema-final.md)
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('name ILIKE ?', [$term])
                  ->orWhereRaw('description ILIKE ?', [$term])
                  ->orWhereRaw('"function" ILIKE ?', [$term])
                  ->orWhereRaw('brand ILIKE ?', [$term])
                  ->orWhereRaw('model ILIKE ?', [$term]);
            });
        }

        // Filter: harga (sale_price atau rental_price_daily sebagai acuan)
        if ($request->filled('min_price')) {
            $query->where(function ($q) use ($request) {
                $q->where('sale_price', '>=', $request->min_price)
                  ->orWhere('rental_price_daily', '>=', $request->min_price);
            });
        }

        if ($request->filled('max_price')) {
            $query->where(function ($q) use ($request) {
                $q->where('sale_price', '<=', $request->max_price)
                  ->orWhere('rental_price_daily', '<=', $request->max_price);
            });
        }

        // Filter: ketersediaan
        if ($request->boolean('purchase_available', false)) {
            $query->where('purchase_available', true);
        }

        if ($request->boolean('rental_available', false)) {
            $query->where('rental_available', true);
        }

        // Sorting
        match ($request->sort) {
            'name_asc'   => $query->orderBy('name'),
            'name_desc'  => $query->orderByDesc('name'),
            'price_asc'  => $query->orderByRaw('LEAST(COALESCE(sale_price, 999999999), COALESCE(rental_price_daily, 999999999)) ASC'),
            'price_desc' => $query->orderByRaw('GREATEST(COALESCE(sale_price, 0), COALESCE(rental_price_daily, 0)) DESC'),
            default      => $query->orderByDesc('created_at'), // newest
        };

        $products = $query->paginate(15);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    // =========================================================================
    // PUBLIC — GET /api/products/{slug}
    // =========================================================================

    /**
     * Detail produk lengkap: include category, images (sorted), dan ringkasan
     * ketersediaan equipment_units (hanya status='available').
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::with([
            'category',
            'images' => fn ($q) => $q->orderBy('is_primary', 'desc')->orderBy('sort_order'),
            'equipmentUnits',
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    // =========================================================================
    // PUBLIC — GET /api/products/{id}/availability
    // =========================================================================

    /**
     * Cek ketersediaan produk untuk periode sewa tertentu.
     */
    public function checkAvailability(Request $request, int $id, \App\Services\RentalAvailabilityService $rentalService): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $product = Product::where('status', 'active')->findOrFail($id);

        if (!$product->rental_available) {
            return response()->json(['message' => 'Produk tidak tersedia untuk disewa.'], 422);
        }

        $availability = $rentalService->checkAvailability(
            $product->id,
            $request->start_date,
            $request->end_date,
            false // no lock for public checking
        );

        return response()->json([
            'data' => $availability,
        ]);
    }

    // =========================================================================
    // ADMIN — GET /api/admin/products
    // =========================================================================

    /**
     * Daftar semua produk (termasuk inactive) untuk admin panel.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'images' => fn ($q) => $q->orderBy('sort_order')]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('name ILIKE ?', [$term])
                  ->orWhereRaw('sku ILIKE ?', [$term]);
            });
        }

        $products = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    // =========================================================================
    // ADMIN — POST /api/admin/products
    // =========================================================================

    /**
     * Buat produk baru.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'message' => 'Produk berhasil dibuat.',
            'data'    => new ProductResource($product->load(['category', 'images'])),
        ], 201);
    }

    // =========================================================================
    // ADMIN — PUT/PATCH /api/admin/products/{id}
    // =========================================================================

    /**
     * Update produk yang ada.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $product->fill($request->validated())->save();

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'data'    => new ProductResource($product->fresh(['category', 'images'])),
        ]);
    }

    // =========================================================================
    // ADMIN — DELETE /api/admin/products/{id}
    // =========================================================================

    /**
     * Hapus produk.
     * Cek apakah ada rental_items aktif — jika ada, tolak penghapusan.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::with('equipmentUnits.rentalItems')->findOrFail($id);

        // Cegah penghapusan jika ada unit yang sedang disewa
        $hasActiveRental = $product->equipmentUnits->some(
            fn ($unit) => $unit->rentalItems->whereNotIn('rental_status', ['returned', 'completed'])->isNotEmpty()
        );

        if ($hasActiveRental) {
            return response()->json([
                'message' => 'Produk tidak dapat dihapus karena memiliki unit yang sedang dalam proses sewa aktif.',
            ], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus.',
        ]);
    }
}
