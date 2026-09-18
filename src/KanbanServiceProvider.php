<?php

namespace JeffersonGoncalves\Filament\Kanban;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class KanbanServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-kanban';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews();
    }

    public function packageBooted(): void
    {
        // SortableJS itself is already global (filament/support bundles it as
        // window.Sortable) — we only own the small stylesheet for the
        // grabbing cursor and the reject-move pulse animation.
        FilamentAsset::register([
            Css::make('filament-kanban-styles', __DIR__.'/../resources/css/kanban.css'),
        ], static::$name);
    }
}
