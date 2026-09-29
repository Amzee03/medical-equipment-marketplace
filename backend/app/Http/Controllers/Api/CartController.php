<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Services\RentalAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CartController extends Controller
{
    protected RentalAvailabilityService $rentalService;

    public function __construct(RentalAvailabilityService $rentalService)
    {
        $this->rentalService = $rentalService;
    }

    /**
     * Get or create the authenticated user's cart.
     */
    protected function getCart(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id]);
    }

    /**
     * Tampilkan isi keranjang.
     */
    public function index(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load('items.product');

        // Kalkulasi harga rental real-time untuk setiap item rental
        foreach ($cart->items as $item) {
            if ($item->type === 'rental' && $item->product) {
                // Kalkulasi harga
                $item->rental_price_breakdown = $this->rentalService->calculateRentalPrice(
                    $item->product,
                    $item->rental_start_date->format('Y-m-d'),
                    $item->rental_end_date->format('Y-m-d')
                );
                
                // Cek ketersediaan tanpa lock
                $availability = $this->rentalService->checkAvailability(
                    $item->product_id,
                    $item->rental_start_date->format('Y-m-d'),
                    $item->rental_end_date->format('Y-m-d'),
                    false
                );
                $item->available_units_count = $availability['available_units_count'];
            }
        }

        return new CartResource($cart);
    }

    /**
     * Tambah item ke keranjang.
     */
    public function addItem(StoreCartItemRequest $request)
    {
        $cart = $this->getCart($request);
        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);

        if ($data['type'] === 'purchase') {
            if (!$product->purchase_available) {
                return response()->json(['message' => 'Produk tidak tersedia untuk dibeli.'], 422);
            }
            if ($product->stock_purchase < $data['quantity']) {
                return response()->json(['message' => 'Stok pembelian tidak mencukupi.'], 422);
            }
            
            // Check if already in cart
            $existingItem = $cart->items()->where('product_id', $product->id)->where('type', 'purchase')->first();
            if ($existingItem) {
                $existingItem->quantity += $data['quantity'];
                if ($existingItem->quantity > $product->stock_purchase) {
                    return response()->json(['message' => 'Total kuantitas di keranjang melebihi stok yang ada.'], 422);
                }
                $existingItem->save();
            } else {
                $cart->items()->create($data);
            }

        } elseif ($data['type'] === 'rental') {
            if (!$product->rental_available) {
                return response()->json(['message' => 'Produk tidak tersedia untuk disewa.'], 422);
            }

            // Validasi min/max rental days
            $start = Carbon::parse($data['rental_start_date'])->startOfDay();
            $end = Carbon::parse($data['rental_end_date'])->startOfDay();
            $days = $start->diffInDays($end) + 1;

            if ($product->min_rental_days && $days < $product->min_rental_days) {
                return response()->json(['message' => "Durasi sewa minimal adalah {$product->min_rental_days} hari."], 422);
            }
            if ($product->max_rental_days && $days > $product->max_rental_days) {
                return response()->json(['message' => "Durasi sewa maksimal adalah {$product->max_rental_days} hari."], 422);
            }

            try {
                // PENTING: Gunakan DB Transaction dan row-level locking untuk mengecek ketersediaan unit sewa
                // Lock dilakukan pada query EquipmentUnit di dalam RentalAvailabilityService
                DB::transaction(function () use ($product, $data, $cart) {
                    $availability = $this->rentalService->checkAvailability(
                        $product->id,
                        $data['rental_start_date'],
                        $data['rental_end_date'],
                        true // Lock for update
                    );

                    // Jika existing cart items (rental) yang overlap perlu dihitung, kita anggap
                    // request ini butuh tambahan quantity.
                    // Untuk mencegah race condition dari 2 request "tambah ke cart" di akun yang sama,
                    // cari apakah item serupa sudah ada di cart dengan tanggal yang sama persis
                    $existingItem = $cart->items()
                        ->where('product_id', $product->id)
                        ->where('type', 'rental')
                        ->whereDate('rental_start_date', $data['rental_start_date'])
                        ->whereDate('rental_end_date', $data['rental_end_date'])
                        ->first();
                    
                    $totalRequestedQuantity = $existingItem 
                        ? $existingItem->quantity + $data['quantity'] 
                        : $data['quantity'];

                    if ($availability['available_units_count'] < $totalRequestedQuantity) {
                        throw new \Exception('Unit tidak mencukupi untuk periode yang dipilih.');
                    }

                    // Calculate price to get the pricing tier used
                    $pricing = $this->rentalService->calculateRentalPrice(
                        $product, 
                        $data['rental_start_date'], 
                        $data['rental_end_date']
                    );
                    $data['rental_pricing_tier'] = $pricing['tier_used'];

                    if ($existingItem) {
                        $existingItem->update(['quantity' => $totalRequestedQuantity]);
                    } else {
                        $cart->items()->create($data);
                    }
                });
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        }

        return response()->json(['message' => 'Item berhasil ditambahkan ke keranjang.'], 200);
    }

    /**
     * Update item keranjang.
     */
    public function updateItem(UpdateCartItemRequest $request, $id)
    {
        $cart = $this->getCart($request);
        $item = $cart->items()->where('id', $id)->firstOrFail();
        $data = $request->validated();

        if ($item->type === 'purchase') {
            if (isset($data['quantity'])) {
                if ($item->product->stock_purchase < $data['quantity']) {
                    return response()->json(['message' => 'Stok pembelian tidak mencukupi.'], 422);
                }
                $item->quantity = $data['quantity'];
                $item->save();
            }
        } elseif ($item->type === 'rental') {
            $newStartDate = $data['rental_start_date'] ?? $item->rental_start_date->format('Y-m-d');
            $newEndDate = $data['rental_end_date'] ?? $item->rental_end_date->format('Y-m-d');
            $newQuantity = $data['quantity'] ?? $item->quantity;

            $start = Carbon::parse($newStartDate)->startOfDay();
            $end = Carbon::parse($newEndDate)->startOfDay();
            $days = $start->diffInDays($end) + 1;

            if ($item->product->min_rental_days && $days < $item->product->min_rental_days) {
                return response()->json(['message' => "Durasi sewa minimal adalah {$item->product->min_rental_days} hari."], 422);
            }
            if ($item->product->max_rental_days && $days > $item->product->max_rental_days) {
                return response()->json(['message' => "Durasi sewa maksimal adalah {$item->product->max_rental_days} hari."], 422);
            }

            try {
                DB::transaction(function () use ($item, $newStartDate, $newEndDate, $newQuantity) {
                    $availability = $this->rentalService->checkAvailability(
                        $item->product_id,
                        $newStartDate,
                        $newEndDate,
                        true // Lock for update
                    );

                    if ($availability['available_units_count'] < $newQuantity) {
                        throw new \Exception('Unit tidak mencukupi untuk periode yang dipilih.');
                    }

                    $pricing = $this->rentalService->calculateRentalPrice(
                        $item->product, 
                        $newStartDate, 
                        $newEndDate
                    );

                    $item->update([
                        'rental_start_date' => $newStartDate,
                        'rental_end_date' => $newEndDate,
                        'quantity' => $newQuantity,
                        'rental_pricing_tier' => $pricing['tier_used']
                    ]);
                });
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        }

        return response()->json(['message' => 'Item keranjang berhasil diupdate.'], 200);
    }

    /**
     * Hapus item dari keranjang.
     */
    public function removeItem(Request $request, $id)
    {
        $cart = $this->getCart($request);
        $item = $cart->items()->where('id', $id)->firstOrFail();
        $item->delete();

        return response()->json(['message' => 'Item berhasil dihapus dari keranjang.'], 200);
    }
}
