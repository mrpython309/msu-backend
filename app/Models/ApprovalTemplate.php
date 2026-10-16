<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalTemplate extends Model
{
    public const MODULE_INDENT = null;

    public const MODULE_PO = 'po';

    protected $fillable = [
        'name',
        'category_id',
        'module',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalTemplateStep::class)->orderBy('sequence');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Indent templates only (module is null). */
    public function scopeForIndentModule(Builder $query): Builder
    {
        return $query->whereNull('module');
    }

    /** Purchase Order templates only (module = po). */
    public function scopeForPoModule(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_PO);
    }

    public function isPurchaseOrderTemplate(): bool
    {
        return $this->module === self::MODULE_PO;
    }

    /**
     * Active PO template for a category, falling back to the default PO template
     * (module = po, category_id = null). Completely separate from indent templates.
     *
     * Tries the given category first (e.g. subcategory "Others"), then its parent,
     * then the default PO template — so leaf-category L1/L2 configs are not skipped.
     */
    public static function forPurchaseOrder(?int $categoryId): ?self
    {
        return static::resolveByCategory($categoryId, static::query()->forPoModule());
    }

    /**
     * Active indent template for a category, falling back to the default indent template.
     * Never returns a PO template.
     */
    public static function forCategory(?int $categoryId): ?self
    {
        return static::resolveByCategory($categoryId, static::query()->forIndentModule());
    }

    /**
     * Shared resolution: category-specific first (incl. parent category), then default.
     */
    private static function resolveByCategory(?int $categoryId, Builder $base): ?self
    {
        $withSteps = fn ($q) => $q->orderBy('sequence');

        $default = (clone $base)
            ->active()
            ->whereNull('category_id')
            ->with(['steps' => $withSteps])
            ->first();

        foreach (self::categoryIdsToTry($categoryId) as $id) {
            $categoryTemplate = (clone $base)
                ->active()
                ->where('category_id', $id)
                ->with(['steps' => $withSteps])
                ->first();

            if ($categoryTemplate?->isFullyConfigured()) {
                return $categoryTemplate;
            }
        }

        if ($default?->isFullyConfigured()) {
            return $default;
        }

        // Return the best available template so validation can report what is missing.
        foreach (self::categoryIdsToTry($categoryId) as $id) {
            $categoryTemplate = (clone $base)
                ->active()
                ->where('category_id', $id)
                ->with(['steps' => $withSteps])
                ->first();

            if ($categoryTemplate?->steps->isNotEmpty()) {
                return $categoryTemplate;
            }
        }

        return $default;
    }

    /**
     * @return list<int>
     */
    private static function categoryIdsToTry(?int $categoryId): array
    {
        if (! $categoryId) {
            return [];
        }

        $ids = [(int) $categoryId];
        $parentId = AssetCategory::query()->whereKey($categoryId)->value('parent_id');
        if ($parentId) {
            $ids[] = (int) $parentId;
        }

        return array_values(array_unique($ids));
    }

    public function isFullyConfigured(): bool
    {
        $steps = $this->relationLoaded('steps')
            ? $this->steps
            : $this->steps()->orderBy('sequence')->get();

        if ($steps->isEmpty()) {
            return false;
        }

        return $steps->every(
            fn (ApprovalTemplateStep $step) => $step->hasAssigneeConfigured()
        );
    }
}
