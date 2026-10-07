@props([
    'cols' => 2,
])

@php
    $gridCols = match((int)$cols) {
        1 => 'grid-cols-1',
        3 => 'grid-cols-3',
        4 => 'grid-cols-4',
        default => 'grid-cols-2',
    };
@endphp

<div {{ $attributes->merge(['class' => "grid {$gridCols} gap-3"]) }}>
    {{ $slot }}
</div>
