<?php

namespace JeffersonGoncalves\Filament\Kanban;

use Filament\Contracts\Plugin;
use Filament\Panel;

// Registering this on a panel is optional. Boards are added by extending
// Pages\KanbanBoard and registering the subclass directly via ->pages([]) —
// there's nothing panel-wide to configure here yet.
class KanbanPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-kanban';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): Plugin
    {
        return filament(app(static::class)->getId());
    }
}
