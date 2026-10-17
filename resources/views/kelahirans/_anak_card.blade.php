{{--
    Partial: _anak_card
    Digunakan oleh kelahirans/create.blade.php (via @include dan <template>).
    Variabel: $index (int|string), $item (array, bisa kosong)
--}}
@php
    $today = date('Y-m-d');
    $tglVal = $item['tanggal_lahir'] ?? $today;
    $isFirst = $index === 0;
@endphp

<div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
    <span class="text-xs font-extrabold text-slate-700 uppercase judul-anak">Anak #{{ is_int($index) ? $index + 1 : 1 }}</span>
    <button type="button" class="btn-hapus-anak text-xs font-semibold text-rose-600 hover:text-rose-800 {{ $isFirst ? 'hidden' : '' }}">
        Hapus
    </button>
</div>

{{-- Hidden: menyimpan id kambing existing (kosong = anak baru) --}}
<input type="hidden" name="anak[{{ $index }}][kambing_id]" value="{{ $item['kambing_id'] ?? '' }}">

{{-- Toggle Mode --}}
<div class="grid grid-cols-2 gap-2">
    <button type="button"
        class="btn-mode btn-mode-existing py-2 rounded-xl text-xs font-bold border border-slate-300 bg-white text-slate-600 hover:border-emerald-400 hover:text-emerald-700 transition">
        🐐 Kambing Ada
    </button>
    <button type="button"
        class="btn-mode btn-mode-new py-2 rounded-xl text-xs font-bold border border-emerald-400 bg-emerald-50 text-emerald-700 transition">
        ✏️ Input Baru
    </button>
</div>

{{-- Mode: Kambing Existing (hidden by default) --}}
<div class="mode-existing hidden">
    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Cari Nama Kambing</label>
    <div class="relative">
        <input type="text"
            class="ac-search w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
            placeholder="Ketik nama kambing...">
        <ul class="ac-dropdown absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-lg mt-1 hidden max-h-48 overflow-y-auto text-xs"></ul>
    </div>
    <div class="ac-selected hidden mt-2 p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs space-y-0.5">
        <p class="font-bold text-emerald-800 ac-nama"></p>
        <p class="text-emerald-600 ac-detail"></p>
    </div>
</div>

{{-- Mode: Input Baru (default) --}}
<div class="mode-new">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                Nama Anak <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="anak[{{ $index }}][nama]"
                value="{{ $item['nama'] ?? '' }}"
                required
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                placeholder="Contoh: Bimo">
            @error("anak.{$index}.nama")
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                Jenis Kelamin <span class="text-rose-500">*</span>
            </label>
            <select name="anak[{{ $index }}][jenis_kelamin]" required
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="jantan" {{ ($item['jenis_kelamin'] ?? '') === 'jantan' ? 'selected' : '' }}>♂ Jantan</option>
                <option value="betina" {{ ($item['jenis_kelamin'] ?? '') === 'betina' ? 'selected' : '' }}>♀ Betina</option>
            </select>
            @error("anak.{$index}.jenis_kelamin")
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div class="mt-3">
        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Ras (Opsional)</label>
        <input type="text" name="anak[{{ $index }}][ras]"
            value="{{ $item['ras'] ?? '' }}"
            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
            placeholder="Ikuti ras induk jika kosong">
    </div>
</div>

{{-- Tanggal Lahir — selalu tampil, selalu required --}}
<div>
    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
        Tanggal Lahir <span class="text-rose-500">*</span>
    </label>
    <input type="date" name="anak[{{ $index }}][tanggal_lahir]"
        value="{{ $tglVal }}"
        required
        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
    <span class="text-[10px] text-slate-400">Anak dengan tanggal berbeda dicatat sebagai kelahiran terpisah</span>
    @error("anak.{$index}.tanggal_lahir")
        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
    @enderror
</div>

{{-- Catatan --}}
<div>
    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Catatan Anak</label>
    <input type="text" name="anak[{{ $index }}][catatan]"
        value="{{ $item['catatan'] ?? '' }}"
        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
        placeholder="Kondisi fisik saat lahir...">
</div>
