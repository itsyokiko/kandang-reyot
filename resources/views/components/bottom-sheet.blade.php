@props([
    'id',
    'title' => null,
    'type' => null,
])

@php
    $isDataSheet = ($type === 'data') || (
        $type !== 'info' && (
            str_contains($id, 'Tambah') ||
            str_contains($id, 'Edit') ||
            str_contains($id, 'Atur') ||
            str_contains($id, 'Konfirmasi') ||
            str_contains($id, 'Relasi')
        )
    );
@endphp

{{-- Overlay --}}
<div id="{{ $id }}Overlay"
     onclick="KR.sheet.close('{{ $id }}')"
     class="fixed inset-0 z-[200] bg-slate-900/60 opacity-0 invisible pointer-events-none transition-[opacity,visibility] duration-300"
     aria-hidden="true"></div>

{{-- Sheet container --}}
<div id="{{ $id }}"
     class="fixed bottom-0 left-0 right-0 z-[201] max-w-lg mx-auto bg-white rounded-t-3xl shadow-2xl
            translate-y-full invisible
            transition-[transform,visibility] duration-300 ease-out
            flex flex-col overflow-hidden will-change-transform"
     style="{{ $isDataSheet ? 'max-height:100dvh;max-height:calc(var(--vh,1svh)*100)' : 'max-height:85dvh;max-height:85svh' }}"
     aria-modal="true"
     role="dialog"
     aria-label="{{ $title ?? 'Panel' }}">

    {{-- Drag handle --}}
    <div class="pt-3 pb-1 flex justify-center cursor-pointer shrink-0"
         onclick="KR.sheet.close('{{ $id }}')">
        <div class="w-12 h-1.5 rounded-full bg-slate-200"></div>
    </div>

    {{-- Title bar --}}
    @if($title)
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between shrink-0">
            <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ $title }}</h3>
            <button type="button"
                    onclick="KR.sheet.close('{{ $id }}')"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 active:scale-95 transition"
                    aria-label="Tutup">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>
    @endif

    {{--
        Scrollable body.
        - flex-1 + min-h-0: required for overflow-y-auto to work inside a flex column (Safari needs this).
        - padding-bottom via inline style: Tailwind CDN doesn't reliably generate complex arbitrary values.
        - We add 1.5rem bottom padding + env(safe-area-inset-bottom) so last item clears home bar.
    --}}
    <div class="flex-1 min-h-0 overflow-y-auto px-5 pt-4 space-y-4"
         style="padding-bottom:max(1.5rem,env(safe-area-inset-bottom,0px));
                -webkit-overflow-scrolling:touch;
                overscroll-behavior:contain;">
        {{ $slot }}
    </div>
</div>

