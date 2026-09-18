<?php

namespace JeffersonGoncalves\Filament\Kanban\Concerns;

use Illuminate\Support\Collection;

/**
 * ADR-0001, decision 1: bound each column's query instead of loading every
 * record for the whole board on every render.
 */
trait HasPerColumnPagination
{
    public array $columnPage = [];

    protected function perPage(): int
    {
        return $this->perPage ?? 50;
    }

    protected function pageFor(int|string $statusId): int
    {
        return $this->columnPage[$statusId] ?? 1;
    }

    protected function recordsForStatus(int|string $statusId): Collection
    {
        $query = $this->getEloquentQuery()->where(static::$recordStatusAttribute, $statusId);

        // persistOrder() (HasBatchedOrdering) always writes static::$orderColumn,
        // so the read side must always order by it too — otherwise the DB can
        // return rows in a different order than what was just dragged, and the
        // board visibly jumps after every successful move. Prefer the host
        // model's own scopeOrdered() when it exists (e.g. a custom order
        // column via spatie/eloquent-sortable), else fall back to ours.
        if (method_exists(static::$model, 'scopeOrdered')) {
            $query->ordered();
        } else {
            $query->orderBy(static::$orderColumn);
        }

        return $query->limit($this->perPage() * $this->pageFor($statusId))->get();
    }

    protected function hasMoreRecords(int|string $statusId): bool
    {
        $loaded = $this->perPage() * $this->pageFor($statusId);

        // ponytail: one extra COUNT per column; switch to a cached/estimated
        // count if boards start carrying many statuses.
        return $this->getEloquentQuery()
            ->where(static::$recordStatusAttribute, $statusId)
            ->count() > $loaded;
    }

    public function loadMoreRecords(int|string $statusId): void
    {
        $this->columnPage[$statusId] = $this->pageFor($statusId) + 1;
    }
}
