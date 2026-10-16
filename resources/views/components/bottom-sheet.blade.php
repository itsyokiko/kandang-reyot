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

{{-- Sheet: slides up from bottom. max-h caps total height; inner flex layout distributes space. --}}
{{-- Data sheets use 100dvh so keyboard + browser bars don't clip content. --}}
<div id="{{ $id }}"
     class="fixed bottom-0 left-0 right-0 z-[201] max-w-lg mx-auto bg-white rounded-t-3xl shadow-2xl
            translate-y-full invisible
            transition-[transform,visibility] duration-300 ease-out
            flex flex-col {{ $isDataSheet ? '' : 'max-h-[85dvh]' }}
            overflow-hidden will-change-transform"
     @if($isDataSheet) style="max-height: calc(var(--vh, 1dvh) * 100);" @endif
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

    {{--
        Scrollable content area.
        flex-1 min-h-0 is critical: without min-h-0, flex children default to min-height:auto
        which prevents overflow scroll on WebKit/Safari.

        Bottom padding:
        - Data sheets: safe-area-inset-bottom so content doesn't hide behind iOS home bar.
          We do NOT add extra padding for bottom nav because it's hidden when sheet is open.
        - Info sheets: minimal safe area.
    --}}
    <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-5 pt-4 space-y-4
                {{ $isDataSheet
                    ? 'pb-[max(1.5rem,env(safe-area-inset-bottom,0px))]'
                    : 'pb-[max(1rem,env(safe-area-inset-bottom,0px))]' }}"
         style="-webkit-overflow-scrolling: touch; touch-action: pan-y;">
        {{ $slot }}
    </div>
</div>

<script>
    if (typeof openBottomSheet === 'undefined') {
        // Track how many sheets are open so we restore bottom nav only when all are closed
        window._openSheets = window._openSheets || new Set();

        function openBottomSheet(sheetId) {
            const sheet   = document.getElementById(sheetId);
            const overlay = document.getElementById(sheetId + 'Overlay');
            if (!sheet || !overlay) return;

            window._openSheets.add(sheetId);

            // Hide Bottom Navigation (slide down)
            const bottomNav = document.getElementById('appBottomNav');
            if (bottomNav) {
                bottomNav.classList.add('translate-y-full', 'opacity-0', 'pointer-events-none');
            }

            // Show sheet & overlay
            sheet.classList.remove('invisible', 'translate-y-full');
            overlay.classList.remove('invisible', 'opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100');
            document.body.classList.add('overflow-hidden');

            // Always start from top
            const scrollArea = sheet.querySelector('.overflow-y-auto');
            if (scrollArea) scrollArea.scrollTop = 0;
        }

        function closeBottomSheet(sheetId) {
            const sheet   = document.getElementById(sheetId);
            const overlay = document.getElementById(sheetId + 'Overlay');
            if (!sheet || !overlay) return;

            window._openSheets.delete(sheetId);

            // Slide sheet down & fade overlay
            sheet.classList.add('translate-y-full');
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0', 'pointer-events-none');

            // Only unlock body scroll + restore bottom nav when no sheet is open
            if (window._openSheets.size === 0) {
                document.body.classList.remove('overflow-hidden');
                const bottomNav = document.getElementById('appBottomNav');
                if (bottomNav) {
                    bottomNav.classList.remove('translate-y-full', 'opacity-0', 'pointer-events-none');
                }
            }

            // After transition, truly hide elements
            setTimeout(() => {
                sheet.classList.add('invisible');
                overlay.classList.add('invisible');
            }, 320);
        }
    }
</script>
