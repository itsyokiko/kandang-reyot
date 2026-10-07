@extends('layouts.app')

@section('title', 'Edit Perkawinan')

@section('content')
<div class="space-y-4">
    <!-- Back Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('perkawinans.show', $perkawinan) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-600 transition p-1">
            <i class="ti ti-arrow-left text-sm"></i>
            <span>Kembali ke Detail</span>
        </a>
    </div>

    <x-section-card title="Edit Catatan Perkawinan" icon="ti ti-edit">
        <form action="{{ route('perkawinans.update', $perkawinan) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Betina -->
                <div>
                    <label for="betina_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Kambing Betina (Induk) <span class="text-rose-500">*</span>
                    </label>
                    <select name="betina_id" id="betina_id" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        @foreach ($betinas as $betina)
                            <option value="{{ $betina->id }}" {{ old('betina_id', $perkawinan->betina_id) == $betina->id ? 'selected' : '' }}>
                                ♀ {{ $betina->nama }} {{ $betina->ras ? '(' . $betina->ras . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('betina_id')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pejantan -->
                <div>
                    <label for="pejantan_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Pejantan <span class="text-rose-500">*</span>
                    </label>
                    <select name="pejantan_id" id="pejantan_id" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        @foreach ($pejantans as $pejantan)
                            <option value="{{ $pejantan->id }}" {{ old('pejantan_id', $perkawinan->pejantan_id) == $pejantan->id ? 'selected' : '' }}>
                                ♂ {{ $pejantan->nama }} {{ $pejantan->ras ? '(' . $pejantan->ras . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('pejantan_id')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Tanggal Kawin -->
                <div>
                    <label for="tanggal_kawin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Tanggal Kawin <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_kawin" id="tanggal_kawin"
                        value="{{ old('tanggal_kawin', $perkawinan->tanggal_kawin->format('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <p class="text-[11px] text-slate-400 mt-1">Perkiraan lahir dihitung ±150 hari setelahnya.</p>
                    @error('tanggal_kawin')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Hasil Perkawinan <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" id="status" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        <option value="menunggu" {{ old('status', $perkawinan->status) === 'menunggu' ? 'selected' : '' }}>Menunggu Hasil</option>
                        <option value="berhasil" {{ old('status', $perkawinan->status) === 'berhasil' ? 'selected' : '' }}>Berhasil (Bunting)</option>
                        <option value="tidak_berhasil" {{ old('status', $perkawinan->status) === 'tidak_berhasil' ? 'selected' : '' }}>Tidak Berhasil</option>
                    </select>
                    @error('status')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Catatan -->
            <div>
                <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Catatan
                </label>
                <textarea name="catatan" id="catatan" rows="3"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('catatan', $perkawinan->catatan) }}</textarea>
                @error('catatan')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <x-action-button href="{{ route('perkawinans.show', $perkawinan) }}" variant="secondary">
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
