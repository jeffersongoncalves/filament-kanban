<?php

namespace JeffersonGoncalves\Filament\Kanban\Concerns;

/**
 * ADR-0001, decision 5: persist a reorder as a single batched write instead
 * of one UPDATE per record moved past.
 */
trait HasBatchedOrdering
{
    protected function persistOrder(array $orderedIds, string $orderColumn = 'order_column'): void
    {
        if ($orderedIds === []) {
            return;
        }

        $model = new (static::$model);
        $keyName = $model->getKeyName();

        $rows = collect($orderedIds)
            ->values()
            ->map(fn ($id, $index) => [
                $keyName => $id,
                $orderColumn => $index + 1,
            ])
            ->all();

        // Deliberately Eloquent's upsert(), not a hand-rolled CASE WHEN string:
        // the query builder's grammar already quotes/prefixes identifiers and
        // binds every id as a parameter per driver (MySQL ON DUPLICATE KEY,
        // Postgres/SQLite ON CONFLICT). Don't replace this with raw SQL.
        $model::query()->upsert($rows, [$keyName], [$orderColumn]);
    }
}
