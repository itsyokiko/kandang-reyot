@props([
    'id',
    'title' => null,
    'type' => null, // 'data', 'info', atau auto-detect
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

{{-- Overlay: truly hidden until opened --}}
<div id="{{ $id }}Overlay"
     onclick="closeBottomSheet('{{ $id }}')"
     class="fixed inset-0 z-[200] bg-slate-900/60 backdrop-blur-xs opacity-0 invisible pointer-events-none transition-[opacity,visibility] duration-300"
     aria-hidden="true"></div>

{{-- Sheet: invisible + translated off-screen until opened --}}
<div id="{{ $id }}"
     class="fixed bottom-0 left-0 right-0 z-[201] max-w-lg mx-auto bg-white rounded-t-3xl shadow-2xl
            translate-y-full invisible
            transition-[transform,visibility] duration-300 ease-out
            flex flex-col h-auto {{ $isDataSheet ? 'max-h-[100dvh] max-h-[100vh] sm:max-h-[92vh]' : 'max-h-[85vh] max-h-[85dvh]' }}
            overflow-hidden will-change-transform"
     aria-modal="true"
     role="dialog"
     aria-label="{{ $title ?? 'Panel' }}">

    {{-- Handle bar --}}
    <div class="pt-3 pb-1 flex justify-center cursor-pointer shrink-0" onclick="closeBottomSheet('{{ $id }}')">
        <div class="w-12 h-1.5 rounded-full bg-slate-200"></div>
    </div>

    {{-- Header --}}
    @if($title)
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between shrink-0">
            <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ $title }}</h3>
            <button type="button"
                    onclick="closeBottomSheet('{{ $id }}')"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 active:scale-95 transition"
                    aria-label="Tutup">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>
    @endif

    {{-- Content: flex-1 min-h-0 allows proper scrolling across all mobile browsers --}}
    {{-- For data/input sheets, 3rem (pb-12) plus safe-area is applied so bottom elements are never truncated by browser bars --}}
    <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-5 {{ $isDataSheet ? 'pb-12 pb-[calc(3rem+env(safe-area-inset-bottom,0px))]' : 'pb-6 pb-[calc(1.5rem+env(safe-area-inset-bottom,0px))]' }} space-y-4"
         style="-webkit-overflow-scrolling: touch; touch-action: pan-y;">
        {{ $slot }}
    </div>
</div>

<script>
    if (typeof openBottomSheet === 'undefined') {
        function openBottomSheet(sheetId) {
            const sheet   = document.getElementById(sheetId);
            const overlay = document.getElementById(sheetId + 'Overlay');
            if (!sheet || !overlay) return;

            // Animate Bottom Navigation out (slide down and fade)
            const bottomNav = document.getElementById('appBottomNav');
            if (bottomNav) {
                bottomNav.classList.add('translate-y-full', 'opacity-0', 'pointer-events-none');
            }

            // Make sheet visible and slide up
            sheet.classList.remove('invisible', 'translate-y-full');
            overlay.classList.remove('invisible', 'opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100');
            document.body.classList.add('overflow-hidden');

            // Reset scroll position to top
            const scrollArea = sheet.querySelector('.overflow-y-auto');
            if (scrollArea) {
                scrollArea.scrollTop = 0;
            }
        }

        function closeBottomSheet(sheetId) {
            const sheet   = document.getElementById(sheetId);
            const overlay = document.getElementById(sheetId + 'Overlay');
            if (!sheet || !overlay) return;

            // Slide down sheet and fade overlay
            sheet.classList.add('translate-y-full');
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('overflow-hidden');

            // Animate Bottom Navigation back in (slide up and restore)
            const bottomNav = document.getElementById('appBottomNav');
            if (bottomNav) {
                bottomNav.classList.remove('translate-y-full', 'opacity-0', 'pointer-events-none');
            }

            // After transition finishes, set invisible so it can't peek through scroll
            const dur = 320;
            setTimeout(() => {
                sheet.classList.add('invisible');
                overlay.classList.add('invisible');
            }, dur);
        }
    }
</script>
