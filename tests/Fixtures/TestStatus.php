<?php

namespace JeffersonGoncalves\Filament\Kanban\Tests\Fixtures;

// Mirrors the shape of a real consumer's status enum (e.g. a help-desk
// ticket status) closely enough to exercise HasStatusTransitions: Done is
// terminal, so Done -> Backlog must be rejected.
enum TestStatus: string
{
    case Backlog = 'backlog';
    case InProgress = 'in_progress';
    case Done = 'done';

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::Backlog => in_array($status, [self::InProgress, self::Done], true),
            self::InProgress => in_array($status, [self::Backlog, self::Done], true),
            self::Done => false,
        };
    }
}
