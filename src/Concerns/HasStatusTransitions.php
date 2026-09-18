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

    // Carries from/to status so a listener can show *why* the move was
    // rejected. No manual DOM snapback needed: the rejected action still
    // triggers a normal Livewire re-render, and since nothing was persisted,
    // the re-fetched, wire:keyed collection puts the card back in its real
    // column on its own.
    protected function rejectMove(int|string $recordId, mixed $fromStatus, mixed $toStatus): void
    {
        $this->dispatch(
            'kanban-move-rejected',
            recordId: $recordId,
            fromStatus: $fromStatus instanceof \BackedEnum ? $fromStatus->value : $fromStatus,
            toStatus: $toStatus instanceof \BackedEnum ? $toStatus->value : $toStatus,
        );
    }
}
