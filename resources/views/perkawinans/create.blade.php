@extends('layouts.app')

@section('title', 'Catat Perkawinan')

@section('content')
<div class="space-y-4">
    <!-- Back Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('perkawinans.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-600 transition p-1">
            <i class="ti ti-arrow-left text-sm"></i>
            <span>Kembali ke Riwayat</span>
        </a>
    </div>

    @if ($betinas->isEmpty() || $pejantans->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-amber-900 space-y-3">
            <h3 class="font-bold text-sm flex items-center gap-1.5">
                <i class="ti ti-alert-circle text-base text-amber-600"></i>
                <span>Data Kambing Belum Cukup</span>
            </h3>
            <p class="text-xs leading-relaxed text-amber-800">
                Untuk mencatat perkawinan, peternakan harus memiliki minimal 1 ekor kambing berjenis kelamin <strong>betina</strong> dan 1 ekor <strong>jantan</strong>.
            </p>
            <div class="flex flex-wrap gap-2 pt-1">
                @if ($betinas->isEmpty())
                    <span class="text-[11px] bg-rose-100 text-rose-800 px-2.5 py-1 rounded-full font-semibold">Belum ada kambing betina</span>
                @endif
                @if ($pejantans->isEmpty())
                    <span class="text-[11px] bg-rose-100 text-rose-800 px-2.5 py-1 rounded-full font-semibold">Belum ada pejantan</span>
                @endif
            </div>
            <div class="pt-2">
                <x-action-button href="{{ route('kambings.create') }}" variant="amber" size="sm" icon="ti ti-plus">
                    Tambah Data Kambing
                </x-action-button>
            </div>
        </div>
    @else
        <x-section-card title="Catat Perkawinan Baru" icon="ti ti-heart">
            <form action="{{ route('perkawinans.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Betina -->
                    <div>
                        <label for="betina_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Kambing Betina (Induk) <span class="text-rose-500">*</span>
                        </label>
                        <select name="betina_id" id="betina_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                            <option value="">-- Pilih Betina --</option>
                            @foreach ($betinas as $betina)
                                <option value="{{ $betina->id }}" {{ old('betina_id', $selectedBetinaId) == $betina->id ? 'selected' : '' }}>
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
                            <option value="">-- Pilih Pejantan --</option>
                            @foreach ($pejantans as $pejantan)
                                <option value="{{ $pejantan->id }}" {{ old('pejantan_id', $selectedPejantanId) == $pejantan->id ? 'selected' : '' }}>
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
                        <input type="date" name="tanggal_kawin" id="tanggal_kawin" value="{{ old('tanggal_kawin', date('Y-m-d')) }}" required
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
                            <option value="menunggu" {{ old('status', 'menunggu') === 'menunggu' ? 'selected' : '' }}>Menunggu Hasil</option>
                            <option value="berhasil" {{ old('status') === 'berhasil' ? 'selected' : '' }}>Berhasil (Bunting)</option>
                            <option value="tidak_berhasil" {{ old('status') === 'tidak_berhasil' ? 'selected' : '' }}>Tidak Berhasil</option>
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
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                        placeholder="Metode kawin, kondisi kambing, atau catatan penting...">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <x-action-button href="{{ route('perkawinans.index') }}" variant="secondary">
                        Batal
                    </x-action-button>
                    <x-action-button type="submit" variant="primary">
                        Simpan Perkawinan
                    </x-action-button>
                </div>
            </form>
        </x-section-card>
    @endif
</div>
@endsection
