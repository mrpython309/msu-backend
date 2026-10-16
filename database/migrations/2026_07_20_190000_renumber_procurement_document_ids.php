<?php

use App\Models\Asset;
use App\Models\Grn;
use App\Models\Indent;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Rfq;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $this->renumber(Indent::class, 'indent_no', 'IDR', true);
            $this->renumber(PurchaseRequisition::class, 'pr_no', 'PR');
            $this->renumber(Rfq::class, 'rfq_no', 'RFQ');
            $this->renumber(PurchaseOrder::class, 'po_no', 'PO');
            $this->renumber(Grn::class, 'grn_no', 'GRN', Schema::hasColumn('grns', 'deleted_at'));

            if (Schema::hasColumn('purchase_requisitions', 'fan_no')) {
                $this->renumber(PurchaseRequisition::class, 'fan_no', 'FAN');
            }

            $this->renumberAssets();
        });
    }

    public function down(): void
    {
        // Irreversible remapping of document numbers.
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    private function renumber(string $modelClass, string $column, string $prefix, bool $withTrashed = false): void
    {
        $query = $withTrashed && method_exists($modelClass, 'withTrashed')
            ? $modelClass::withTrashed()->orderBy('id')
            : $modelClass::query()->orderBy('id');

        $seq = 1;
        foreach ($query->cursor() as $row) {
            $newCode = $prefix.'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            if ((string) $row->{$column} !== $newCode) {
                $row->forceFill([$column => $newCode])->saveQuietly();
            }

            $seq++;
        }
    }

    private function renumberAssets(): void
    {
        $seq = 1;

        Asset::query()->orderBy('id')->cursor()->each(function (Asset $asset) use (&$seq) {
            $newCode = 'AST-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            $asset->forceFill([
                'asset_code' => $newCode,
                'asset_prefix' => 'AST',
                'asset_nos' => $seq,
            ])->saveQuietly();

            $seq++;
        });
    }
};
