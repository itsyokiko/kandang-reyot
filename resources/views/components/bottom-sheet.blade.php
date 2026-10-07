@props([
    'id',
    'title' => null,
])

<div id="{{ $id }}Overlay"
     onclick="closeBottomSheet('{{ $id }}')"
     class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs opacity-0 pointer-events-none transition-opacity duration-300"></div>

<div id="{{ $id }}"
     class="fixed bottom-0 left-0 right-0 z-50 max-w-lg mx-auto bg-white rounded-t-3xl shadow-2xl translate-y-full transition-transform duration-300 ease-out flex flex-col max-h-[90vh] pb-[env(safe-area-inset-bottom)]">
    <!-- Handle bar -->
    <div class="pt-3 pb-1 flex justify-center cursor-pointer" onclick="closeBottomSheet('{{ $id }}')">
        <div class="w-12 h-1.5 rounded-full bg-slate-200"></div>
    </div>

    <!-- Header -->
    @if($title)
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ $title }}</h3>
            <button type="button"
                    onclick="closeBottomSheet('{{ $id }}')"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 active:scale-95 transition"
                    aria-label="Tutup">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>
    @endif

    <!-- Content -->
    <div class="p-5 overflow-y-auto space-y-4">
        {{ $slot }}
    </div>
</div>

<script>
    if (typeof openBottomSheet === 'undefined') {
        function openBottomSheet(sheetId) {
            const sheet = document.getElementById(sheetId);
            const overlay = document.getElementById(sheetId + 'Overlay');
            if (!sheet || !overlay) return;

            sheet.classList.remove('translate-y-full');
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100');
            document.body.classList.add('overflow-hidden');
        }

        function closeBottomSheet(sheetId) {
            const sheet = document.getElementById(sheetId);
            const overlay = document.getElementById(sheetId + 'Overlay');
            if (!sheet || !overlay) return;

            sheet.classList.add('translate-y-full');
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('overflow-hidden');
        }
    }
</script>
