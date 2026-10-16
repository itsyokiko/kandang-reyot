@extends('layouts.app')

@section('title', 'Relasi Silsilah')

@section('content')
<div class="space-y-4">
    <!-- 1. Hero Header Banner -->
    <div class="bg-gradient-to-br from-indigo-800 via-indigo-700 to-purple-800 rounded-3xl p-5 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-4 -bottom-6 text-7xl opacity-15 select-none pointer-events-none">🌳</div>
        <div class="relative z-10 space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-indigo-100 backdrop-blur-xs">
                    <span>🔗</span> Silsilah Mandiri
                </span>
                <a href="{{ route('silsilah.index') }}" class="text-[11px] font-semibold text-indigo-200 hover:text-white underline flex items-center gap-1">
                    <span>Lihat Pohon</span>
                    <i class="ti ti-arrow-right"></i>
                </a>
            </div>
            <h1 class="text-xl font-black tracking-tight text-white mt-1">Relasi Silsilah Kambing</h1>
            <p class="text-xs text-indigo-100/90 font-medium leading-relaxed">
                Hubungkan silsilah kambing nyata secara manual: tentukan orang tua (ayah/ibu) lalu pilih kambing-kambing yang menjadi anak.
            </p>
        </div>
    </div>

    <!-- 2. Ringkasan Status Relasi -->
    <div class="grid grid-cols-3 gap-2.5">
        <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-xs text-center">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight block">Total Kambing</span>
            <span class="text-xl font-black text-slate-900 leading-tight">{{ $totalKambing }}</span>
            <span class="text-[10px] text-slate-500 block mt-0.5">ekor terdaftar</span>
        </div>
        <div class="p-3 rounded-2xl bg-emerald-50/70 border border-emerald-100 shadow-xs text-center">
            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-tight block">Terhubung</span>
            <span class="text-xl font-black text-emerald-900 leading-tight">{{ $terhubungCount }}</span>
            <span class="text-[10px] text-emerald-600 block mt-0.5">punya orang tua</span>
        </div>
        <div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-100 shadow-xs text-center">
            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-tight block">Belum Terhubung</span>
            <span class="text-xl font-black text-amber-900 leading-tight">{{ $belumTerhubungCount }}</span>
            <span class="text-[10px] text-amber-700 block mt-0.5">tanpa orang tua</span>
        </div>
    </div>

    <!-- 3. Tombol Aksi Utama -->
    <button type="button" onclick="openBottomSheet('sheetHubungkanRelasi')"
            class="w-full py-3.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-bold text-xs shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2 transition cursor-pointer">
        <i class="ti ti-git-fork text-base"></i>
        <span>+ Hubungkan Relasi Orang Tua & Anak</span>
    </button>

    <!-- 4. Daftar Kambing Belum Punya Orang Tua (Prioritas Hubungkan) -->
    @if ($kambingTanpaOrangTua->isNotEmpty())
        <x-section-card title="Perlu Dihubungkan (Belum Ada Orang Tua)" icon="ti ti-alert-circle">
            <p class="text-xs text-slate-500 mb-2.5">
                Kambing di bawah ini belum memiliki catatan ayah (pejantan) maupun ibu (induk):
            </p>
            <div class="divide-y divide-slate-100">
                @foreach ($kambingTanpaOrangTua->take(6) as $k)
                    <div class="py-2.5 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-xs text-slate-800 truncate">{{ $k->nama }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold {{ $k->jenis_kelamin === 'jantan' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                                    {{ $k->jenis_kelamin === 'jantan' ? '♂ Jantan' : '♀ Betina' }}
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400 block truncate">{{ $k->ras ?? 'Tanpa ras' }}</span>
                        </div>
                        <button type="button"
                                onclick="hubungkanSpesifik({{ $k->id }}, '{{ addslashes($k->nama) }}')"
                                class="px-2.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 active:scale-95 text-indigo-700 font-bold text-[11px] transition shrink-0">
                            Hubungkan &rarr;
                        </button>
                    </div>
                @endforeach
            </div>
            @if ($kambingTanpaOrangTua->count() > 6)
                <p class="text-[11px] text-slate-400 text-center pt-2 border-t border-slate-100">
                    + {{ $kambingTanpaOrangTua->count() - 6 }} ekor kambing lainnya belum terhubung.
                </p>
            @endif
        </x-section-card>
    @endif

    <!-- 5. Daftar Seluruh Relasi Silsilah -->
    <x-section-card title="Daftar Relasi Kambing Terdaftar" icon="ti ti-sitemap">
        @if ($allKambings->isEmpty())
            <div class="text-center py-6 text-slate-400 text-xs">
                <i class="ti ti-mood-empty text-3xl mb-1 block"></i>
                <p>Belum ada data kambing tersimpan.</p>
            </div>
        @else
            <!-- Search Filter Bar -->
            <div class="mb-3">
                <div class="relative">
                    <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="cariRelasiList" oninput="filterRelasiList()"
                           placeholder="Cari nama kambing, ras, atau orang tua..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                </div>
            </div>

            <div class="space-y-2.5" id="relasiListContainer">
                @foreach ($allKambings as $k)
                    <div class="item-relasi p-3 rounded-2xl bg-white border border-slate-200/80 shadow-2xs space-y-2"
                         data-nama="{{ strtolower($k->nama) }}"
                         data-ras="{{ strtolower($k->ras ?? '') }}"
                         data-ortu="{{ strtolower(($k->pejantan->nama ?? '') . ' ' . ($k->induk->nama ?? '')) }}">
                        <!-- Header Baris Kambing -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm shrink-0 {{ $k->jenis_kelamin === 'jantan' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $k->jenis_kelamin === 'jantan' ? '♂' : '♀' }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('kambings.show', $k) }}" class="font-bold text-xs text-slate-900 hover:text-indigo-600 truncate block">
                                        {{ $k->nama }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 block truncate">
                                        {{ $k->ras ?? 'Tanpa ras' }} • Status: <strong>{{ ucfirst($k->status) }}</strong>
                                    </span>
                                </div>
                            </div>

                            <a href="{{ route('silsilah.index', $k) }}"
                               class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition shrink-0"
                               title="Lihat Pohon Silsilah">
                                <i class="ti ti-sitemap"></i>
                                <span>Pohon</span>
                            </a>
                        </div>

                        <!-- Baris Orang Tua -->
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
                            <!-- Ayah / Pejantan -->
                            <div class="p-2 rounded-xl bg-blue-50/50 border border-blue-100 flex items-center justify-between">
                                <div class="min-w-0">
                                    <span class="text-[9px] font-bold text-blue-600 uppercase tracking-tight block">Ayah (Pejantan)</span>
                                    @if ($k->pejantan)
                                        <a href="{{ route('kambings.show', $k->pejantan) }}" class="font-bold text-slate-900 hover:underline truncate block text-[11px]">
                                            ♂ {{ $k->pejantan->nama }}
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic block">Belum ada</span>
                                    @endif
                                </div>
                                @if ($k->pejantan)
                                    <form action="{{ route('relasi.destroy', $k) }}?tipe=pejantan" method="POST"
                                          onsubmit="return confirmDelete(event, this, 'Hapus Relasi Ayah?', 'Hubungan ayah dengan kambing ini akan diputuskan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 transition" title="Putus relasi ayah">
                                            <i class="ti ti-x text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Ibu / Induk -->
                            <div class="p-2 rounded-xl bg-pink-50/50 border border-pink-100 flex items-center justify-between">
                                <div class="min-w-0">
                                    <span class="text-[9px] font-bold text-pink-600 uppercase tracking-tight block">Ibu (Induk)</span>
                                    @if ($k->induk)
                                        <a href="{{ route('kambings.show', $k->induk) }}" class="font-bold text-slate-900 hover:underline truncate block text-[11px]">
                                            ♀ {{ $k->induk->nama }}
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic block">Belum ada</span>
                                    @endif
                                </div>
                                @if ($k->induk)
                                    <form action="{{ route('relasi.destroy', $k) }}?tipe=induk" method="POST"
                                          onsubmit="return confirmDelete(event, this, 'Hapus Relasi Ibu?', 'Hubungan induk dengan kambing ini akan diputuskan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 transition" title="Putus relasi induk">
                                            <i class="ti ti-x text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-section-card>
</div>

<!-- ========================================== -->
<!-- BOTTOM SHEET: HUBUNGKAN RELASI SILSILAH   -->
<!-- ========================================== -->
<x-bottom-sheet id="sheetHubungkanRelasi" title="Hubungkan Relasi Silsilah">
    <form action="{{ route('relasi.store') }}" method="POST" id="formHubungkanRelasi" class="space-y-4">
        @csrf

        <!-- Langkah 1: Pilih Orang Tua -->
        <div class="space-y-3 p-3.5 rounded-2xl bg-indigo-50/60 border border-indigo-100">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">1</div>
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-indigo-950">Pilih Orang Tua</h4>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <!-- Dropdown Pejantan (Ayah) -->
                <div>
                    <label for="relasi_pejantan_id" class="block text-[11px] font-bold text-slate-700 uppercase tracking-tight mb-1">
                        ♂ Pejantan (Ayah)
                    </label>
                    <select name="pejantan_id" id="relasi_pejantan_id" onchange="updateEligibleChildren()"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Tanpa Ayah (Kosongkan) —</option>
                        @foreach ($pejantans as $p)
                            <option value="{{ $p->id }}">
                                ♂ {{ $p->nama }} {{ $p->ras ? '(' . $p->ras . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dropdown Induk (Ibu) -->
                <div>
                    <label for="relasi_induk_id" class="block text-[11px] font-bold text-slate-700 uppercase tracking-tight mb-1">
                        ♀ Induk (Ibu)
                    </label>
                    <select name="induk_id" id="relasi_induk_id" onchange="updateEligibleChildren()"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Tanpa Induk (Kosongkan) —</option>
                        @foreach ($induks as $i)
                            <option value="{{ $i->id }}">
                                ♀ {{ $i->nama }} {{ $i->ras ? '(' . $i->ras . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="text-[10px] text-indigo-700 font-medium">
                * Minimal pilih salah satu (Ayah saja, Ibu saja, atau Sepasang Orang Tua).
            </p>
        </div>

        <!-- Langkah 2: Pilih Anak-Anak -->
        <div class="space-y-2.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">2</div>
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Pilih Kambing Anak</h4>
                </div>
                <div class="flex items-center gap-2 text-[11px]">
                    <button type="button" onclick="toggleSelectAllAnak(true)" class="text-indigo-600 font-bold hover:underline">Pilih Semua</button>
                    <span class="text-slate-300">•</span>
                    <button type="button" onclick="toggleSelectAllAnak(false)" class="text-slate-500 hover:underline">Reset</button>
                </div>
            </div>

            <!-- Search Filter Anak -->
            <div class="relative">
                <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="filterAnakInput" oninput="filterAnakOptions()"
                       placeholder="Cari kambing yang akan dijadikan anak..."
                       class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
            </div>

            <!-- List Kambing Anak dengan Checkbox -->
            <div class="max-h-60 overflow-y-auto border border-slate-200 rounded-2xl divide-y divide-slate-100 bg-white p-1" id="listAnakContainer">
                @foreach ($allKambings as $k)
                    <label class="item-anak-checkbox flex items-center gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 transition cursor-pointer select-none"
                           id="label-anak-{{ $k->id }}"
                           data-nama="{{ strtolower($k->nama) }}"
                           data-ras="{{ strtolower($k->ras ?? '') }}">
                        <input type="checkbox" name="anak_ids[]" value="{{ $k->id }}" id="check-anak-{{ $k->id }}"
                               class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-xs text-slate-900 truncate">{{ $k->nama }}</span>
                                <span class="text-[10px] px-1 py-0.2 rounded font-semibold {{ $k->jenis_kelamin === 'jantan' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                                    {{ $k->jenis_kelamin === 'jantan' ? '♂' : '♀' }}
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-400 block truncate">
                                {{ $k->ras ?? 'Tanpa ras' }}
                                @if ($k->pejantan || $k->induk)
                                    • (Saat ini: {{ $k->pejantan ? 'Ayah: ' . $k->pejantan->nama : '' }}{{ $k->pejantan && $k->induk ? ', ' : '' }}{{ $k->induk ? 'Ibu: ' . $k->induk->nama : '' }})
                                @else
                                    • <span class="text-amber-600 font-semibold">Belum punya orang tua</span>
                                @endif
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>
            <p id="anakSelectedCountText" class="text-[11px] text-slate-500 font-medium">
                0 ekor anak dipilih.
            </p>
        </div>

        <!-- Actions Buttons -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetHubungkanRelasi')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs shadow-xs transition">
                Simpan Relasi
            </button>
        </div>
    </form>
</x-bottom-sheet>

<script>
    // Update counter anak yang dipilih
    document.addEventListener('change', function(e) {
        if (e.target && e.target.name === 'anak_ids[]') {
            updateSelectedCount();
        }
    });

    function updateSelectedCount() {
        const checked = document.querySelectorAll('input[name="anak_ids[]"]:checked').length;
        const textEl = document.getElementById('anakSelectedCountText');
        if (textEl) {
            textEl.textContent = `${checked} ekor anak dipilih.`;
        }
    }

    function toggleSelectAllAnak(select) {
        const visibleItems = document.querySelectorAll('.item-anak-checkbox:not(.hidden) input[type="checkbox"]');
        visibleItems.forEach(cb => {
            if (!cb.disabled) {
                cb.checked = select;
            }
        });
        updateSelectedCount();
    }

    // Filter list kambing anak di bottom sheet
    function filterAnakOptions() {
        const query = (document.getElementById('filterAnakInput')?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('.item-anak-checkbox');
        items.forEach(item => {
            const nama = item.getAttribute('data-nama') || '';
            const ras = item.getAttribute('data-ras') || '';
            if (!query || nama.includes(query) || ras.includes(query)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    }

    // Filter daftar seluruh relasi kambing di halaman
    function filterRelasiList() {
        const query = (document.getElementById('cariRelasiList')?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('.item-relasi');
        items.forEach(item => {
            const nama = item.getAttribute('data-nama') || '';
            const ras = item.getAttribute('data-ras') || '';
            const ortu = item.getAttribute('data-ortu') || '';
            if (!query || nama.includes(query) || ras.includes(query) || ortu.includes(query)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    }

    // Disable opsi anak jika dia dipilih sebagai orang tua
    function updateEligibleChildren() {
        const pejantanId = document.getElementById('relasi_pejantan_id')?.value;
        const indukId = document.getElementById('relasi_induk_id')?.value;

        document.querySelectorAll('.item-anak-checkbox').forEach(item => {
            const cb = item.querySelector('input[type="checkbox"]');
            if (!cb) return;

            if (cb.value === pejantanId || cb.value === indukId) {
                cb.checked = false;
                cb.disabled = true;
                item.classList.add('opacity-40', 'pointer-events-none');
            } else {
                cb.disabled = false;
                item.classList.remove('opacity-40', 'pointer-events-none');
            }
        });
        updateSelectedCount();
    }

    // Helper klik "Hubungkan" pada baris kambing tertentu
    function hubungkanSpesifik(anakId, namaKambing) {
        toggleSelectAllAnak(false);
        const cb = document.getElementById('check-anak-' + anakId);
        if (cb) {
            cb.checked = true;
            updateSelectedCount();
        }
        openBottomSheet('sheetHubungkanRelasi');
    }

    @if($selectedKambingId)
        document.addEventListener('DOMContentLoaded', function() {
            hubungkanSpesifik({{ $selectedKambingId }});
        });
    @endif
</script>
@endsection
