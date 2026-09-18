@props(['status'])

{{-- ADR-0001, decision 3: wire:key gives the column stable identity so Livewire
     morphs it instead of tearing down/rebuilding the subtree (and the Sortable
     instance attached to it) on every render. --}}
<div wire:key="kanban-column-{{ $status['id'] }}" class="md:w-[24rem] flex-shrink-0 mb-5 md:min-h-full flex flex-col">
    <div class="flex items-center justify-between px-1 pb-2 font-semibold text-gray-700 dark:text-gray-200">
        <span>{{ $status['label'] }}</span>
    </div>

    <div
        data-status-id="{{ $status['id'] }}"
        class="flex flex-col flex-1 gap-2 p-3 bg-gray-200 dark:bg-gray-800 rounded-xl"
    >
        @foreach ($status['records'] as $record)
            @include('filament-kanban::components.kanban-record', ['record' => $record])
        @endforeach
    </div>

    @if ($status['hasMore'])
        <button
            type="button"
            wire:click="loadMoreRecords('{{ $status['id'] }}')"
            class="mt-2 text-sm text-primary-600 hover:underline"
        >
            {{ __('filament-kanban::kanban.load_more') }}
        </button>
    @endif
</div>
