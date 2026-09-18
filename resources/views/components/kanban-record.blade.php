@props(['record'])

{{-- ADR-0001, decisions 2, 3 & 7: id-only payload (no json_encode'd record in
     markup), stable wire:key, and a prefixed data-record-id instead of a raw
     PK used as a DOM/CSS-selector id (works for int, UUID, and ULID keys). --}}
<div
    wire:key="kanban-record-{{ $record->getKey() }}"
    data-record-id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}')"
    class="record bg-white dark:bg-gray-700 rounded-lg px-4 py-2 cursor-grab font-medium text-gray-600 dark:text-gray-200"
>
    {{ $record->{static::$recordTitleAttribute} }}
</div>
