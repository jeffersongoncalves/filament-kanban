<x-filament-panels::page>
    {{--
        ADR-0001, decision 4: the drag library is (re)bound after every
        Livewire commit for this component, not just once on page load —
        that's what keeps drag working after the first move instead of
        silently dying. Bound via the `commit` hook's succeed() callback
        (fires once per request, after the DOM is patched), not
        `morph.updated` (fires once per morphed *element*, which would
        destroy/recreate every column's Sortable instance N times per
        drag for no reason).
    --}}
    <div
        x-data
        wire:ignore.self
        class="fi-kanban-board"
        x-init="
            const bindColumns = () => {
                $el.querySelectorAll('[data-status-id]').forEach((column) => {
                    column.sortableInstance?.destroy()

                    column.sortableInstance = Sortable.create(column, {
                        group: 'filament-kanban',
                        ghostClass: 'opacity-50',
                        animation: 150,
                        onStart: () => document.body.classList.add('grabbing'),
                        onEnd: () => document.body.classList.remove('grabbing'),
                        onAdd: (event) => {
                            Livewire.dispatch('status-changed', {
                                recordId: event.item.dataset.recordId,
                                toStatus: event.to.dataset.statusId,
                                toOrderedIds: [...event.to.children].map((el) => el.dataset.recordId),
                            })
                        },
                        onUpdate: (event) => {
                            Livewire.dispatch('sort-changed', {
                                orderedIds: [...event.from.children].map((el) => el.dataset.recordId),
                            })
                        },
                    })
                })
            }

            bindColumns()

            const componentId = $wire.$id
            Livewire.hook('commit', ({ component, succeed }) => {
                if (component.id !== componentId) return
                succeed(() => bindColumns())
            })
        "
        x-on:kanban-move-rejected.window="
            $el.querySelector(`[data-record-id='${$event.detail.recordId}']`)
                ?.classList.add('animate-pulse-twice')
        "
    >
        @foreach ($statuses as $status)
            @include('filament-kanban::components.kanban-column', ['status' => $status])
        @endforeach
    </div>
</x-filament-panels::page>
