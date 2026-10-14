<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflow extends Model
{
    public const MODULE_INDENT = 'Indent';

    protected $fillable = [
        'workflow_name',
        'module',
        'category_id',
        'sequence',
        'permission_name',
        'is_mandatory',
        'is_active',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    /**
     * Ordered, active steps configured for a category. Falls back to the
     * default workflow (category_id = null) when the category has none.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function stepsForCategory(?int $categoryId, string $module = self::MODULE_INDENT)
    {
        if ($categoryId) {
            $steps = static::query()
                ->forModule($module)
                ->active()
                ->where('category_id', $categoryId)
                ->orderBy('sequence')
                ->get();

            if ($steps->isNotEmpty()) {
                return $steps;
            }
        }

        return static::query()
            ->forModule($module)
            ->active()
            ->whereNull('category_id')
            ->orderBy('sequence')
            ->get();
    }
}
