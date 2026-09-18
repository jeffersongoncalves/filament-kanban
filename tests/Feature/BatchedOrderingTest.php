<?php

use Illuminate\Support\Facades\DB;
use JeffersonGoncalves\Filament\Kanban\Tests\Fixtures\TestKanbanUsage;
use JeffersonGoncalves\Filament\Kanban\Tests\Fixtures\TestTask;
use Livewire\Livewire;

it('persists a full column reorder in a single query', function () {
    $a = TestTask::create(['title' => 'A', 'status' => 'backlog', 'order_column' => 0]);
    $b = TestTask::create(['title' => 'B', 'status' => 'backlog', 'order_column' => 0]);
    $c = TestTask::create(['title' => 'C', 'status' => 'backlog', 'order_column' => 0]);

    $component = Livewire::test(TestKanbanUsage::class);

    $writeQueries = 0;
    DB::listen(function ($query) use (&$writeQueries) {
        if (str_starts_with(strtolower($query->sql), 'update')) {
            $writeQueries++;
        }
    });

    $component->instance()->exposedPersistOrder([$c->id, $a->id, $b->id]);

    expect($writeQueries)->toBe(1)
        ->and($c->refresh()->order_column)->toBe(1)
        ->and($a->refresh()->order_column)->toBe(2)
        ->and($b->refresh()->order_column)->toBe(3);
});

it('does nothing for an empty order', function () {
    $component = Livewire::test(TestKanbanUsage::class);

    $component->instance()->exposedPersistOrder([]);
})->throwsNoExceptions();
