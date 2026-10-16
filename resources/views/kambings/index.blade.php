@extends('layouts.app')

@section('title', 'Daftar Kambing')

@section('content')
<div class="space-y-3">
    <!-- Page Title -->
    <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Daftar Kambing</h1>
        <p class="text-xs text-slate-500">Kelola populasi kambing dalam kandang</p>
    </div>

    <!-- Search & Filter Card -->
    <x-section-card>
        <form action="{{ route('kambings.index') }}" method="GET" class="space-y-3">
            <!-- Search always visible -->
            <div class="relative">
                <x-search-bar name="search" :value="request('search')" placeholder="Cari nama kambing..." />
            </div>

            <!-- Filter: collapsed by default, opens on tap -->
            <details {{ request()->hasAny(['jenis_kelamin','status','ras','tanggal_lahir_dari','tanggal_lahir_sampai']) ? 'open' : '' }}
                     class="group">
                <summary class="flex items-center justify-between text-xs font-semibold text-slate-500 hover:text-slate-700 cursor-pointer select-none py-1">
                    <span class="flex items-center gap-1.5">
                        <i class="ti ti-adjustments-horizontal"></i>
                        Filter
                        @if(request()->hasAny(['jenis_kelamin','status','ras','tanggal_lahir_dari','tanggal_lahir_sampai']))
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full">Aktif</span>
                        @endif
                    </span>
                    <i class="ti ti-chevron-down group-open:rotate-180 transition-transform"></i>
                </summary>

                <div class="pt-3 space-y-3 border-t border-slate-100 mt-2">
                    <!-- Quick Filter Grid -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="jenis_kelamin" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Kelamin</label>
                            <select name="jenis_kelamin" id="jenis_kelamin"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">Semua</option>
                                <option value="jantan" {{ request('jenis_kelamin') === 'jantan' ? 'selected' : '' }}>♂ Jantan</option>
                                <option value="betina" {{ request('jenis_kelamin') === 'betina' ? 'selected' : '' }}>♀ Betina</option>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                            <select name="status" id="status"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">Semua</option>
                                <option value="hidup" {{ request('status') === 'hidup' ? 'selected' : '' }}>Hidup</option>
                                <option value="dijual" {{ request('status') === 'dijual' ? 'selected' : '' }}>Dijual</option>
                                <option value="mati" {{ request('status') === 'mati' ? 'selected' : '' }}>Mati</option>
                            </select>
                        </div>

                        <div>
                            <label for="ras" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Ras</label>
                            <select name="ras" id="ras"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">Semua</option>
                                @foreach ($availableRas as $r)
                                    <option value="{{ $r }}" {{ request('ras') === $r ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold transition active:scale-97">
                                Terapkan
                            </button>
                        </div>
                    </div>

                    <!-- Extended: Tanggal Lahir -->
                    <details class="group/date">
                        <summary class="text-xs font-semibold text-slate-400 hover:text-slate-600 cursor-pointer flex items-center justify-between select-none">
                            <span>Filter Tanggal Lahir</span>
                            <i class="ti ti-chevron-down group-open/date:rotate-180 transition-transform text-xs"></i>
                        </summary>
                        <div class="grid grid-cols-2 gap-2 pt-3">
                            <div>
                                <label for="tanggal_lahir_dari" class="block text-[10px] font-medium text-slate-500 mb-1">Lahir Dari</label>
                                <input type="date" name="tanggal_lahir_dari" id="tanggal_lahir_dari" value="{{ request('tanggal_lahir_dari') }}"
                                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label for="tanggal_lahir_sampai" class="block text-[10px] font-medium text-slate-500 mb-1">Lahir Sampai</label>
                                <input type="date" name="tanggal_lahir_sampai" id="tanggal_lahir_sampai" value="{{ request('tanggal_lahir_sampai') }}"
                                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>
                    </details>

                    @if (request()->hasAny(['search', 'jenis_kelamin', 'status', 'ras', 'tanggal_lahir_dari', 'tanggal_lahir_sampai']))
                        <div class="flex items-center justify-between text-xs border-t border-slate-100 pt-2">
                            <span class="text-slate-500">Filter aktif</span>
                            <a href="{{ route('kambings.index') }}" class="text-emerald-600 font-semibold hover:underline">Reset</a>
                        </div>
                    @endif
                </div>
            </details>
        </form>
    </x-section-card>


    <!-- Total items info -->
    <div class="flex items-center justify-between text-xs text-slate-500 px-1">
        <span>Menampilkan <strong>{{ $kambings->total() }}</strong> kambing</span>
    </div>

    <!-- Kambing Cards List -->
    @if ($kambings->isEmpty())
        <x-empty-state
            title="Tidak ada kambing yang sesuai"
            description="Coba ubah kata kunci pencarian atau sesuaikan filter Anda."
            icon="ti ti-search"
            actionText="Reset Filter"
            actionHref="{{ route('kambings.index') }}"
        />
    @else
        <div class="space-y-3">
            @foreach ($kambings as $kambing)
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs hover:border-emerald-300 transition-all flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <!-- Avatar / Foto -->
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-xl text-emerald-600 shadow-2xs">
                                @if ($kambing->foto)
                                    <img src="{{ asset('storage/' . $kambing->foto) }}" alt="{{ $kambing->nama }}" class="w-full h-full object-cover">
                                @else
                                    <span>🐐</span>
                                @endif
                            </div>

                            <!-- Identitas -->
                            <div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('kambings.show', $kambing) }}" class="font-bold text-slate-900 hover:text-emerald-600 text-sm tracking-tight transition">
                                        {{ $kambing->nama }}
                                    </a>
                                </div>

                                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                                    <span class="font-semibold {{ $kambing->jenis_kelamin === 'jantan' ? 'text-blue-600' : 'text-pink-600' }}">
                                        {{ $kambing->jenis_kelamin === 'jantan' ? '♂ Jantan' : '♀ Betina' }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ $kambing->ras ?? 'Biasa' }}</span>
                                    @if($kambing->tanggal_lahir)
                                        <span>•</span>
                                        <span>{{ $kambing->tanggal_lahir->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <x-status-badge :status="$kambing->status" />
                    </div>

                    @if ($kambing->catatan)
                        <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100 line-clamp-2">
                            {{ $kambing->catatan }}
                        </p>
                    @endif

                    <!-- Card Footer Actions -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs font-semibold">
                        <a href="{{ route('silsilah.index', $kambing) }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-1 min-h-[36px] items-center">
                            <i class="ti ti-git-fork"></i>
                            <span>Silsilah</span>
                        </a>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('kambings.show', $kambing) }}" class="text-emerald-600 hover:text-emerald-700 p-1">
                                Detail
                            </a>
                            <a href="{{ route('kambings.edit', $kambing) }}" class="text-amber-600 hover:text-amber-700 p-1">
                                Edit
                            </a>
                            <form action="{{ route('kambings.destroy', $kambing) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, this, 'Hapus Kambing?', 'Apakah Anda yakin ingin menghapus data kambing {{ $kambing->nama }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-700 p-1">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($kambings->hasPages())
            <div class="pt-2">
                {{ $kambings->links() }}
            </div>
        @endif
    @endif
</div>

{{-- FAB: Tambah Kambing --}}
<button type="button"
        onclick="openBottomSheet('sheetTambahKambing')"
        class="fixed z-30 bottom-24 right-4 w-14 h-14 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white shadow-lg flex items-center justify-center transition-all"
        aria-label="Tambah Kambing">
    <i class="ti ti-plus text-2xl"></i>
</button>

{{-- Bottom Sheet Tambah Kambing --}}
<x-bottom-sheet id="sheetTambahKambing" title="Tambah Kambing Baru">
    <form action="{{ route('kambings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <!-- Nama Kambing -->
        <div>
            <label for="sheet_nama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Nama Kambing <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nama" id="sheet_nama" value="{{ old('nama') }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                placeholder="Contoh: Si Putih, Bandot 01">
            @error('nama')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Kelamin & Ras -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="sheet_jenis_kelamin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Jenis Kelamin <span class="text-rose-500">*</span>
                </label>
                <select name="jenis_kelamin" id="sheet_jenis_kelamin" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">-- Pilih Kelamin --</option>
                    <option value="jantan" {{ old('jenis_kelamin') === 'jantan' ? 'selected' : '' }}>♂ Jantan</option>
                    <option value="betina" {{ old('jenis_kelamin') === 'betina' ? 'selected' : '' }}>♀ Betina</option>
                </select>
                @error('jenis_kelamin')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sheet_ras" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Ras / Jenis
                </label>
                <input type="text" name="ras" id="sheet_ras" value="{{ old('ras') }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                    placeholder="Contoh: Etawa, Boer, Jawa Randu">
                @error('ras')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Tanggal Lahir & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="sheet_tanggal_lahir" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Tanggal Lahir
                </label>
                <input type="date" name="tanggal_lahir" id="sheet_tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @error('tanggal_lahir')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sheet_status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Status <span class="text-rose-500">*</span>
                </label>
                <select name="status" id="sheet_status" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="hidup" {{ old('status', 'hidup') === 'hidup' ? 'selected' : '' }}>Hidup</option>
                    <option value="mati" {{ old('status') === 'mati' ? 'selected' : '' }}>Mati</option>
                    <option value="dijual" {{ old('status') === 'dijual' ? 'selected' : '' }}>Dijual</option>
                </select>
                @error('status')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Foto -->
        <div>
            <label for="sheet_foto" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Foto Kambing
            </label>
            <input type="file" name="foto" id="sheet_foto" accept="image/*"
                class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, WEBP (Maksimal 2MB).</p>
            @error('foto')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Catatan -->
        <div>
            <label for="sheet_catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Catatan
            </label>
            <textarea name="catatan" id="sheet_catatan" rows="3"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                placeholder="Ciri fisik, kondisi khusus, dll...">{{ old('catatan') }}</textarea>
            @error('catatan')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetTambahKambing')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Simpan Kambing
            </button>
        </div>
    </form>
</x-bottom-sheet>

@if(request('action') === 'create' || ($errors->any() && !old('_method')))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetTambahKambing');
    });
</script>
@endif
@endsection
