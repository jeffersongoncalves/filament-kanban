<?php

namespace JeffersonGoncalves\Filament\Kanban\Concerns;

/**
 * ADR-0001, decision 6: validate status transitions server-side before
 * persisting a drag, instead of trusting whatever the client dropped.
 */
trait HasStatusTransitions
{
    protected function canTransition(mixed $fromStatus, mixed $toStatus): bool
    {
        if (! is_object($fromStatus) || ! method_exists($fromStatus, 'canTransitionTo')) {
            return true;
        }

        return $fromStatus->canTransitionTo($toStatus);
    }

    protected function rejectMove(int|string $recordId): void
    {
        $this->dispatch('kanban-move-rejected', recordId: $recordId);
    }
}
