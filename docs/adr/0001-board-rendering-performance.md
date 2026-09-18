# ADR-0001: Board rendering & drag performance strategy

## Status

Accepted

## Context

Existing Filament kanban implementations in the ecosystem share a common failure pattern that causes browser slowness/freezes on real data volumes:

- Every column query loads its entire result set with no pagination or per-column limit.
- Each card embeds the full serialized record (e.g. a JSON blob) in a DOM attribute just so a click handler can read it — this means total HTML size scales with row count × model size, not with what's visible.
- Cards/columns have no stable DOM identity (no `wire:key`), so any Livewire re-render replaces the whole subtree instead of patching it — this also silently kills any JS library state (e.g. drag library instances) attached to those nodes.
- The drag library is initialized once (on page load / SPA navigation) and never re-bound after a Livewire re-render, so it stops working correctly after the first mutation.
- Reordering writes one UPDATE per moved-past row instead of one batched write.
- Status changes are persisted with no validation that the transition is legal.
- Record identity is used raw as an HTML `id`/CSS selector target, which breaks for UUID/ULID keys (e.g. an id that starts with a digit is not a valid CSS identifier).

We're building this plugin from scratch, so we can design these constraints out up front instead of patching them in later.

## Decision

1. **Bounded queries per column.** Each column loads a capped page of records (configurable, sane default e.g. 50) with a "load more" affordance, not `->get()` on the full status subset. Never render more DOM nodes than the visible window needs.
2. **Card payload = ID only.** `wire:click` (or dispatch) passes only the record key. The click handler fetches/opens the full record server-side. No `json_encode($record)` in markup.
3. **Stable DOM identity.** Every column and card gets a `wire:key` derived from its primary key. Livewire morphs instead of replacing subtrees, so external JS (drag library) state and Alpine state survive re-renders.
4. **Re-bind the drag library on every morph, not just on navigation.** Hook the drag library's init into Livewire's morph lifecycle (not only `livewire:navigated`), so it keeps working after any update — this is what upstream boards get wrong and why dragging silently breaks after the first move.
5. **Batch order writes.** Persist a reorder as a single query (e.g. one `CASE WHEN ... THEN ...` UPDATE, or the ORM's bulk-order helper) instead of one UPDATE per row moved past.
6. **Server-side transition validation.** Before persisting a status change, check it's an allowed transition for that record's domain (when the model exposes one). Reject + emit a browser event to snap the card back if invalid, instead of trusting the client.
7. **Prefixed, escaped DOM ids.** Card/column DOM ids are always prefixed (e.g. `record-{key}`) so integer, UUID, and ULID primary keys all work as CSS selectors/`querySelector` targets without special-casing.

## Consequences

- Board stays responsive regardless of table size — DOM node count and query cost are bounded by page size, not total record count.
- Drag interactions keep working across repeated moves in the same session (no "works once then breaks").
- Reordering N cards costs one query, not N.
- Invalid status moves (per the host app's state machine) are rejected server-side, not just visually assumed valid.
- Slightly more implementation work up front (morph-lifecycle rebinding, batched order writes) than the naive approach — accepted, since it's the actual thing that was asked for (avoid the known failure mode) rather than premature optimization.

## Validated against: filament-help-desk

Checked against `jeffersongoncalves/laravel-help-desk`'s `Ticket` model and `TicketStatus` enum:

- 6 statuses, so column count is small — no special layout handling needed.
- `TicketStatus::allowedTransitions()` already exists and enforces rules (e.g. `Resolved` can only go to `Open` or `Closed`, `Closed → Open` is config-gated). Decision 6 is not optional here — the board must call this before persisting a drag, or it'll let agents make moves the domain forbids.
- `Ticket` has heavy relations (comments, attachments, history) — decision 2 is required, not just an optimization: embedding a ticket's full JSON per card would be large and would leak relation data into markup.
- Ticket volume in a live help desk is unbounded — decision 1 (bounded per-column query) is required, not optional, for this consumer.
- Primary key is a plain auto-incrementing int here, so decision 7 isn't exercised by this consumer specifically, but keeps the plugin generic for others.

Conclusion: this design fits `filament-help-desk`'s ticket board use case, and the two decisions called out above (6 and 1) are load-bearing for it specifically, not just general hygiene.
