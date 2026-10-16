<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InitDailyStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:init-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize daily stock records based on current dynamic stock values (One-time run)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();
        $this->info("Initializing daily stock for today ($today)...");

        $items = \App\Models\MsuItemMaster::all();
        $count = 0;

        foreach ($items as $item) {
            // Get dynamically calculated current stock from all time
            $currentStock = $item->current_stock;
            
            \App\Models\MsuDailyItemStock::firstOrCreate(
                [
                    'msu_item_master_id' => $item->id,
                    'date' => $today,
                ],
                [
                    'opening_stock' => $currentStock,
                    'stock_in' => 0,
                    'stock_out' => 0,
                    'closing_stock' => $currentStock,
                ]
            );
            $count++;
        }

        $this->info("Successfully initialized stock for $count items.");
    }
}
