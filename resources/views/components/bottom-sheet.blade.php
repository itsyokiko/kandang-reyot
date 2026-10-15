@props([
    'id',
    'title' => null,
])

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
            flex flex-col max-h-[90vh] max-h-[90dvh]
            overflow-hidden"
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

    {{-- Content: flex-1 min-h-0 allows proper scrolling on all device screens --}}
    {{-- Bottom padding is measured from bottom-nav height (4rem/64px) + safe area + extra margin so action buttons are clearly visible above bottom nav --}}
    <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-5 pb-[calc(5.5rem+env(safe-area-inset-bottom,0px))] space-y-4">
        {{ $slot }}
    </div>
</div>

<script>
    if (typeof openBottomSheet === 'undefined') {
        function openBottomSheet(sheetId) {
            const sheet   = document.getElementById(sheetId);
            const overlay = document.getElementById(sheetId + 'Overlay');
            if (!sheet || !overlay) return;

            // Make visible first so transition plays
            sheet.classList.remove('invisible', 'translate-y-full');
            overlay.classList.remove('invisible', 'opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100');
            document.body.classList.add('overflow-hidden');
        }

        function closeBottomSheet(sheetId) {
            const sheet   = document.getElementById(sheetId);
            const overlay = document.getElementById(sheetId + 'Overlay');
            if (!sheet || !overlay) return;

            sheet.classList.add('translate-y-full');
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('overflow-hidden');

            // After transition finishes, set invisible so it can't peek through scroll
            const dur = 320;
            setTimeout(() => {
                sheet.classList.add('invisible');
                overlay.classList.add('invisible');
            }, dur);
        }
    }
</script>
