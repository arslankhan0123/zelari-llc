@php
    /**
     * Dropdown picker used instead of a native <select>, because Chrome's select
     * popup clamps every option row to ~30px and we need a 7px group divider.
     *
     * $items        collection of models with ->id and ->name
     *               (optionally ->tree_prefix for indentation, ->group_end for a divider)
     * $name         form field name
     * $selected     currently selected id (may be null/'')
     * $placeholder  text for the empty choice
     * $triggerClass classes for the closed control so it matches surrounding inputs
     * $rules        false for a flat list with no group dividers (e.g. brands)
     * $searchable   false hides the search box (shown by default)
     */
    $name         = $name        ?? 'category_id';
    $placeholder  = $placeholder ?? '— No Category —';
    $selected     = (string) ($selected ?? '');
    $triggerClass = $triggerClass ?? 'form-input';
    $rules        = $rules       ?? true;          // group dividers (off for flat lists)
    $searchable   = (bool) ($searchable ?? true);  // pass false to hide the search box

    $list = collect($items)->values();
    $opts = [];
    foreach ($list as $i => $row) {
        $opts[] = [
            'id'    => (string) $row->id,
            'label' => ($row->tree_prefix ?? '') . $row->name,
            'rule'  => $rules && (bool) ($row->group_end ?? false) && $i !== $list->count() - 1,
        ];
    }
@endphp

<div class="relative"
     x-data="{
        open: false,
        q: '',
        value: @js($selected),
        items: @js($opts),
        ph: @js($placeholder),
        label() { const f = this.items.find(o => o.id === String(this.value)); return f ? f.label : this.ph; },
        filtered() {
            const s = this.q.trim().toLowerCase();
            return s ? this.items.filter(o => o.label.toLowerCase().includes(s)) : this.items;
        },
        pick(v) { this.value = v; this.open = false; this.q = ''; },
        toggle() { this.open = !this.open; if (this.open) this.$nextTick(() => this.$refs.q && this.$refs.q.focus()); }
     }"
     @keydown.escape.window="open = false"
     @click.outside="open = false">

    <input type="hidden" name="{{ $name }}" :value="value">

    <button type="button" @click="toggle()"
            class="{{ $triggerClass }} flex items-center justify-between gap-2 text-left">
        <span x-text="label()" :class="value ? '' : 'text-gray-400'" class="truncate"></span>
        <svg class="w-4 h-4 shrink-0 text-gray-400 transition-transform" :class="open && 'rotate-180'"
             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-cloak x-transition.opacity.duration.120ms
         class="absolute z-50 mt-1.5 w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-xl overflow-hidden">

        @if($searchable)
        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
            <input x-ref="q" x-model="q" type="text" placeholder="Search categories…"
                   class="w-full px-2.5 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        @endif

        <ul class="max-h-72 overflow-y-auto py-1 text-sm">
            <li>
                <button type="button" @click="pick('')"
                        class="w-full text-left px-3 py-1.5 text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-gray-700"
                        :class="!value && 'bg-indigo-50 dark:bg-gray-700 font-medium'">{{ $placeholder }}</button>
            </li>
            <template x-for="o in filtered()" :key="o.id">
                <li>
                    <button type="button" @click="pick(o.id)" x-text="o.label"
                            class="w-full text-left px-3 py-1.5 text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-gray-700 truncate"
                            :class="String(value) === o.id && 'bg-indigo-50 dark:bg-gray-700 font-semibold text-indigo-700 dark:text-indigo-300'"></button>
                    <div x-show="o.rule && !q.trim()" class="cat-rule"></div>
                </li>
            </template>
            <li x-show="filtered().length === 0" class="px-3 py-3 text-center text-gray-400">No match</li>
        </ul>
    </div>
</div>
