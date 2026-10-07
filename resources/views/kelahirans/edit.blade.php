@extends('layouts.app')

@section('title', 'Edit Kelahiran')

@section('content')
<div class="space-y-4">
    <!-- Back Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('kelahirans.show', $kelahiran) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-600 transition p-1">
            <i class="ti ti-arrow-left text-sm"></i>
            <span>Kembali ke Detail</span>
        </a>
    </div>

    <x-section-card title="Edit Catatan Kelahiran" icon="ti ti-edit">
        <form action="{{ route('kelahirans.update', $kelahiran) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Pilihan Perkawinan (Opsional) -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-1.5">
                <label for="perkawinan_id" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Referensi Perkawinan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                </label>
                <select name="perkawinan_id" id="perkawinan_id"
                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">-- Tanpa Referensi Perkawinan --</option>
                    @foreach ($perkawinans as $p)
                        <option value="{{ $p->id }}" {{ old('perkawinan_id', $kelahiran->perkawinan_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->betina->nama }} (Induk) &times; {{ $p->pejantan->nama }} (Pejantan) — Kawin: {{ $p->tanggal_kawin->format('d M Y') }}
                        </option>
                    @endforeach
                </select>
                @error('perkawinan_id')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pasangan Induk & Pejantan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="induk_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Induk Betina <span class="text-rose-500">*</span>
                    </label>
                    <select name="induk_id" id="induk_id" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        @foreach ($betinas as $betina)
                            <option value="{{ $betina->id }}" {{ old('induk_id', $kelahiran->induk_id) == $betina->id ? 'selected' : '' }}>
                                ♀ {{ $betina->nama }} {{ $betina->ras ? '(' . $betina->ras . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('induk_id')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pejantan_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Pejantan <span class="text-rose-500">*</span>
                    </label>
                    <select name="pejantan_id" id="pejantan_id" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        @foreach ($pejantans as $pejantan)
                            <option value="{{ $pejantan->id }}" {{ old('pejantan_id', $kelahiran->pejantan_id) == $pejantan->id ? 'selected' : '' }}>
                                ♂ {{ $pejantan->nama }} {{ $pejantan->ras ? '(' . $pejantan->ras . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('pejantan_id')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tanggal Kelahiran -->
            <div>
                <label for="tanggal_kelahiran" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Tanggal Kelahiran <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_kelahiran" id="tanggal_kelahiran"
                    value="{{ old('tanggal_kelahiran', $kelahiran->tanggal_kelahiran->format('Y-m-d')) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                <p class="text-[11px] text-slate-400 mt-1">Mengubah tanggal ini juga memperbarui tanggal lahir anak-anaknya.</p>
                @error('tanggal_kelahiran')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan Kelahiran -->
            <div>
                <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Catatan Peristiwa Kelahiran
                </label>
                <textarea name="catatan" id="catatan" rows="3"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('catatan', $kelahiran->catatan) }}</textarea>
                @error('catatan')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <x-action-button href="{{ route('kelahirans.show', $kelahiran) }}" variant="secondary">
                    Batal
                </x-action-button>
                <x-action-button type="submit" variant="primary">
                    Simpan Perubahan
                </x-action-button>
            </div>
        </form>
    </x-section-card>
</div>
@endsection
