@props(['status'])

{{-- ADR-0001, decision 3: wire:key gives the column stable identity so Livewire
     morphs it instead of tearing down/rebuilding the subtree (and the Sortable
     instance attached to it) on every render. --}}
<div wire:key="kanban-column-{{ $status['id'] }}" class="fi-kanban-column">
    <div class="fi-kanban-column-header">
        <span>{{ $status['label'] }}</span>
    </div>

    <div
        data-status-id="{{ $status['id'] }}"
        class="fi-kanban-column-body"
    >
        @foreach ($status['records'] as $record)
            @include('filament-kanban::components.kanban-record', ['record' => $record])
        @endforeach
    </div>

    @if ($status['hasMore'])
        <button
            type="button"
            wire:click="loadMoreRecords('{{ $status['id'] }}')"
            class="fi-kanban-load-more"
        >
            {{ __('filament-kanban::kanban.load_more') }}
        </button>
    @endif
</div>
