@extends('layouts.app')

@section('title', 'Daftar Pengingat Perawatan')

@section('content')
<div class="space-y-3">
    <!-- Page Title & Header Actions -->
    <div class="flex items-center justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Perawatan Kambing</h1>
            <p class="text-xs text-slate-500">Vitamin, obat cacing & rekam jejak berkala</p>
        </div>
        <div class="flex items-center gap-1.5">
            <button type="button" onclick="openBottomSheet('sheetCatatRiwayat')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-95 transition">
                <i class="ti ti-check"></i>
                <span>Catat Riwayat</span>
            </button>
        </div>
    </div>

    <!-- Segmented Tab: Perawatan Kambing vs Semprot Kandang -->
    <div class="flex p-1 bg-slate-200/70 rounded-xl gap-1">
        <a href="{{ route('pengingat-perawatan.index', ['tab' => $tab]) }}" class="flex-1 py-1.5 px-3 rounded-lg text-center text-xs font-bold bg-white text-emerald-800 shadow-2xs">
            Perawatan Kambing
        </a>
        <a href="{{ route('semprot-kandang.index') }}" class="flex-1 py-1.5 px-3 rounded-lg text-center text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
            Semprot Kandang
        </a>
    </div>

    <!-- Sub-tabs: Jadwal Akan Datang vs Rekam Jejak (Riwayat) -->
    <div class="flex border-b border-slate-200 text-xs font-semibold">
        <a href="{{ route('pengingat-perawatan.index', ['tab' => 'jadwal']) }}"
           class="py-2.5 px-4 border-b-2 flex items-center gap-2 transition {{ $tab === 'jadwal' ? 'border-emerald-600 text-emerald-700 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <i class="ti ti-calendar-event"></i>
            <span>Jadwal Akan Datang</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $tab === 'jadwal' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                {{ $countJadwal }}
            </span>
        </a>
        <a href="{{ route('pengingat-perawatan.index', ['tab' => 'riwayat']) }}"
           class="py-2.5 px-4 border-b-2 flex items-center gap-2 transition {{ $tab === 'riwayat' ? 'border-emerald-600 text-emerald-700 font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <i class="ti ti-history"></i>
            <span>Rekam Jejak (Riwayat)</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $tab === 'riwayat' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                {{ $countRiwayat }}
            </span>
        </a>
    </div>

    @if ($pengingatPerawatans->isEmpty())
        @if ($tab === 'riwayat')
            <x-empty-state
                title="Belum ada riwayat perawatan"
                description="Catat perawatan yang sudah diberikan seperti vitamin atau obat cacing untuk mulai merekam jejak."
                icon="ti ti-history"
                actionText="+ Catat Riwayat Pertama"
                actionHref="javascript:openBottomSheet('sheetCatatRiwayat')"
            />
        @else
            <x-empty-state
                title="Tidak ada jadwal perawatan aktif"
                description="Buat jadwal pengingat untuk Vitamin B Complex, Obat Cacing, atau perawatan lainnya."
                icon="ti ti-calendar-plus"
                actionText="+ Buat Jadwal Baru"
                actionHref="javascript:openBottomSheet('sheetTambahPengingat')"
            />
        @endif
    @else
        <div class="space-y-3">
            @foreach ($pengingatPerawatans as $p)
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs transition-all flex flex-col gap-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900">
                                @if ($p->kambing)
                                    <a href="{{ route('kambings.show', $p->kambing) }}" class="hover:text-emerald-600 transition flex items-center gap-1.5">
                                        <span>🐐</span>
                                        <span>{{ $p->kambing->nama }}</span>
                                        @if($p->kambing->ras)
                                            <span class="text-[10px] text-slate-400 font-normal">({{ $p->kambing->ras }})</span>
                                        @endif
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">Kambing Dihapus</span>
                                @endif
                            </span>
                        </div>
                        <x-status-badge :status="$p->status" />
                    </div>

                    <div class="text-xs space-y-1">
                        <div class="font-extrabold text-emerald-800 text-sm flex items-center justify-between">
                            <span>{{ $p->jenis_perawatan }}</span>
                            @if ($p->status === 'belum_diberikan' && $p->tanggal)
                                @php
                                    $diff = (int) now()->startOfDay()->diffInDays($p->tanggal->startOfDay(), false);
                                @endphp
                                @if ($diff < 0)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800">
                                        Lewat {{ abs($diff) }} hari
                                    </span>
                                @elseif ($diff === 0)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                        Hari Ini
                                    </span>
                                @else
                                    <span class="text-[10px] font-semibold text-slate-500">
                                        {{ $diff }} hari lagi
                                    </span>
                                @endif
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-slate-500 text-[11px] pt-0.5">
                            @if ($p->status === 'belum_diberikan')
                                <span class="flex items-center gap-1">
                                    <i class="ti ti-calendar text-slate-400"></i>
                                    <span>Jadwal: <strong>{{ $p->tanggal ? $p->tanggal->format('d M Y') : '-' }}</strong></span>
                                </span>
                            @else
                                <span class="flex items-center gap-1 text-emerald-700 font-semibold">
                                    <i class="ti ti-check text-emerald-600"></i>
                                    <span>Selesai: <strong>{{ $p->tanggal_pemberian ? $p->tanggal_pemberian->format('d M Y') : '-' }}</strong></span>
                                </span>
                            @endif
                        </div>

                        @if ($p->catatan)
                            <p class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 mt-1.5 text-slate-700 text-[11px] whitespace-pre-line leading-relaxed">
                                {{ $p->catatan }}
                            </p>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 text-xs font-semibold">
                        @if ($p->status === 'belum_diberikan')
                            <button type="button"
                                    onclick="bukaSelesaiModal({{ $p->id }}, '{{ addslashes($p->jenis_perawatan) }}', '{{ $p->kambing?->nama }}')"
                                    class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 font-bold transition active:scale-95 flex items-center gap-1">
                                <i class="ti ti-check text-sm"></i>
                                <span>Sudah Diberikan</span>
                            </button>
                        @endif

                        <form action="{{ route('pengingat-perawatan.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, this, 'Hapus Catatan Perawatan?', 'Apakah Anda yakin ingin menghapus catatan perawatan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-700 p-1.5 text-xs">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($pengingatPerawatans->hasPages())
            <div class="pt-2">
                {{ $pengingatPerawatans->links() }}
            </div>
        @endif
    @endif
</div>

{{-- FAB: Pilihan Cepat (Jadwal / Catat Riwayat) --}}
<div class="fixed bottom-20 left-0 right-0 z-30 max-w-lg mx-auto pointer-events-none px-4 flex justify-end transition-all duration-200" data-fab>
    <button type="button"
            onclick="openBottomSheet('sheetTambahPengingat')"
            class="pointer-events-auto w-14 h-14 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white shadow-xl flex items-center justify-center transition-all focus:outline-none"
            aria-label="Buat Jadwal Perawatan">
        <i class="ti ti-plus text-2xl font-bold"></i>
    </button>
</div>

{{-- 1. Bottom Sheet: Rencanakan Jadwal Perawatan (Mendatang) --}}
<x-bottom-sheet id="sheetTambahPengingat" title="Rencanakan Jadwal Perawatan">
    <form action="{{ route('pengingat-perawatan.store') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="tipe_input" value="jadwal">

        <!-- Pilih Kambing -->
        <div>
            <label for="sheet_kambing_perawatan_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Pilih Kambing <span class="text-rose-500">*</span>
            </label>
            <select name="kambing_id" id="sheet_kambing_perawatan_id" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                <option value="">-- Pilih Kambing --</option>
                @foreach ($kambings as $k)
                    <option value="{{ $k->id }}" {{ old('kambing_id') == $k->id ? 'selected' : '' }}>
                        🐐 {{ $k->nama }} ({{ $k->jenis_kelamin === 'jantan' ? 'Jantan' : 'Betina' }}{{ $k->ras ? ' - ' . $k->ras : '' }})
                    </option>
                @endforeach
            </select>
            @error('kambing_id')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Perawatan -->
        <div>
            <label for="sheet_jenis_perawatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Jenis Perawatan <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="jenis_perawatan" id="sheet_jenis_perawatan" list="sheet_jenis_perawatan_options" placeholder="Pilih atau ketik jenis..." value="{{ old('jenis_perawatan', 'Vitamin B Complex') }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            <datalist id="sheet_jenis_perawatan_options">
                <option value="Vitamin B Complex">
                <option value="Obat Cacing">
                <option value="Vaksinasi">
                <option value="Pembersihan Kuku">
                <option value="Antibiotik">
            </datalist>
            @error('jenis_perawatan')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanggal Jadwal -->
        <div>
            <label for="sheet_tanggal_perawatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Tanggal Jadwal <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal" id="sheet_tanggal_perawatan" value="{{ old('tanggal', date('Y-m-d')) }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            @error('tanggal')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Catatan -->
        <div>
            <label for="sheet_catatan_perawatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Catatan Opsional
            </label>
            <textarea name="catatan" id="sheet_catatan_perawatan" rows="2" placeholder="Dosis, instruksi pemberian..."
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('catatan') }}</textarea>
            @error('catatan')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetTambahPengingat')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Simpan Jadwal
            </button>
        </div>
    </form>
</x-bottom-sheet>

{{-- 2. Bottom Sheet: Catat Riwayat Perawatan (Sudah Dilakukan + Kapan Dilakukan Lagi) --}}
<x-bottom-sheet id="sheetCatatRiwayat" title="Catat Riwayat Perawatan">
    <form action="{{ route('pengingat-perawatan.store') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="tipe_input" value="riwayat">

        <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl text-xs text-emerald-800">
            Catat perawatan yang <strong>sudah selesai diberikan</strong> beserta rencana jadwal berikutnya secara berkala.
        </div>

        <!-- Pilih Kambing -->
        <div>
            <label for="sheet_riwayat_kambing_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Pilih Kambing <span class="text-rose-500">*</span>
            </label>
            <select name="kambing_id" id="sheet_riwayat_kambing_id" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                <option value="">-- Pilih Kambing --</option>
                @foreach ($kambings as $k)
                    <option value="{{ $k->id }}">
                        🐐 {{ $k->nama }} ({{ $k->jenis_kelamin === 'jantan' ? 'Jantan' : 'Betina' }}{{ $k->ras ? ' - ' . $k->ras : '' }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Jenis Perawatan -->
        <div>
            <label for="sheet_riwayat_jenis" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Jenis Perawatan / Obat <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="jenis_perawatan" id="sheet_riwayat_jenis" list="sheet_jenis_perawatan_options" placeholder="Contoh: Obat Cacing Albendazole, Vitamin B..." required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
        </div>

        <!-- Tanggal Dilakukan -->
        <div>
            <label for="sheet_riwayat_tanggal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Tanggal Diberikan <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal_pemberian" id="sheet_riwayat_tanggal" value="{{ date('Y-m-d') }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
        </div>

        <!-- Kapan Akan Dilakukan Lagi (Berkala) -->
        <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 space-y-2">
            <label for="sheet_riwayat_ulang" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                Kapan Akan Dilakukan Lagi? <span class="text-[10px] font-normal text-slate-400">(Jadwal Berkala)</span>
            </label>
            <select name="ulang_hari" id="sheet_riwayat_ulang"
                class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                <option value="">-- Tidak Perlu Dijadwalkan Ulang --</option>
                <option value="30">30 Hari Lagi (1 Bulan)</option>
                <option value="60">60 Hari Lagi (2 Bulan)</option>
                <option value="90">90 Hari Lagi (3 Bulan)</option>
                <option value="180">180 Hari Lagi (6 Bulan)</option>
            </select>
            <p class="text-[10px] text-slate-500">Sistem otomatis membuat jadwal pengingat berikutnya untuk kambing ini.</p>
        </div>

        <!-- Catatan -->
        <div>
            <label for="sheet_riwayat_catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Catatan Dosis & Hasil
            </label>
            <textarea name="catatan" id="sheet_riwayat_catatan" rows="2" placeholder="Dosis yang diberikan, reaksi kambing, merk obat..."
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"></textarea>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetCatatRiwayat')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Simpan ke Riwayat
            </button>
        </div>
    </form>
</x-bottom-sheet>

{{-- 3. Bottom Sheet: Konfirmasi Selesai & Jadwalkan Lagi --}}
<x-bottom-sheet id="sheetSelesaiJadwal" title="Konfirmasi Perawatan Selesai">
    <form id="formSelesaiJadwal" method="POST" class="space-y-4">
        @csrf
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 space-y-1">
            <span class="font-bold block" id="selesaiKambingNama"></span>
            <span id="selesaiPerawatanJenis"></span>
        </div>

        <div>
            <label for="selesai_tanggal_pemberian" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Tanggal Diberikan <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal_pemberian" id="selesai_tanggal_pemberian" value="{{ date('Y-m-d') }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
        </div>

        <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 space-y-2">
            <label for="selesai_ulang_hari" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                Kapan Akan Dilakukan Lagi? <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
            </label>
            <select name="ulang_hari" id="selesai_ulang_hari"
                class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                <option value="">-- Tidak Perlu Dijadwalkan Ulang --</option>
                <option value="30">30 Hari Lagi (1 Bulan)</option>
                <option value="60">60 Hari Lagi (2 Bulan)</option>
                <option value="90">90 Hari Lagi (3 Bulan)</option>
                <option value="180">180 Hari Lagi (6 Bulan)</option>
            </select>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetSelesaiJadwal')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Simpan & Selesai
            </button>
        </div>
    </form>
</x-bottom-sheet>

<script>
    function bukaSelesaiModal(id, jenis, kambing) {
        const form = document.getElementById('formSelesaiJadwal');
        if (form) {
            form.action = `/pengingat-perawatan/${id}/selesai`;
        }
        const kambingEl = document.getElementById('selesaiKambingNama');
        if (kambingEl) kambingEl.textContent = '🐐 Kambing: ' + (kambing || 'Kambing');
        const jenisEl = document.getElementById('selesaiPerawatanJenis');
        if (jenisEl) jenisEl.textContent = 'Perawatan: ' + jenis;

        openBottomSheet('sheetSelesaiJadwal');
    }
</script>

@if(request('action') === 'create' || ($errors->any() && !old('tanggal_pemberian')))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetTambahPengingat');
    });
</script>
@endif
@endsection
