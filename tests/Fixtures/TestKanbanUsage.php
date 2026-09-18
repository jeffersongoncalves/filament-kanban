<?php

namespace JeffersonGoncalves\Filament\Kanban\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use JeffersonGoncalves\Filament\Kanban\Concerns\HasBatchedOrdering;
use JeffersonGoncalves\Filament\Kanban\Concerns\HasPerColumnPagination;
use JeffersonGoncalves\Filament\Kanban\Concerns\HasStatusTransitions;
use Livewire\Component;

// Exercises the three concerns directly, without going through
// Filament\Pages\Page / panel routing — that lifecycle is a separate concern
// from whether the query/ordering/transition logic itself is correct.
class TestKanbanUsage extends Component
{
    use HasBatchedOrdering;
    use HasPerColumnPagination;
    use HasStatusTransitions;

    protected static string $model = TestTask::class;

    protected static string $recordStatusAttribute = 'status';

    protected static string $orderColumn = 'order_column';

    protected int $perPage = 2;

    protected function getEloquentQuery(): Builder
    {
        return TestTask::query();
    }

    public function exposedRecordsForStatus(string $statusId, ?int $perPage = null): Collection
    {
        if ($perPage !== null) {
            $this->perPage = $perPage;
        }

        return $this->recordsForStatus($statusId);
    }

    public function exposedHasMoreRecords(string $statusId): bool
    {
        return $this->hasMoreRecords($statusId);
    }

    public function exposedPersistOrder(array $orderedIds): void
    {
        $this->persistOrder($orderedIds, static::$orderColumn);
    }

    public function exposedCanTransition(mixed $from, mixed $to): bool
    {
        return $this->canTransition($from, $to);
    }

    public function exposedRejectMove(int|string $recordId, mixed $from, mixed $to): void
    {
        $this->rejectMove($recordId, $from, $to);
    }

    public function render(): string
    {
        return '<div></div>';
    }
}
