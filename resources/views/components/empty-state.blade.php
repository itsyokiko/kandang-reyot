@props([
    'title' => 'Belum ada data',
    'description' => null,
    'icon' => 'ti ti-inbox',
    'actionText' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center my-4']) }}>
    <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-3xl shadow-inner">
        @if(str_starts_with($icon, 'ti '))
            <i class="{{ $icon }}"></i>
        @else
            <span>{{ $icon }}</span>
        @endif
    </div>

    <h3 class="text-sm font-bold text-slate-900 tracking-tight">{{ $title }}</h3>

    @if($description)
        <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">{{ $description }}</p>
    @endif

    @if($actionText && $actionHref)
        <div class="mt-4">
            <a href="{{ $actionHref }}"
               class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white text-xs font-semibold shadow-sm transition">
                <span>{{ $actionText }}</span>
            </a>
        </div>
    @endif

    {{ $slot }}
</div>
