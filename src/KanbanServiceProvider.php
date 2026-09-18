<?php

namespace JeffersonGoncalves\Filament\Kanban;

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
}
