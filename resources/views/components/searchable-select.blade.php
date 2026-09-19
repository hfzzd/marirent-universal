@props([
    'name' => '',
    'placeholder' => '-- Pilih --',
    'required' => false,
    'size' => 'md',
    'wrapClass' => 'w-full',
])

@php
    $sizes = [
        'sm' => 'px-3 py-2 text-[12px] rounded-lg pr-8',
        'md' => 'px-4 py-2.5 text-[13px] rounded-xl pr-9',
    ];
    $inputClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div x-data="searchableSelect({ placeholder: @js($placeholder), required: @js($required) })" class="relative {{ $wrapClass }}">
    <input
        type="text"
        x-ref="input"
        x-model="query"
        @focus="onFocus()"
        @click="onFocus()"
        @input="onInput()"
        @keydown="onKeydown($event)"
        @blur="onBlur()"
        :required="required"
        autocomplete="off"
        role="combobox"
        :aria-expanded="open.toString()"
        :aria-activedescendant="highlight > -1 ? 'ss-' + highlight : null"
        :placeholder="query ? '' : placeholder"
        class="w-full border border-gray-300 bg-white text-navy-800 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition cursor-pointer {{ $inputClass }}"
    >
    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-[11px] pointer-events-none transition-transform" :class="open ? 'rotate-180' : ''"></i>
    <button type="button" tabindex="-1" x-show="query" x-cloak @mousedown.prevent="clear()" class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 text-[11px] cursor-pointer">
        <i class="fas fa-circle-xmark"></i>
    </button>

    <template x-teleport="body">
        <ul
            x-show="open"
            x-cloak
            @mousedown.stop
            @click.stop
            :style="'top:' + drop.top + 'px;left:' + drop.left + 'px;width:' + drop.width + 'px'"
            class="fixed z-[70] overflow-hidden bg-white border border-gray-200 rounded-xl shadow-2xl shadow-navy-900/10 max-h-60 overflow-y-auto p-1"
            role="listbox"
        >
            <template x-for="(o, i) in filtered" :key="o.value">
                <li
                    :id="'ss-' + i"
                    @mousedown.prevent="select(o)"
                    @mouseenter="highlight = i"
                    role="option"
                    :aria-selected="String(o.value) === String(sel.value) ? 'true' : 'false'"
                    class="px-3 py-2 text-[13px] text-navy-700 cursor-pointer select-none rounded-lg truncate"
                    :class="i === highlight ? 'bg-sky-50 text-sky-700 font-medium' : 'hover:bg-gray-50'"
                    x-text="o.label"
                ></li>
            </template>
            <li x-show="filtered.length === 0" x-cloak class="px-3 py-3 text-[13px] text-gray-400 text-center cursor-default">Tidak ada hasil</li>
        </ul>
    </template>

    <select
        name="{{ $name }}"
        x-ref="select"
        class="hidden"
        {{ $attributes }}
    >{{ $slot }}</select>
</div>