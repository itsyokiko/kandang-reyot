@props([
    'title',
    'value' => null,
    'subtitle' => null,
    'icon' => null,
    'href' => null,
    'color' => 'emerald',
])

@php
    $colorClasses = match($color) {
        'blue' => 'bg-blue-50/80 border-blue-200/70 text-blue-900 icon-bg:bg-blue-100 icon-text:text-blue-700',
        'pink' => 'bg-pink-50/80 border-pink-200/70 text-pink-900 icon-bg:bg-pink-100 icon-text:text-pink-700',
        'amber' => 'bg-amber-50/80 border-amber-200/70 text-amber-900 icon-bg:bg-amber-100 icon-text:text-amber-700',
        'rose' => 'bg-rose-50/80 border-rose-200/70 text-rose-900 icon-bg:bg-rose-100 icon-text:text-rose-700',
        'purple' => 'bg-purple-50/80 border-purple-200/70 text-purple-900 icon-bg:bg-purple-100 icon-text:text-purple-700',
        'slate' => 'bg-slate-50/80 border-slate-200/70 text-slate-900 icon-bg:bg-slate-100 icon-text:text-slate-700',
        default => 'bg-emerald-50/80 border-emerald-200/70 text-emerald-900 icon-bg:bg-emerald-100 icon-text:text-emerald-700',
    };
@endphp

@if($href)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => "block p-4 rounded-2xl border transition-all duration-200 active:scale-98 shadow-sm hover:shadow-md {$colorClasses}"]) }}>
@else
    <div {{ $attributes->merge(['class' => "p-4 rounded-2xl border shadow-sm {$colorClasses}"]) }}>
@endif
    <div class="flex items-start justify-between gap-2">
        <div class="space-y-1">
            <span class="text-xs font-semibold opacity-75 block leading-tight">{{ $title }}</span>
            @if($value !== null)
                <div class="text-2xl font-black tracking-tight leading-none mt-1">{{ $value }}</div>
            @endif
            @if($subtitle)
                <p class="text-[11px] opacity-80 mt-1 leading-snug">{{ $subtitle }}</p>
            @endif
            {{ $slot }}
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shrink-0 bg-white/70 shadow-xs">
                @if(str_starts_with($icon, 'ti '))
                    <i class="{{ $icon }}"></i>
                @else
                    <span>{{ $icon }}</span>
                @endif
            </div>
        @endif
    </div>
@if($href)
    </a>
@else
    </div>
@endif
