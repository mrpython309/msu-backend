<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MasterListing
{
    public static function perPage(Request $request, int $default = 10): int
    {
        $perPage = $request->input('per_page', $default);

        if ($perPage === 'all') {
            return 1000000;
        }

        $perPage = (int) $perPage;

        return in_array($perPage, [10, 25, 50, 100, 1000000], true) ? $perPage : $default;
    }

    public static function applySearch(Builder $query, Request $request, array $columns): void
    {
        if (! $request->filled('search')) {
            return;
        }

        $search = $request->string('search')->toString();

        $query->where(function (Builder $q) use ($search, $columns) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function (Builder $relationQuery) use ($field, $search) {
                        $relationQuery->where($field, 'like', '%'.$search.'%');
                    });
                } else {
                    $q->orWhere($column, 'like', '%'.$search.'%');
                }
            }
        });
    }

    public static function applyStatus(Builder $query, Request $request, string $column = 'status'): void
    {
        if ($request->filled('status')) {
            $query->where($column, $request->status);
        }
    }
}
