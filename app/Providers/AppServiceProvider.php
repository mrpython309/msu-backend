<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // Add this line
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        
        \App\Models\MsuStockMovement::observe(\App\Observers\MsuStockMovementObserver::class);

        \Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());

        // Local/dev-only slow query signal for lightweight diagnostics.
        if (app()->environment(['local', 'development'])) {
            $thresholdMs = (float) env('DB_SLOW_QUERY_THRESHOLD_MS', 500);
            DB::listen(static function (QueryExecuted $query) use ($thresholdMs): void {
                if ((float) $query->time < $thresholdMs) {
                    return;
                }

                Log::debug('Slow query detected', [
                    'sql' => $query->sql,
                    'time_ms' => (float) $query->time,
                    'bindings_count' => count($query->bindings),
                ]);
            });
        }
    }
}
