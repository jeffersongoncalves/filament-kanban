<?php

namespace JeffersonGoncalves\Filament\Kanban\Pages;

use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use JeffersonGoncalves\Filament\Kanban\Concerns\HasBatchedOrdering;
use JeffersonGoncalves\Filament\Kanban\Concerns\HasPerColumnPagination;
use JeffersonGoncalves\Filament\Kanban\Concerns\HasStatusTransitions;
use Livewire\Attributes\On;

class KanbanBoard extends Page
{
    use HasBatchedOrdering;
    use HasPerColumnPagination;
    use HasStatusTransitions;

    // Filament v3: $view is static (v4/v5 changed it to a plain instance property)
    protected static string $view = 'filament-kanban::pages.kanban-board';

    protected static string $model;

    protected static string $statusEnum;

    protected static string $recordTitleAttribute = 'title';

    protected static string $recordStatusAttribute = 'status';

    protected static string $orderColumn = 'order_column';

    protected int $perPage = 50;

    protected function statuses(): Collection
    {
        return static::$statusEnum::statuses();
    }

    protected function getEloquentQuery(): Builder
    {
        return static::$model::query();
    }

    protected function getViewData(): array
    {
        return [
            'statuses' => $this->statuses()->map(fn (array $status) => [
                ...$status,
                'records' => $this->recordsForStatus($status['id']),
                'hasMore' => $this->hasMoreRecords($status['id']),
            ]),
        ];
    }

    // ADR-0001, decision 2: click only ever sends the record key — the
    // full record is fetched server-side, never serialized into markup.
    public function recordClicked(int|string $recordId): void
    {
        $record = $this->getEloquentQuery()->findOrFail($recordId);

        $this->dispatch('kanban-record-selected', recordId: $record->getKey());
    }

    #[On('status-changed')]
    public function onStatusChanged(int|string $recordId, string $toStatus, array $toOrderedIds): void
    {
        $record = $this->getEloquentQuery()->find($recordId);

        if (! $record) {
            return;
        }

        $fromStatus = $record->{static::$recordStatusAttribute};
        $targetStatus = method_exists(static::$statusEnum, 'from')
            ? static::$statusEnum::from($toStatus)
            : $toStatus;

        if (! $this->canTransition($fromStatus, $targetStatus)) {
            $this->rejectMove($recordId, $fromStatus, $targetStatus);

            return;
        }

        $record->update([static::$recordStatusAttribute => $toStatus]);

        $this->persistOrder($toOrderedIds, static::$orderColumn);
    }

    #[On('sort-changed')]
    public function onSortChanged(array $orderedIds): void
    {
        $this->persistOrder($orderedIds, static::$orderColumn);
    }
}
