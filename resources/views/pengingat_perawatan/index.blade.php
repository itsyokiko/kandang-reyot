@extends('layouts.app')

@section('title', 'Daftar Pengingat Perawatan')

@section('content')
<div class="space-y-4">
    <!-- Top Action Header -->
    <div class="flex items-center justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Perawatan Kambing</h1>
            <p class="text-xs text-slate-500">Vitamin, obat cacing & perawatan per individu</p>
        </div>
        <button type="button" onclick="openBottomSheet('sheetTambahPengingat')"
                class="inline-flex items-center justify-center gap-1.5 font-bold transition active:scale-95 shadow-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs">
            <i class="ti ti-plus"></i>
            <span>Buat</span>
        </button>
    </div>

    <!-- Segmented Tab: Perawatan Kambing vs Semprot Kandang -->
    <div class="flex p-1 bg-slate-200/70 rounded-xl gap-1">
        <a href="{{ route('pengingat-perawatan.index') }}" class="flex-1 py-1.5 px-3 rounded-lg text-center text-xs font-bold bg-white text-emerald-800 shadow-2xs">
            Perawatan Kambing
        </a>
        <a href="{{ route('semprot-kandang.index') }}" class="flex-1 py-1.5 px-3 rounded-lg text-center text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
            Semprot Kandang
        </a>
    </div>

    @if ($pengingatPerawatans->isEmpty())
        <div class="p-6 text-center bg-white rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                <i class="ti ti-pill"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-800">Belum ada pengingat perawatan</h3>
            <p class="text-xs text-slate-500 max-w-xs mx-auto">Buat jadwal pengingat untuk Vitamin B Complex, Obat Cacing, atau perawatan lainnya.</p>
            <button type="button" onclick="openBottomSheet('sheetTambahPengingat')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95">
                + Buat Pengingat Pertama
            </button>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($pengingatPerawatans as $p)
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs transition-all flex flex-col gap-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900">
                                @if ($p->kambing)
                                    <a href="{{ route('kambings.show', $p->kambing) }}" class="hover:text-emerald-600 transition">
                                        🐐 {{ $p->kambing->nama }}
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">Kambing Dihapus</span>
                                @endif
                            </span>
                        </div>
                        <x-status-badge :status="$p->status" />
                    </div>

                    <div class="text-xs space-y-1">
                        <div class="font-extrabold text-emerald-800 text-xs">
                            {{ $p->jenis_perawatan }}
                        </div>

                        <div class="flex items-center gap-2 text-slate-500 text-[11px]">
                            <span>Jadwal: <strong>{{ $p->tanggal ? $p->tanggal->format('d M Y') : '-' }}</strong></span>
                            @if ($p->tanggal_pemberian)
                                <span>• Diberikan: {{ $p->tanggal_pemberian->format('d M Y') }}</span>
                            @endif
                        </div>

                        @if ($p->catatan)
                            <p class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 mt-1 text-slate-700 whitespace-pre-line">
                                {{ $p->catatan }}
                            </p>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 text-xs font-semibold">
                        @if ($p->status === 'belum_diberikan')
                            <form action="{{ route('pengingat-perawatan.selesai', $p) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="tanggal_pemberian" value="{{ date('Y-m-d') }}">
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 font-bold transition active:scale-95">
                                    ✓ Sudah Diberikan
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('pengingat-perawatan.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, this, 'Hapus Pengingat Perawatan?', 'Apakah Anda yakin ingin menghapus pengingat ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-700 p-1">
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

<!-- Bottom Sheet Buat Pengingat Perawatan -->
<x-bottom-sheet id="sheetTambahPengingat" title="Buat Pengingat Perawatan">
    <form action="{{ route('pengingat-perawatan.store') }}" method="POST" class="space-y-4">
        @csrf

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
                Simpan Pengingat
            </button>
        </div>
    </form>
</x-bottom-sheet>

@if(request('action') === 'create' || ($errors->any() && !old('tanggal_pemberian')))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetTambahPengingat');
    });
</script>
@endif
@endsection
