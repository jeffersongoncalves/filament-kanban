<?php

namespace JeffersonGoncalves\Filament\Kanban\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\Filament\Kanban\KanbanServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            KanbanServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('status');
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();
        });
    }
}
