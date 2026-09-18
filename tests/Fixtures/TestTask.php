<?php

namespace JeffersonGoncalves\Filament\Kanban\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TestTask extends Model
{
    protected $table = 'test_tasks';

    protected $fillable = [
        'title',
        'status',
        'order_column',
    ];

    protected $casts = [
        'status' => TestStatus::class,
    ];
}
