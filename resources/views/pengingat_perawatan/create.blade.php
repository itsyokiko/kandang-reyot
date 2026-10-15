@extends('layouts.app')

@section('title', 'Buat Pengingat Perawatan')

@section('content')
<div class="space-y-4">
    <!-- Back Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('pengingat-perawatan.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-600 transition p-1">
            <i class="ti ti-arrow-left text-sm"></i>
            <span>Batal</span>
        </a>
    </div>

    <!-- Banner Pemisahan Konteks -->
    <div class="p-3 bg-teal-50/70 border border-teal-200 rounded-2xl flex items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center text-base shrink-0">
                <i class="ti ti-droplet"></i>
            </div>
            <div>
                <span class="font-bold text-teal-900 block">Perawatan Fasilitas Kandang?</span>
                <span class="text-[11px] text-teal-700">Untuk semprot / disinfeksi lingkungan kandang tanpa memilih kambing.</span>
            </div>
        </div>
        <a href="{{ route('semprot-kandang.index') }}" class="shrink-0 px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-xs shadow-2xs active:scale-95 transition">
            Buka Semprot &rarr;
        </a>
    </div>

    <x-section-card title="Pengingat Perawatan Kambing (Individu)" icon="ti ti-pill">
        <form action="{{ route('pengingat-perawatan.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Pilih Kambing -->
            <div>
                <label for="kambing_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Pilih Kambing <span class="text-rose-500">*</span>
                </label>
                <select name="kambing_id" id="kambing_id" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">-- Pilih Kambing --</option>
                    @foreach ($kambings as $k)
                        <option value="{{ $k->id }}" {{ (old('kambing_id', $selectedKambingId) == $k->id) ? 'selected' : '' }}>
                            {{ $k->nama }} ({{ $k->jenis_kelamin === 'jantan' ? 'Jantan' : 'Betina' }}{{ $k->ras ? ' - ' . $k->ras : '' }})
                        </option>
                    @endforeach
                </select>
                @error('kambing_id')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jenis Perawatan -->
            <div>
                <label for="jenis_perawatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Jenis Perawatan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="jenis_perawatan" id="jenis_perawatan" list="jenis_perawatan_options" placeholder="Pilih atau ketik jenis perawatan..." value="{{ old('jenis_perawatan', 'Vitamin B Complex') }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                <datalist id="jenis_perawatan_options">
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
                <label for="tanggal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Tanggal Jadwal <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @error('tanggal')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan -->
            <div>
                <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Catatan Opsional
                </label>
                <textarea name="catatan" id="catatan" rows="3" placeholder="Catatan tambahan (dosis, instruksi)..."
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('catatan') }}</textarea>
                @error('catatan')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <x-action-button href="{{ route('pengingat-perawatan.index') }}" variant="secondary">
                    Batal
                </x-action-button>
                <x-action-button type="submit" variant="primary">
                    Simpan Pengingat
                </x-action-button>
            </div>
        </form>
    </x-section-card>
</div>
@endsection
