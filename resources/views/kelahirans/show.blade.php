@extends('layouts.app')

@section('title', 'Detail Kelahiran')

@section('content')
<div class="space-y-4">
    <!-- Back Header & Actions -->
    <div class="flex items-center justify-between gap-2">
        <a href="{{ route('kelahirans.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-600 transition p-1">
            <i class="ti ti-arrow-left text-sm"></i>
            <span>Kembali</span>
        </a>

        <div class="flex items-center gap-1.5">
            <button type="button" onclick="openBottomSheet('sheetEditKelahiran')"
                    class="inline-flex items-center justify-center gap-1.5 font-bold transition active:scale-95 shadow-xs bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-xl text-xs">
                <i class="ti ti-edit"></i>
                <span>Edit</span>
            </button>
            <form action="{{ route('kelahirans.destroy', $kelahiran) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, this, 'Hapus Catatan Kelahiran?', 'Apakah Anda yakin ingin menghapus catatan kelahiran ini? Data anak kambing tetap aman.');">
                @csrf
                @method('DELETE')
                <x-action-button type="submit" variant="danger" size="sm" icon="ti ti-trash">
                    Hapus
                </x-action-button>
            </form>
        </div>
    </div>

    <!-- Detail Card -->
    <x-section-card title="Peristiwa Kelahiran" icon="ti ti-mood-kid">
        <div class="space-y-4">
            <!-- Header Meta -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tanggal Kelahiran</span>
                    <span class="text-sm font-extrabold text-slate-900 mt-0.5 block">
                        {{ $kelahiran->tanggal_kelahiran->format('d F Y') }}
                    </span>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                    {{ $kelahiran->anaks->count() }} Ekor Anak
                </span>
            </div>

            <!-- Pasangan Orang Tua -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Induk -->
                <div class="border border-pink-200 bg-pink-50/50 rounded-xl p-3.5 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-pink-100 border border-pink-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-pink-500 text-xl shadow-2xs">
                        @if ($kelahiran->induk->foto)
                            <img src="{{ asset('storage/' . $kelahiran->induk->foto) }}" alt="{{ $kelahiran->induk->nama }}" class="w-full h-full object-cover">
                        @else
                            <span>🐐</span>
                        @endif
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-[10px] font-extrabold text-pink-600 uppercase tracking-wider block">Induk Betina</span>
                        <a href="{{ route('kambings.show', $kelahiran->induk) }}" class="font-bold text-slate-900 hover:text-pink-600 transition text-sm truncate block">
                            ♀ {{ $kelahiran->induk->nama }}
                        </a>
                        <p class="text-[11px] text-slate-400 truncate">Ras: {{ $kelahiran->induk->ras ?? 'Tidak dicatat' }}</p>
                    </div>
                </div>

                <!-- Pejantan -->
                <div class="border border-blue-200 bg-blue-50/50 rounded-xl p-3.5 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 border border-blue-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-blue-500 text-xl shadow-2xs">
                        @if ($kelahiran->pejantan->foto)
                            <img src="{{ asset('storage/' . $kelahiran->pejantan->foto) }}" alt="{{ $kelahiran->pejantan->nama }}" class="w-full h-full object-cover">
                        @else
                            <span>🐐</span>
                        @endif
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider block">Pejantan</span>
                        <a href="{{ route('kambings.show', $kelahiran->pejantan) }}" class="font-bold text-slate-900 hover:text-blue-600 transition text-sm truncate block">
                            ♂ {{ $kelahiran->pejantan->nama }}
                        </a>
                        <p class="text-[11px] text-slate-400 truncate">Ras: {{ $kelahiran->pejantan->ras ?? 'Tidak dicatat' }}</p>
                    </div>
                </div>
            </div>

            @if ($kelahiran->perkawinan)
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Berasal Dari Perkawinan</span>
                        <span class="font-semibold text-slate-800">
                            Kawin: {{ $kelahiran->perkawinan->tanggal_kawin->format('d M Y') }}
                        </span>
                    </div>
                    <a href="{{ route('perkawinans.show', $kelahiran->perkawinan) }}" class="font-semibold text-emerald-600 hover:underline">
                        Lihat Perkawinan &rarr;
                    </a>
                </div>
            @endif

            <!-- Daftar Anak -->
            <div class="space-y-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Anak yang Dilahirkan</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($kelahiran->anaks as $anak)
                        <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 flex items-center justify-between hover:border-emerald-300 transition">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-slate-200 flex items-center justify-center text-sm">
                                    🐐
                                </div>
                                <div>
                                    <a href="{{ route('kambings.show', $anak) }}" class="font-bold text-slate-900 hover:text-emerald-600 text-xs block">
                                        {{ $anak->nama }}
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5 text-[10px]">
                                        <span class="font-semibold {{ $anak->jenis_kelamin === 'jantan' ? 'text-blue-600' : 'text-pink-600' }}">
                                            {{ $anak->jenis_kelamin === 'jantan' ? '♂ Jantan' : '♀ Betina' }}
                                        </span>
                                        @if ($anak->ras)
                                            <span class="text-slate-400">• {{ $anak->ras }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('kambings.show', $anak) }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                                Profil &rarr;
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Catatan -->
            @if ($kelahiran->catatan)
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Catatan Peristiwa</span>
                    <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line">
                        {{ $kelahiran->catatan }}
                    </p>
                </div>
            @endif
        </div>
    </x-section-card>
</div>

<!-- Bottom Sheet Edit Catatan Kelahiran -->
<x-bottom-sheet id="sheetEditKelahiran" title="Edit Catatan Kelahiran">
    <form action="{{ route('kelahirans.update', $kelahiran) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <!-- Pilihan Perkawinan (Opsional) -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-1.5">
            <label for="edit_perkawinan_id" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                Referensi Perkawinan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
            </label>
            <select name="perkawinan_id" id="edit_perkawinan_id"
                class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                <option value="">-- Tanpa Referensi Perkawinan --</option>
                @foreach ($perkawinans as $p)
                    <option value="{{ $p->id }}" {{ old('perkawinan_id', $kelahiran->perkawinan_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->betina->nama }} &times; {{ $p->pejantan->nama }} (Kawin: {{ $p->tanggal_kawin->format('d M Y') }})
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
                <label for="edit_induk_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Induk Betina <span class="text-rose-500">*</span>
                </label>
                <select name="induk_id" id="edit_induk_id" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    @foreach ($betinas as $b)
                        <option value="{{ $b->id }}" {{ old('induk_id', $kelahiran->induk_id) == $b->id ? 'selected' : '' }}>
                            ♀ {{ $b->nama }} {{ $b->ras ? "($b->ras)" : '' }}
                        </option>
                    @endforeach
                </select>
                @error('induk_id')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="edit_pejantan_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Pejantan <span class="text-rose-500">*</span>
                </label>
                <select name="pejantan_id" id="edit_pejantan_id" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    @foreach ($pejantans as $p)
                        <option value="{{ $p->id }}" {{ old('pejantan_id', $kelahiran->pejantan_id) == $p->id ? 'selected' : '' }}>
                            ♂ {{ $p->nama }} {{ $p->ras ? "($p->ras)" : '' }}
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
            <label for="edit_tanggal_kelahiran" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Tanggal Kelahiran <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal_kelahiran" id="edit_tanggal_kelahiran"
                value="{{ old('tanggal_kelahiran', $kelahiran->tanggal_kelahiran->format('Y-m-d')) }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            @error('tanggal_kelahiran')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Catatan -->
        <div>
            <label for="edit_catatan_kelahiran" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Catatan Peristiwa
            </label>
            <textarea name="catatan" id="edit_catatan_kelahiran" rows="3"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('catatan', $kelahiran->catatan) }}</textarea>
            @error('catatan')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetEditKelahiran')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Perbarui Kelahiran
            </button>
        </div>
    </form>
</x-bottom-sheet>

@if(request('action') === 'edit' || ($errors->any() && old('_method') === 'PUT'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetEditKelahiran');
    });
</script>
@endif
@endsection
