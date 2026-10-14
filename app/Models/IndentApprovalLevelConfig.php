<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IndentApprovalLevelConfig extends Model
{
    protected $table = 'indent_approval_levels';

    protected $fillable = [
        'level',
        'code',
        'name',
        'is_active',
        'sort_order',
        'approver_role_names',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
        'sort_order' => 'integer',
        'approver_role_names' => 'array',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('level');
    }

    public static function activeLevelCount(): int
    {
        return static::query()->active()->count();
    }

    /**
     * @return list<int>
     */
    public static function activeLevels(): array
    {
        return static::query()
            ->active()
            ->ordered()
            ->pluck('level')
            ->map(fn ($level) => (int) $level)
            ->values()
            ->all();
    }
}
