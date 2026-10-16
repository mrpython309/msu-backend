<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CarryForwardDailyStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:carry-forward-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carry forward the closing stock of yesterday as the opening stock for today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $this->info("Carrying forward stock from $yesterday to $today...");

        // Get all items that are active
        $items = \App\Models\MsuItemMaster::active()->get();

        foreach ($items as $item) {
            // Find the most recent stock record (could be yesterday, or even earlier if no transactions happened recently)
            $previousStock = \App\Models\MsuDailyItemStock::where('msu_item_master_id', $item->id)
                ->where('date', '<', $today)
                ->orderBy('date', 'desc')
                ->first();

            $openingStock = $previousStock ? $previousStock->closing_stock : 0;

            // Create or update today's record
            \App\Models\MsuDailyItemStock::firstOrCreate(
                [
                    'msu_item_master_id' => $item->id,
                    'date' => $today,
                ],
                [
                    'opening_stock' => $openingStock,
                    'stock_in' => 0,
                    'stock_out' => 0,
                    'closing_stock' => $openingStock,
                ]
            );
        }

        $this->info('Daily stock carried forward successfully.');
    }
}
