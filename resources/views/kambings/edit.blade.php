@extends('layouts.app')

@section('title', 'Edit Kambing - ' . $kambing->nama)

@section('content')
<div class="space-y-4">
    <!-- Back Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('kambings.show', $kambing) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-600 transition p-1">
            <i class="ti ti-arrow-left text-sm"></i>
            <span>Kembali ke Detail</span>
        </a>
    </div>

    <x-section-card title="Edit Data Kambing: {{ $kambing->nama }}" icon="ti ti-edit">
        <form action="{{ route('kambings.update', $kambing) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Nama Kambing -->
            <div>
                <label for="nama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Nama Kambing <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $kambing->nama) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @error('nama')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jenis Kelamin & Ras -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="jenis_kelamin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Jenis Kelamin <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenis_kelamin" id="jenis_kelamin" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="jantan" {{ old('jenis_kelamin', $kambing->jenis_kelamin) === 'jantan' ? 'selected' : '' }}>♂ Jantan</option>
                        <option value="betina" {{ old('jenis_kelamin', $kambing->jenis_kelamin) === 'betina' ? 'selected' : '' }}>♀ Betina</option>
                    </select>
                    @error('jenis_kelamin')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ras" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Ras / Jenis Kambing
                    </label>
                    <input type="text" name="ras" id="ras" list="list-ras" value="{{ old('ras', $kambing->ras) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                        placeholder="Pilih atau ketik ras (Etawa, Boer, Jawa Randu...)">
                    @error('ras')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tanggal Lahir & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="tanggal_lahir" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Tanggal Lahir
                    </label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                        value="{{ old('tanggal_lahir', $kambing->tanggal_lahir ? $kambing->tanggal_lahir->format('Y-m-d') : '') }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    @error('tanggal_lahir')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Status <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" id="status" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        <option value="hidup" {{ old('status', $kambing->status) === 'hidup' ? 'selected' : '' }}>Hidup</option>
                        <option value="mati" {{ old('status', $kambing->status) === 'mati' ? 'selected' : '' }}>Mati</option>
                        <option value="dijual" {{ old('status', $kambing->status) === 'dijual' ? 'selected' : '' }}>Dijual</option>
                    </select>
                    @error('status')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Foto -->
            <div>
                <label for="foto" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Ganti Foto Kambing
                </label>
                <div id="container-preview-edit" class="hidden mb-2">
                    <div class="relative inline-block w-20 h-20 rounded-2xl overflow-hidden border-2 border-emerald-500 shadow-sm bg-slate-50">
                        <img id="preview-foto-edit" src="" alt="Preview Foto Baru" class="w-full h-full object-cover">
                        <button type="button" onclick="clearFotoUpload('foto', 'container-preview-edit', 'preview-foto-edit')"
                                class="absolute top-1 right-1 bg-black/60 hover:bg-black/80 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                            <i class="ti ti-x text-[10px]"></i>
                        </button>
                    </div>
                </div>
                @if ($kambing->foto)
                    <div class="flex items-center gap-3 mb-2 p-2 border border-slate-200 rounded-xl bg-slate-50">
                        <img src="{{ asset('storage/' . $kambing->foto) }}" alt="{{ $kambing->nama }}" class="w-12 h-12 object-cover rounded-lg border border-slate-300">
                        <span class="text-xs text-slate-500">Foto saat ini. Pilih file baru jika ingin mengganti.</span>
                    </div>
                @endif
                <input type="file" name="foto" id="foto" accept="image/*"
                    data-preview="#preview-foto-edit"
                    data-preview-container="#container-preview-edit"
                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, WEBP, HEIC (Maksimal 15MB. Otomatis dioptimalkan).</p>
                @error('foto')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan -->
            <div>
                <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Catatan
                </label>
                <textarea name="catatan" id="catatan" rows="3"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('catatan', $kambing->catatan) }}</textarea>
                @error('catatan')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <x-action-button href="{{ route('kambings.show', $kambing) }}" variant="secondary">
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
