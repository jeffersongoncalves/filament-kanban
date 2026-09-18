<?php

use JeffersonGoncalves\Filament\Kanban\Tests\Fixtures\TestKanbanUsage;
use JeffersonGoncalves\Filament\Kanban\Tests\Fixtures\TestTask;
use Livewire\Livewire;

it('bounds a column to perPage instead of loading every record', function () {
    foreach (range(1, 5) as $i) {
        TestTask::create(['title' => "Task $i", 'status' => 'backlog', 'order_column' => $i]);
    }

    $component = Livewire::test(TestKanbanUsage::class);

    expect($component->instance()->exposedRecordsForStatus('backlog'))->toHaveCount(2)
        ->and($component->instance()->exposedHasMoreRecords('backlog'))->toBeTrue();
});

it('reflects order_column on read, matching what persistOrder writes', function () {
    TestTask::create(['title' => 'C', 'status' => 'backlog', 'order_column' => 3]);
    TestTask::create(['title' => 'A', 'status' => 'backlog', 'order_column' => 1]);
    TestTask::create(['title' => 'B', 'status' => 'backlog', 'order_column' => 2]);

    $component = Livewire::test(TestKanbanUsage::class);
    $records = $component->instance()->exposedRecordsForStatus('backlog', perPage: 10);

    expect($records->pluck('title')->all())->toBe(['A', 'B', 'C']);
});
