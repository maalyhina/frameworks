<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ListQueryService
{
    private const DEFAULT_ITEMS_PER_PAGE = 10;
    private const MAX_ITEMS_PER_PAGE = 100;

    /**
     * @param array<string, array{0: string, 1: string}> $filters  параметр запиту => [колонка, тип: like|eq|date]
     */
    public function paginate(Builder $query, Request $request, array $filters): array
    {
        foreach ($filters as $param => [$column, $type]) {
            $value = $request->query($param);

            if ($value === null || $value === '') {
                continue;
            }

            match ($type) {
                'like' => $query->where($column, 'like', '%' . $value . '%'),
                'date' => $query->whereDate($column, $value),
                default => $query->where($column, $value),
            };
        }

        $perPage = (int) $request->query('itemsPerPage', self::DEFAULT_ITEMS_PER_PAGE);
        $perPage = min(self::MAX_ITEMS_PER_PAGE, max(1, $perPage));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->orderBy('id')->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'itemsPerPage' => $paginator->perPage(),
                'totalItems' => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
            ],
        ];
    }
}
