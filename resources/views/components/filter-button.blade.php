@props([
    'target' => null,
    'count' => 0,
    'label' => 'Filter',
])

<button type="button"
        @if($target) onclick="openBottomSheet('{{ $target }}')" @endif
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 hover:bg-slate-50 active:scale-95 shadow-2xs transition select-none']) }}>
    <i class="ti ti-adjustments-horizontal text-base text-emerald-600"></i>
    <span>{{ $label }}</span>
    @if((int)$count > 0)
        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] font-bold">
            {{ $count }}
        </span>
    @endif
</button>
