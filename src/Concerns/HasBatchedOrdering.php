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

        $model::query()->upsert($rows, [$keyName], [$orderColumn]);
    }
}
