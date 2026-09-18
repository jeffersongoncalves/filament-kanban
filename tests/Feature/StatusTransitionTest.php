<?php

use JeffersonGoncalves\Filament\Kanban\Tests\Fixtures\TestKanbanUsage;
use JeffersonGoncalves\Filament\Kanban\Tests\Fixtures\TestStatus;
use Livewire\Livewire;

it('allows a legal transition', function () {
    $component = Livewire::test(TestKanbanUsage::class);

    expect($component->instance()->exposedCanTransition(TestStatus::Backlog, TestStatus::InProgress))->toBeTrue();
});

it('rejects an illegal transition out of a terminal status', function () {
    $component = Livewire::test(TestKanbanUsage::class);

    expect($component->instance()->exposedCanTransition(TestStatus::Done, TestStatus::Backlog))->toBeFalse();
});

it('allows any move when the status has no canTransitionTo (plain string status)', function () {
    $component = Livewire::test(TestKanbanUsage::class);

    expect($component->instance()->exposedCanTransition('open', 'closed'))->toBeTrue();
});

it('dispatches kanban-move-rejected with from/to status on rejection', function () {
    Livewire::test(TestKanbanUsage::class)
        ->call('exposedRejectMove', 1, TestStatus::Done, TestStatus::Backlog)
        ->assertDispatched(
            'kanban-move-rejected',
            recordId: 1,
            fromStatus: 'done',
            toStatus: 'backlog',
        );
});
