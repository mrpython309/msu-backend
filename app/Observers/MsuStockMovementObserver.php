<?php

namespace App\Observers;

use App\Models\MsuStockMovement;

class MsuStockMovementObserver
{
    /**
     * Handle the MsuStockMovement "created" event.
     */
    public function created(MsuStockMovement $msuStockMovement): void
    {
        $this->syncDailyStock($msuStockMovement);
    }

    /**
     * Handle the MsuStockMovement "updated" event.
     */
    public function updated(MsuStockMovement $msuStockMovement): void
    {
        // Currently, we just recalculate the whole day from movements
        // if a movement is updated.
        $this->syncDailyStock($msuStockMovement);
    }

    /**
     * Handle the MsuStockMovement "deleted" event.
     */
    public function deleted(MsuStockMovement $msuStockMovement): void
    {
        $this->syncDailyStock($msuStockMovement);
    }

    /**
     * Handle the MsuStockMovement "restored" event.
     */
    public function restored(MsuStockMovement $msuStockMovement): void
    {
        $this->syncDailyStock($msuStockMovement);
    }

    /**
     * Handle the MsuStockMovement "force deleted" event.
     */
    public function forceDeleted(MsuStockMovement $msuStockMovement): void
    {
        $this->syncDailyStock($msuStockMovement);
    }

    /**
     * Synchronize the daily stock based on the movement's date.
     */
    protected function syncDailyStock(MsuStockMovement $movement): void
    {
        $date = $movement->created_at ? $movement->created_at->toDateString() : now()->toDateString();
        $itemId = $movement->msu_item_master_id;

        // Get the previous day's closing stock if the record for today doesn't exist yet
        $dailyStock = \App\Models\MsuDailyItemStock::firstOrNew([
            'msu_item_master_id' => $itemId,
            'date' => $date,
        ]);

        if (!$dailyStock->exists) {
            $previousStock = \App\Models\MsuDailyItemStock::where('msu_item_master_id', $itemId)
                ->where('date', '<', $date)
                ->orderBy('date', 'desc')
                ->first();

            $dailyStock->opening_stock = $previousStock ? $previousStock->closing_stock : 0;
        }

        // Recalculate 'in' and 'out' for this specific date
        $in = \App\Models\MsuStockMovement::where('msu_item_master_id', $itemId)
            ->whereDate('created_at', $date)
            ->where('transaction_type', 'in')
            ->sum('quantity');

        $out = \App\Models\MsuStockMovement::where('msu_item_master_id', $itemId)
            ->whereDate('created_at', $date)
            ->where('transaction_type', 'out')
            ->sum('quantity');

        $dailyStock->stock_in = $in;
        $dailyStock->stock_out = $out;
        $dailyStock->closing_stock = $dailyStock->opening_stock + $in - $out;
        $dailyStock->save();
        
        // Also we should propagate the change to future dates if any exist
        $futureStocks = \App\Models\MsuDailyItemStock::where('msu_item_master_id', $itemId)
            ->where('date', '>', $date)
            ->orderBy('date', 'asc')
            ->get();
            
        $previousClosing = $dailyStock->closing_stock;
        foreach ($futureStocks as $futureStock) {
            $futureStock->opening_stock = $previousClosing;
            $futureStock->closing_stock = $futureStock->opening_stock + $futureStock->stock_in - $futureStock->stock_out;
            $futureStock->save();
            $previousClosing = $futureStock->closing_stock;
        }
    }
}
