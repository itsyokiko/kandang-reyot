@extends('layouts.app')

@section('title', 'Detail Perkawinan')

@section('content')
<div class="space-y-4">
    <!-- Navigation & Action Header -->
    <div class="flex items-center justify-between gap-2">
        <a href="{{ route('perkawinans.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-600 transition p-1">
            <i class="ti ti-arrow-left text-sm"></i>
            <span>Kembali</span>
        </a>

        <div class="flex items-center gap-1.5">
            <x-action-button href="{{ route('kelahirans.create', ['perkawinan_id' => $perkawinan->id]) }}" variant="primary" size="sm" icon="ti ti-mood-kid">
                Catat Kelahiran
            </x-action-button>
            <button type="button" onclick="openBottomSheet('sheetEditPerkawinan')"
                    class="inline-flex items-center justify-center gap-1.5 font-bold transition active:scale-95 shadow-xs bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-xl text-xs">
                <i class="ti ti-edit"></i>
                <span>Edit</span>
            </button>
            <form action="{{ route('perkawinans.destroy', $perkawinan) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, this, 'Hapus Catatan Perkawinan?', 'Apakah Anda yakin ingin menghapus catatan perkawinan ini?');">
                @csrf
                @method('DELETE')
                <x-action-button type="submit" variant="danger" size="sm" icon="ti ti-trash">
                    Hapus
                </x-action-button>
            </form>
        </div>
    </div>

    <!-- Detail Card -->
    <x-section-card title="Detail Perkawinan" icon="ti ti-heart">
        <div class="space-y-4">
            <!-- Status & Meta -->
            <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-100">
                <span class="text-xs text-slate-500">Dicatat: {{ $perkawinan->created_at->format('d M Y, H:i') }}</span>
                <x-status-badge :status="$perkawinan->status" />
            </div>

            <!-- Pasangan Kambing -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Betina -->
                <div class="border border-pink-200 bg-pink-50/50 rounded-xl p-3.5 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-pink-100 border border-pink-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-pink-500 text-xl shadow-2xs">
                        @if ($perkawinan->betina->foto)
                            <img src="{{ asset('storage/' . $perkawinan->betina->foto) }}" alt="{{ $perkawinan->betina->nama }}" class="w-full h-full object-cover">
                        @else
                            <span>🐐</span>
                        @endif
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-[10px] font-extrabold text-pink-600 uppercase tracking-wider block">Betina (Induk)</span>
                        <a href="{{ route('kambings.show', $perkawinan->betina) }}" class="font-bold text-slate-900 hover:text-pink-600 transition text-sm truncate block">
                            ♀ {{ $perkawinan->betina->nama }}
                        </a>
                        <p class="text-[11px] text-slate-400 truncate">Ras: {{ $perkawinan->betina->ras ?? 'Tidak dicatat' }}</p>
                    </div>
                </div>

                <!-- Pejantan -->
                <div class="border border-blue-200 bg-blue-50/50 rounded-xl p-3.5 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 border border-blue-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-blue-500 text-xl shadow-2xs">
                        @if ($perkawinan->pejantan->foto)
                            <img src="{{ asset('storage/' . $perkawinan->pejantan->foto) }}" alt="{{ $perkawinan->pejantan->nama }}" class="w-full h-full object-cover">
                        @else
                            <span>🐐</span>
                        @endif
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider block">Pejantan</span>
                        <a href="{{ route('kambings.show', $perkawinan->pejantan) }}" class="font-bold text-slate-900 hover:text-blue-600 transition text-sm truncate block">
                            ♂ {{ $perkawinan->pejantan->nama }}
                        </a>
                        <p class="text-[11px] text-slate-400 truncate">Ras: {{ $perkawinan->pejantan->ras ?? 'Tidak dicatat' }}</p>
                    </div>
                </div>
            </div>

            <!-- Tanggal & Perkiraan -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tanggal Kawin</span>
                        <span class="text-sm font-bold text-slate-900 mt-0.5 block">
                            {{ $perkawinan->tanggal_kawin->format('d F Y') }}
                        </span>
                    </div>

                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-2.5">
                        <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1">
                            <i class="ti ti-calendar-event"></i>
                            <span>Perkiraan Lahir</span>
                        </span>
                        <span class="text-sm font-extrabold text-emerald-950 mt-0.5 block">
                            {{ $perkawinan->perkiraan_lahir->format('d F Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Catatan Perkawinan</span>
                <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line">
                    {{ $perkawinan->catatan ?: 'Tidak ada catatan.' }}
                </p>
            </div>
        </div>
    </x-section-card>
</div>

<!-- Bottom Sheet Edit Perkawinan -->
<x-bottom-sheet id="sheetEditPerkawinan" title="Edit Data Perkawinan">
    <form action="{{ route('perkawinans.update', $perkawinan) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <!-- Pejantan -->
        <div>
            <label for="edit_pejantan_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Kambing Pejantan (♂) <span class="text-rose-500">*</span>
            </label>
            <select name="pejantan_id" id="edit_pejantan_id" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @foreach ($pejantans as $p)
                    <option value="{{ $p->id }}" {{ old('pejantan_id', $perkawinan->pejantan_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} {{ $p->ras ? "($p->ras)" : '' }}
                    </option>
                @endforeach
            </select>
            @error('pejantan_id')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Betina -->
        <div>
            <label for="edit_betina_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Kambing Betina (♀) <span class="text-rose-500">*</span>
            </label>
            <select name="betina_id" id="edit_betina_id" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @foreach ($betinas as $b)
                    <option value="{{ $b->id }}" {{ old('betina_id', $perkawinan->betina_id) == $b->id ? 'selected' : '' }}>
                        {{ $b->nama }} {{ $b->ras ? "($b->ras)" : '' }}
                    </option>
                @endforeach
            </select>
            @error('betina_id')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanggal Kawin & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="edit_tanggal_kawin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Tanggal Kawin <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_kawin" id="edit_tanggal_kawin"
                    value="{{ old('tanggal_kawin', $perkawinan->tanggal_kawin->format('Y-m-d')) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @error('tanggal_kawin')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="edit_status_kawin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Status <span class="text-rose-500">*</span>
                </label>
                <select name="status" id="edit_status_kawin" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="menunggu" {{ old('status', $perkawinan->status) === 'menunggu' ? 'selected' : '' }}>Menunggu Hasil</option>
                    <option value="berhasil" {{ old('status', $perkawinan->status) === 'berhasil' ? 'selected' : '' }}>Berhasil Bunting</option>
                    <option value="tidak_berhasil" {{ old('status', $perkawinan->status) === 'tidak_berhasil' ? 'selected' : '' }}>Tidak Berhasil</option>
                </select>
                @error('status')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Catatan -->
        <div>
            <label for="edit_catatan_kawin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Catatan
            </label>
            <textarea name="catatan" id="edit_catatan_kawin" rows="2"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('catatan', $perkawinan->catatan) }}</textarea>
            @error('catatan')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetEditPerkawinan')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Perbarui Perkawinan
            </button>
        </div>
    </form>
</x-bottom-sheet>

@if(request('action') === 'edit' || ($errors->any() && old('_method') === 'PUT'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetEditPerkawinan');
    });
</script>
@endif
@endsection
