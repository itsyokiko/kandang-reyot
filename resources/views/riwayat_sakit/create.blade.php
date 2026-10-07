@extends('layouts.app')

@section('title', 'Catat Kambing Sakit')

@section('content')
<div class="space-y-4">
    <!-- Back Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('riwayat-sakit.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-600 transition p-1">
            <i class="ti ti-arrow-left text-sm"></i>
            <span>Batal</span>
        </a>
    </div>

    <x-section-card title="Catat Kambing Sakit" icon="ti ti-stethoscope">
        <form action="{{ route('riwayat-sakit.store') }}" method="POST" class="space-y-4">
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

            <!-- Tanggal Mulai Sakit -->
            <div>
                <label for="tanggal_mulai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Tanggal Mulai Sakit <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @error('tanggal_mulai')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan -->
            <div>
                <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Gejala / Catatan
                </label>
                <textarea name="catatan" id="catatan" rows="3" placeholder="Contoh: Nafsu makan menurun, lemas..."
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('catatan') }}</textarea>
                @error('catatan')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <x-action-button href="{{ route('riwayat-sakit.index') }}" variant="secondary">
                    Batal
                </x-action-button>
                <x-action-button type="submit" variant="danger">
                    Simpan Catatan Sakit
                </x-action-button>
            </div>
        </form>
    </x-section-card>
</div>
@endsection
