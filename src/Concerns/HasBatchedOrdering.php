<?php

namespace JeffersonGoncalves\Filament\Kanban\Concerns;

/**
 * ADR-0001, decision 5: persist a reorder as a single batched write instead
 * of one UPDATE per record moved past.
 */
trait HasBatchedOrdering
{
    /**
     * A single UPDATE ... CASE WHEN, not Eloquent's upsert(). upsert() always
     * attempts an INSERT first (falling back to UPDATE on conflict), and that
     * INSERT is validated against every NOT NULL column on the table — not
     * just the two we're touching — so it breaks the moment the model has
     * any other required column, on every driver, even for rows that already
     * exist. A plain UPDATE never attempts to insert, so it doesn't hit that.
     *
     * Identifiers are quoted via the connection's own grammar (backticks on
     * MySQL, double quotes on Postgres/SQLite), and every id/position is a
     * bound parameter — this is standard CASE WHEN SQL, not driver-specific.
     */
    protected function persistOrder(array $orderedIds, string $orderColumn = 'order_column'): void
    {
        $orderedIds = array_values($orderedIds);

        if ($orderedIds === []) {
            return;
        }

        $model = new (static::$model);
        $connection = $model->getConnection();
        $grammar = $connection->getQueryGrammar();

        $wrappedTable = $grammar->wrapTable($model->getTable());
        $wrappedKey = $grammar->wrap($model->getKeyName());
        $wrappedColumn = $grammar->wrap($orderColumn);

        $bindings = [];
        $whenClauses = [];

        foreach ($orderedIds as $index => $id) {
            $whenClauses[] = 'when ? then ?';
            $bindings[] = $id;
            $bindings[] = $index + 1;
        }

        $bindings = [...$bindings, ...$orderedIds];
        $placeholders = implode(',', array_fill(0, count($orderedIds), '?'));

        $sql = "update {$wrappedTable} set {$wrappedColumn} = case {$wrappedKey} "
            .implode(' ', $whenClauses)
            ." end where {$wrappedKey} in ({$placeholders})";

        $connection->update($sql, $bindings);
    }
}
