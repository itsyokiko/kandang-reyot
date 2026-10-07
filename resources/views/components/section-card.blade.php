@props([
    'title' => null,
    'icon' => null,
    'actionText' => null,
    'actionHref' => null,
    'badge' => null,
])

<section {{ $attributes->merge(['class' => 'bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/80 mb-4 transition-all']) }}>
    @if($title || $actionText || $badge)
        <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                @if($icon)
                    <span class="text-base text-emerald-700">
                        @if(str_starts_with($icon, 'ti '))
                            <i class="{{ $icon }}"></i>
                        @else
                            {{ $icon }}
                        @endif
                    </span>
                @endif
                @if($title)
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight leading-tight">{{ $title }}</h2>
                @endif
                @if($badge)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        {{ $badge }}
                    </span>
                @endif
            </div>

            @if($actionText && $actionHref)
                <a href="{{ $actionHref }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 active:scale-95 transition flex items-center gap-0.5">
                    <span>{{ $actionText }}</span>
                    <i class="ti ti-chevron-right text-xs"></i>
                </a>
            @endif
        </div>
    @endif

    <div>
        {{ $slot }}
    </div>
</section>
