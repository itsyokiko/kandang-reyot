@extends('layouts.app')

@section('title', 'Catatan Kelahiran')

@section('content')
<div class="space-y-4">
    <!-- Top Action Header -->
    <div class="flex items-center justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Catatan Kelahiran</h1>
            <p class="text-xs text-slate-500">Peristiwa kelahiran & anak yang lahir</p>
        </div>
        <button type="button" onclick="openBottomSheet('sheetTambahKelahiran')"
                class="inline-flex items-center justify-center gap-1.5 font-bold transition active:scale-95 shadow-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs">
            <i class="ti ti-plus"></i>
            <span>Catat</span>
        </button>
    </div>

    @if ($kelahirans->isEmpty())
        <div class="p-6 text-center bg-white rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                <i class="ti ti-mood-kid"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-800">Belum ada catatan kelahiran</h3>
            <p class="text-xs text-slate-500 max-w-xs mx-auto">Catat peristiwa kelahiran ketika induk kambing melahirkan anak.</p>
            <button type="button" onclick="openBottomSheet('sheetTambahKelahiran')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95">
                + Catat Kelahiran Pertama
            </button>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($kelahirans as $kelahiran)
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs hover:border-emerald-300 transition-all flex flex-col gap-3">
                    <!-- Header -->
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-1.5 text-xs text-slate-900 font-bold">
                            <i class="ti ti-calendar text-emerald-600"></i>
                            <span>{{ $kelahiran->tanggal_kelahiran->format('d M Y') }}</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            {{ $kelahiran->anaks->count() }} Ekor Anak
                        </span>
                    </div>

                    <!-- Pasangan Induk & Pejantan -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-2 bg-pink-50/60 border border-pink-100 rounded-xl text-xs">
                            <span class="text-[10px] font-extrabold text-pink-600 uppercase tracking-wider block">Induk Betina</span>
                            <a href="{{ route('kambings.show', $kelahiran->induk) }}" class="font-bold text-slate-900 hover:text-pink-600 truncate block mt-0.5">
                                ♀ {{ $kelahiran->induk->nama }}
                            </a>
                        </div>

                        <div class="p-2 bg-blue-50/60 border border-blue-100 rounded-xl text-xs">
                            <span class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider block">Pejantan</span>
                            <a href="{{ route('kambings.show', $kelahiran->pejantan) }}" class="font-bold text-slate-900 hover:text-blue-600 truncate block mt-0.5">
                                ♂ {{ $kelahiran->pejantan->nama }}
                            </a>
                        </div>
                    </div>

                    <!-- Daftar Anak -->
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Anak yang Lahir:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($kelahiran->anaks as $anak)
                                <a href="{{ route('kambings.show', $anak) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-semibold {{ $anak->jenis_kelamin === 'jantan' ? 'bg-blue-50 text-blue-800 border border-blue-200' : 'bg-pink-50 text-pink-800 border border-pink-200' }} hover:opacity-80 transition">
                                    <span>{{ $anak->jenis_kelamin === 'jantan' ? '♂' : '♀' }}</span>
                                    <span>{{ $anak->nama }}</span>
                                </a>
                            @empty
                                <span class="text-xs text-slate-400 italic">Tidak ada data anak</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs font-semibold">
                        <div>
                            @if ($kelahiran->perkawinan)
                                <a href="{{ route('perkawinans.show', $kelahiran->perkawinan) }}" class="text-slate-500 hover:text-emerald-600 text-[11px] flex items-center gap-1">
                                    <i class="ti ti-link"></i>
                                    <span>Dari Kawin {{ $kelahiran->perkawinan->tanggal_kawin->format('d/m/Y') }}</span>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('kelahirans.show', $kelahiran) }}" class="text-emerald-600 hover:text-emerald-700 p-1">
                                Detail
                            </a>
                            <a href="{{ route('kelahirans.edit', $kelahiran) }}" class="text-amber-600 hover:text-amber-700 p-1">
                                Edit
                            </a>
                            <form action="{{ route('kelahirans.destroy', $kelahiran) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, this, 'Hapus Catatan Kelahiran?', 'Apakah Anda yakin ingin menghapus catatan kelahiran ini? (Data anak tidak akan dihapus, hanya tautan kelahirannya yang dilepas)');">
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
    @endif
</div>

<!-- Bottom Sheet Catat Kelahiran -->
<x-bottom-sheet id="sheetTambahKelahiran" title="Catat Kelahiran Baru">
    <form action="{{ route('kelahirans.store') }}" method="POST" id="form-kelahiran-sheet" class="space-y-4">
        @csrf

        <!-- Pilihan Dari Riwayat Perkawinan -->
        <div class="bg-emerald-50/70 border border-emerald-200 rounded-xl p-3.5 space-y-2">
            <label for="sheet_perkawinan_id" class="block text-xs font-bold text-emerald-950 uppercase tracking-wider">
                Pilih Dari Riwayat Perkawinan <span class="text-[10px] font-normal text-emerald-700">(Rekomendasi)</span>
            </label>
            <select name="perkawinan_id" id="sheet_perkawinan_id"
                class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                <option value="">-- Tanpa Perkawinan Tercatat (Pilih Manual) --</option>
                @foreach ($perkawinans as $p)
                    <option value="{{ $p->id }}"
                        data-betina="{{ $p->betina_id }}"
                        data-pejantan="{{ $p->pejantan_id }}"
                        {{ old('perkawinan_id') == $p->id ? 'selected' : '' }}>
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
                <label for="sheet_induk_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Induk Betina <span class="text-rose-500">*</span>
                </label>
                <select name="induk_id" id="sheet_induk_id" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">-- Pilih Induk --</option>
                    @foreach ($betinas as $b)
                        <option value="{{ $b->id }}" {{ old('induk_id') == $b->id ? 'selected' : '' }}>
                            ♀ {{ $b->nama }} {{ $b->ras ? "($b->ras)" : '' }}
                        </option>
                    @endforeach
                </select>
                @error('induk_id')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sheet_pejantan_kelahiran_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Pejantan <span class="text-rose-500">*</span>
                </label>
                <select name="pejantan_id" id="sheet_pejantan_kelahiran_id" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">-- Pilih Pejantan --</option>
                    @foreach ($pejantans as $p)
                        <option value="{{ $p->id }}" {{ old('pejantan_id') == $p->id ? 'selected' : '' }}>
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
            <label for="sheet_tanggal_kelahiran" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Tanggal Kelahiran <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal_kelahiran" id="sheet_tanggal_kelahiran" value="{{ old('tanggal_kelahiran', date('Y-m-d')) }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            @error('tanggal_kelahiran')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Section Anak Kambing -->
        <div class="border-t border-slate-100 pt-3 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Data Anak</h4>
                    <p class="text-[11px] text-slate-500">Otomatis terdaftar ke data kambing</p>
                </div>
                <button type="button" id="btn-tambah-anak-sheet"
                    class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 font-bold px-2.5 py-1.5 rounded-xl text-xs transition active:scale-95">
                    <i class="ti ti-plus text-xs"></i>
                    <span>+ Anak</span>
                </button>
            </div>

            <div id="container-anak-sheet" class="space-y-3">
                <div class="item-anak-sheet bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-3">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-200/60">
                        <span class="text-xs font-extrabold text-slate-700 uppercase judul-anak-sheet">Anak #1</span>
                        <button type="button" class="btn-hapus-anak-sheet text-xs font-semibold text-rose-600 hover:text-rose-800 hidden">
                            Hapus
                        </button>
                    </div>
                    <!-- Row 1: Nama & Kelamin -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Anak <span class="text-rose-500">*</span></label>
                            <input type="text" name="anak[0][nama]" required placeholder="Contoh: Cempe 01"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                            <select name="anak[0][jenis_kelamin]" required
                                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                                <option value="jantan">♂ Jantan</option>
                                <option value="betina">♀ Betina</option>
                            </select>
                        </div>
                    </div>
                    <!-- Row 2: Tanggal Lahir & Bapak (Pejantan) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Lahir</label>
                            <input type="date" name="anak[0][tanggal_lahir]"
                                   class="input-tgl-anak w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                            <span class="text-[10px] text-slate-400">Otomatis ikuti tanggal kelahiran jika kosong</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Bapak / Pejantan</label>
                            <select name="anak[0][pejantan_id]"
                                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                                <option value="">-- Ikuti Pejantan di Atas --</option>
                                @foreach ($pejantans as $p)
                                    <option value="{{ $p->id }}">♂ {{ $p->nama }} {{ $p->ras ? "($p->ras)" : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- Row 3: Ras & Catatan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Ras (Opsional)</label>
                            <input type="text" name="anak[0][ras]" placeholder="Ikuti induk jika kosong"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Catatan</label>
                            <input type="text" name="anak[0][catatan]" placeholder="Ciri, bobot lahir, dll"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetTambahKelahiran')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Simpan Kelahiran & Anak
            </button>
        </div>
    </form>
</x-bottom-sheet>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const perkawinanSelect = document.getElementById('sheet_perkawinan_id');
        const indukSelect = document.getElementById('sheet_induk_id');
        const pejantanSelect = document.getElementById('sheet_pejantan_kelahiran_id');

        if (perkawinanSelect) {
            perkawinanSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.value) {
                    const betinaId = opt.getAttribute('data-betina');
                    const pejantanId = opt.getAttribute('data-pejantan');
                    if (betinaId && indukSelect) indukSelect.value = betinaId;
                    if (pejantanId && pejantanSelect) pejantanSelect.value = pejantanId;
                }
            });
        }

        const container = document.getElementById('container-anak-sheet');
        const btnTambah = document.getElementById('btn-tambah-anak-sheet');

        function updateAnakNumbers() {
            if (!container) return;
            const items = container.querySelectorAll('.item-anak-sheet');
            items.forEach((item, index) => {
                const judul = item.querySelector('.judul-anak-sheet');
                if (judul) judul.textContent = 'Anak #' + (index + 1);

                item.querySelectorAll('input, select').forEach(field => {
                    const name = field.getAttribute('name');
                    if (name) {
                        field.setAttribute('name', name.replace(/anak\[\d+\]/, 'anak[' + index + ']'));
                    }
                });

                const btnHapus = item.querySelector('.btn-hapus-anak-sheet');
                if (btnHapus) {
                    btnHapus.classList.toggle('hidden', items.length <= 1);
                }
            });
        }

        if (btnTambah && container) {
            btnTambah.addEventListener('click', function() {
                const items = container.querySelectorAll('.item-anak-sheet');
                const newIndex = items.length;
                const template = items[0].cloneNode(true);

                template.querySelectorAll('input').forEach(i => i.value = '');
                template.querySelectorAll('select').forEach(s => s.selectedIndex = 0);

                container.appendChild(template);
                updateAnakNumbers();
            });

            container.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('btn-hapus-anak-sheet')) {
                    const items = container.querySelectorAll('.item-anak-sheet');
                    if (items.length > 1) {
                        e.target.closest('.item-anak-sheet').remove();
                        updateAnakNumbers();
                    }
                }
            });
        }
    });
</script>

@if(request('action') === 'create' || ($errors->any() && !old('_method')))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetTambahKelahiran');
    });
</script>
@endif
@endsection
