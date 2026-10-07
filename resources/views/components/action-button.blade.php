@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'href' => null,
    'fullWidth' => false,
])

@php
    $variantClasses = match($variant) {
        'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200',
        'danger' => 'bg-rose-600 hover:bg-rose-500 text-white shadow-sm',
        'amber' => 'bg-amber-600 hover:bg-amber-500 text-white shadow-sm',
        'outline' => 'bg-transparent hover:bg-slate-100 text-slate-700 border border-slate-300',
        'ghost' => 'bg-transparent hover:bg-slate-100 text-slate-700',
        default => 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm shadow-emerald-600/20',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-2 text-xs min-h-[36px]',
        'lg' => 'px-5 py-3.5 text-base min-h-[50px]',
        default => 'px-4 py-2.5 text-sm min-h-[44px]',
    };

    $widthClass = $fullWidth ? 'w-full' : 'inline-flex';
    $baseClasses = "{$widthClass} {$sizeClasses} {$variantClasses} items-center justify-center gap-2 rounded-xl font-semibold tracking-tight transition-all duration-150 active:scale-97 select-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if($icon)
            <span class="text-base leading-none">
                @if(str_starts_with($icon, 'ti '))
                    <i class="{{ $icon }}"></i>
                @else
                    {{ $icon }}
                @endif
            </span>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if($icon)
            <span class="text-base leading-none">
                @if(str_starts_with($icon, 'ti '))
                    <i class="{{ $icon }}"></i>
                @else
                    {{ $icon }}
                @endif
            </span>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
