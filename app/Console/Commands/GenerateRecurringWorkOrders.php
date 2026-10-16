<?php

namespace App\Console\Commands;

use App\Services\Procurement\MsuWorkOrderRecurrenceService;
use Illuminate\Console\Command;

class GenerateRecurringWorkOrders extends Command
{
    protected $signature = 'work-orders:generate-recurring';

    protected $description = 'Create work orders that are due according to their frequency';

    public function handle(MsuWorkOrderRecurrenceService $recurrence): int
    {
        $created = $recurrence->generateAllDue();
        $this->info("Created {$created} recurring work order(s).");

        return self::SUCCESS;
    }
}
