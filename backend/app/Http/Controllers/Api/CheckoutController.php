<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RentalAgreement;
use App\Models\RentalItem;
use App\Services\RentalAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    protected RentalAvailabilityService $rentalService;

    public function __construct(RentalAvailabilityService $rentalService)
    {
        $this->rentalService = $rentalService;
    }

    public function store(StoreCheckoutRequest $request)
    {
        $user = $request->user();
        
        $data = $request->validated();
        
        try {
            DB::beginTransaction();

            $cart = $user->cart()->with('items.product')->first();
            
            if (!$cart || $cart->items->isEmpty()) {
                throw new \Exception('Keranjang belanja kosong.');
            }

            $hasRental = $cart->items->where('type', 'rental')->isNotEmpty();
            if ($hasRental && empty($data['rental_agreement_accepted'])) {
                throw ValidationException::withMessages([
                    'rental_agreement_accepted' => ['Persetujuan sewa wajib dicentang.']
                ]);
            }

            $address = $user->addresses()->findOrFail($data['address_id']);
            
            $purchaseItems = $cart->items->where('type', 'purchase');
            $rentalItems = $cart->items->where('type', 'rental');
            
            $orders = [];
            $totalAmount = 0;
            
            // 1 Payment for all orders
            $payment = Payment::create([
                'invoice_number' => 'INV-' . time() . '-' . rand(1000, 9999),
                'user_id' => $user->id,
                'payment_gateway' => 'midtrans',
                'status' => 'pending',
                'amount' => 0, // temporary, will update later
            ]);

            // Handle Purchase Items
            if ($purchaseItems->isNotEmpty()) {
                $order = $this->createOrder($user, $payment, 'purchase', $address, $data['shipping_method']);
                $subtotal = 0;
                
                foreach ($purchaseItems as $item) {
                    // lockForUpdate to prevent race condition on purchase stock
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                    
                    if ($product->stock_purchase < $item->quantity) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi untuk pembelian.");
                    }
                    
                    $product->decrement('stock_purchase', $item->quantity);
                    
                    $itemSubtotal = $product->sale_price * $item->quantity;
                    $subtotal += $itemSubtotal;
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $product->sale_price,
                        'subtotal' => $itemSubtotal,
                    ]);
                }
                
                $order->subtotal = $subtotal;
                $order->total = $subtotal + $order->shipping_cost;
                $order->save();
                
                $totalAmount += $order->total;
                $orders[] = $order;
            }
            
            // Handle Rental Items
            if ($rentalItems->isNotEmpty()) {
                $order = $this->createOrder($user, $payment, 'rental', $address, $data['shipping_method']);
                $subtotal = 0;
                
                foreach ($rentalItems as $item) {
                    $product = $item->product;
                    
                    // Ambil unit yang tersedia melalui single source of truth
                    $availableUnits = $this->rentalService
                        ->getAvailableUnitsQuery($product->id, $item->rental_start_date->format('Y-m-d'), $item->rental_end_date->format('Y-m-d'), true)
                        ->take($item->quantity)
                        ->get();
                        
                    if ($availableUnits->count() < $item->quantity) {
                         throw new \Exception("Gagal mengalokasikan unit spesifik untuk {$product->name} (tidak mencukupi atau sudah dipesan orang lain).");
                    }
                    
                    $pricing = $this->rentalService->calculateRentalPrice($product, $item->rental_start_date->format('Y-m-d'), $item->rental_end_date->format('Y-m-d'));
                    $itemSubtotal = $pricing['total_price'] * $item->quantity;
                    $subtotal += $itemSubtotal;
                    
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $pricing['total_price'],
                        'subtotal' => $itemSubtotal,
                    ]);
                    
                    $rentalStatus = $user->ktp_status !== 'approved' ? 'awaiting_ktp_verification' : 'confirmed';
                    
                    foreach ($availableUnits as $unit) {
                        RentalItem::create([
                            'order_item_id' => $orderItem->id,
                            'equipment_unit_id' => $unit->id,
                            'start_date' => $item->rental_start_date->format('Y-m-d'),
                            'end_date' => $item->rental_end_date->format('Y-m-d'),
                            'rental_due_date' => $item->rental_end_date->format('Y-m-d'),
                            'rental_status' => $rentalStatus,
                        ]);
                    }
                }
                
                $order->subtotal = $subtotal;
                $order->total = $subtotal + $order->shipping_cost;
                $order->save();
                
                // Create rental agreement
                $agreementText = "Perjanjian Sewa (Rental Agreement)\n\n"
                    . "Nomor Pesanan: {$order->order_number}\n"
                    . "Pihak penyewa menyetujui syarat penyewaan barang selama periode yang ditentukan. "
                    . "Keterlambatan pengembalian dan kerusakan barang akan dikenakan denda sesuai dengan FR-31.";
                    
                RentalAgreement::create([
                    'order_id' => $order->id,
                    'agreed_at' => now(),
                    'agreement_version' => '1.0',
                    'snapshot_content' => $agreementText,
                ]);
                
                $totalAmount += $order->total;
                $orders[] = $order;
            }
            
            // Update total payment amount (rounded to integer as requested)
            $payment->update([
                'amount' => round($totalAmount)
            ]);
            
            // Empty the cart
            $cart->items()->delete();
            
            $snapInfo = $this->createMidtransTransaction($payment);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Checkout berhasil.',
                'payment' => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'snap_token' => $snapInfo['snap_token'],
                    'redirect_url' => $snapInfo['redirect_url'],
                ],
                'orders' => OrderResource::collection($orders),
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Helper to create an order with address snapshot.
     */
    private function createOrder($user, $payment, $orderType, $address, $shippingMethod)
    {
        return Order::create([
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'order_number' => 'ORD-' . strtoupper(substr($orderType, 0, 1)) . '-' . time() . '-' . rand(100, 999),
            'order_type' => $orderType,
            'status' => 'pending_payment',
            'subtotal' => 0, // calculated later
            'shipping_cost' => 0, // simple for now
            'total' => 0, // calculated later
            'shipping_method' => $shippingMethod,
            'address_id' => $address->id,
            'shipping_recipient_name' => $address->recipient_name,
            'shipping_phone' => $address->phone,
            'shipping_full_address' => $address->full_address,
            'shipping_city' => $address->city,
            'shipping_province' => $address->province,
            'shipping_postal_code' => $address->postal_code,
        ]);
    }

    /**
     * Stub for Midtrans.
     */
    private function createMidtransTransaction($payment)
    {
        // TODO Tahap 10: ganti dengan actual Midtrans API call
        return [
            'snap_token' => 'dummy_snap_token_' . $payment->id,
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/dummy_snap_token_' . $payment->id,
        ];
    }
}
