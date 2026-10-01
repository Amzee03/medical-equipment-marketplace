<?php

namespace App\Console\Commands;

use App\Models\RentalItem;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateOverdueRentals extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rentals:update-overdue';

    /**
     * The console command description.
     */
    protected $description = 'Menandai rental_items yang melewati rental_due_date sebagai overdue. Dijalankan setiap hari via scheduler.';

    /**
     * Execute the console command.
     * 
     * Hanya mengubah rental_status menjadi overdue.
     * late_fee TIDAK dihitung di sini -- late_fee baru final dihitung saat 
     * actual return terjadi di RentalReturnController.
     */
    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $updated = RentalItem::whereIn('rental_status', ['confirmed', 'active', 'awaiting_return'])
            ->where('rental_due_date', '<', $today)
            ->whereNull('actual_return_date')
            ->update(['rental_status' => 'overdue']);

        $this->info("Berhasil menandai {$updated} rental item(s) sebagai overdue.");

        return Command::SUCCESS;
    }
}
