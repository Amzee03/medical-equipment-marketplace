<?php

namespace App\Services;

use App\Models\Product;
use App\Models\EquipmentUnit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RentalAvailabilityService
{
    /**
     * Membuat query untuk mengambil unit sewa yang tersedia.
     */
    public function getAvailableUnitsQuery(int $productId, string $startDate, string $endDate, bool $lock = false): \Illuminate\Database\Eloquent\Builder
    {
        $query = EquipmentUnit::where('product_id', $productId)
            ->where('status', '!=', 'retired')
            ->whereDoesntHave('rentalItems', function ($q) use ($startDate, $endDate) {
                $q->whereIn('rental_status', ['confirmed', 'active', 'awaiting_return', 'overdue'])
                  ->whereDate('start_date', '<=', $endDate)
                  ->whereDate('end_date', '>=', $startDate)
                  ->whereHas('orderItem.order', function ($q2) {
                      $q2->where('status', '!=', 'cancelled');
                  });
            });
        
        if ($lock) {
            $query->lockForUpdate();
        }
        
        return $query;
    }

    /**
     * Mengecek ketersediaan unit sewa untuk produk dan periode tertentu.
     *
     * @param int $productId
     * @param string $startDate (Y-m-d)
     * @param string $endDate (Y-m-d)
     * @param bool $lock Gunakan row-level locking (lockForUpdate)
     * @return array ['available' => bool, 'available_units_count' => int]
     */
    public function checkAvailability(int $productId, string $startDate, string $endDate, bool $lock = false): array
    {
        $query = $this->getAvailableUnitsQuery($productId, $startDate, $endDate, $lock);

        $availableUnitsCount = $query->count();

        return [
            'available' => $availableUnitsCount > 0,
            'available_units_count' => $availableUnitsCount,
        ];
    }

    /**
     * Menghitung harga sewa termurah berdasarkan durasi.
     * Mengembalikan breakdown hari, tier yang digunakan, dan total harga.
     *
     * @param Product $product
     * @param string $startDate (Y-m-d)
     * @param string $endDate (Y-m-d)
     * @return array ['days' => int, 'tier_used' => string, 'total_price' => float]
     */
    public function calculateRentalPrice(Product $product, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        // Inclusive count: jika start dan end sama, dihitung 1 hari.
        $days = $start->diffInDays($end) + 1;

        $dailyPrice = (float) $product->rental_price_daily;
        
        // Pendekatan paling sederhana: 
        // 1. Hitung harga baseline (harga harian * jumlah hari)
        // 2. Jika produk punya harga mingguan dan durasi >= 7 hari, hitung harga mingguan.
        // 3. Jika produk punya harga bulanan dan durasi >= 30 hari, hitung harga bulanan.
        // Pilih yang paling murah.

        $totalPrice = $dailyPrice * $days;
        $tierUsed = 'daily';

        if ($product->rental_price_weekly && $days >= 7) {
            $weeks = ceil($days / 7);
            $weeklyTotal = ((float) $product->rental_price_weekly) * $weeks;
            if ($weeklyTotal < $totalPrice) {
                $totalPrice = $weeklyTotal;
                $tierUsed = 'weekly';
            }
        }

        if ($product->rental_price_monthly && $days >= 30) {
            $months = ceil($days / 30);
            $monthlyTotal = ((float) $product->rental_price_monthly) * $months;
            if ($monthlyTotal < $totalPrice) {
                $totalPrice = $monthlyTotal;
                $tierUsed = 'monthly';
            }
        }

        return [
            'days' => (int) $days,
            'tier_used' => $tierUsed,
            'total_price' => $totalPrice,
        ];
    }
}
