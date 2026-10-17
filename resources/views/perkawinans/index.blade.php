@extends('layouts.app')

@section('title', 'Riwayat Perkawinan')

@section('content')
<div class="space-y-4">
    <!-- Top Action Header -->
    <div class="flex items-center justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Riwayat Perkawinan</h1>
            <p class="text-xs text-slate-500">Catatan perkawinan & perkiraan lahir</p>
        </div>
        <button type="button" onclick="openBottomSheet('sheetTambahPerkawinan')"
                class="inline-flex items-center justify-center gap-1.5 font-bold transition active:scale-95 shadow-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs">
            <i class="ti ti-plus"></i>
            <span>Catat</span>
        </button>
    </div>

    @if ($perkawinans->isEmpty())
        <div class="p-6 text-center bg-white rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                <i class="ti ti-heart"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-800">Belum ada catatan perkawinan</h3>
            <p class="text-xs text-slate-500 max-w-xs mx-auto">Mulai catat perkawinan kambing betina dan pejantan untuk memantau reproduksi.</p>
            <button type="button" onclick="openBottomSheet('sheetTambahPerkawinan')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95">
                + Catat Perkawinan Pertama
            </button>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($perkawinans as $perkawinan)
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs hover:border-emerald-300 transition-all flex flex-col gap-3">
                    <!-- Status & Header -->
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <i class="ti ti-calendar text-slate-400"></i>
                            <span>Kawin: <strong>{{ $perkawinan->tanggal_kawin->format('d M Y') }}</strong></span>
                        </div>
                        <x-status-badge :status="$perkawinan->status" />
                    </div>

                    <!-- Pasangan Kambing Grid -->
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Betina -->
                        <div class="p-2.5 bg-pink-50/60 border border-pink-100 rounded-xl">
                            <span class="text-[10px] font-extrabold text-pink-600 uppercase tracking-wider block">Betina</span>
                            <a href="{{ route('kambings.show', $perkawinan->betina) }}" class="font-bold text-slate-900 hover:text-pink-600 text-xs block truncate mt-0.5">
                                ♀ {{ $perkawinan->betina->nama }}
                            </a>
                            <span class="text-[10px] text-slate-400 block truncate">{{ $perkawinan->betina->ras ?? 'Biasa' }}</span>
                        </div>

                        <!-- Pejantan -->
                        <div class="p-2.5 bg-blue-50/60 border border-blue-100 rounded-xl">
                            <span class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider block">Pejantan</span>
                            <a href="{{ route('kambings.show', $perkawinan->pejantan) }}" class="font-bold text-slate-900 hover:text-blue-600 text-xs block truncate mt-0.5">
                                ♂ {{ $perkawinan->pejantan->nama }}
                            </a>
                            <span class="text-[10px] text-slate-400 block truncate">{{ $perkawinan->pejantan->ras ?? 'Biasa' }}</span>
                        </div>
                    </div>

                    <!-- Estimasi Tanggal Lahir -->
                    <div class="bg-emerald-50/50 border border-emerald-100 p-2.5 rounded-xl flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5 text-emerald-800">
                            <i class="ti ti-calendar-event text-emerald-600"></i>
                            <span>Perkiraan Lahir: <strong>{{ $perkawinan->perkiraan_lahir->format('d M Y') }}</strong></span>
                        </div>
                        <span class="text-[10px] text-emerald-600 font-medium">±150 hari</span>
                    </div>

                    <!-- Card Actions -->
                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 text-xs font-semibold">
                        <a href="{{ route('perkawinans.show', $perkawinan) }}" class="text-emerald-600 hover:text-emerald-700 p-1">
                            Detail
                        </a>
                        <a href="{{ route('perkawinans.edit', $perkawinan) }}" class="text-amber-600 hover:text-amber-700 p-1">
                            Edit
                        </a>
                        <form action="{{ route('perkawinans.destroy', $perkawinan) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, this, 'Hapus Catatan Perkawinan?', 'Apakah Anda yakin ingin menghapus catatan perkawinan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-700 p-1">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Bottom Sheet Catat Perkawinan -->
<x-bottom-sheet id="sheetTambahPerkawinan" title="Catat Perkawinan Baru">
    <form action="{{ route('perkawinans.store') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Pejantan -->
        <div>
            <label for="sheet_pejantan_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Kambing Pejantan (♂) <span class="text-rose-500">*</span>
            </label>
            <select name="pejantan_id" id="sheet_pejantan_id" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                <option value="">-- Pilih Pejantan --</option>
                @foreach ($pejantans as $p)
                    <option value="{{ $p->id }}" {{ old('pejantan_id') == $p->id ? 'selected' : '' }}>
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
            <label for="sheet_betina_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Kambing Betina (♀) <span class="text-rose-500">*</span>
            </label>
            <select name="betina_id" id="sheet_betina_id" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                <option value="">-- Pilih Betina --</option>
                @foreach ($betinas as $b)
                    <option value="{{ $b->id }}" {{ old('betina_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->nama }} {{ $b->ras ? "($b->ras)" : '' }}
                    </option>
                @endforeach
            </select>
            @error('betina_id')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Inbreeding Warning Alert Box -->
        <div id="sheet_inbreeding_box" class="hidden p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 space-y-2 text-xs">
            <div class="flex items-start gap-2 font-bold">
                <i class="ti ti-alert-triangle text-base text-rose-600 mt-0.5 shrink-0"></i>
                <div>
                    <h5 id="sheet_inbreeding_title" class="font-extrabold text-rose-900 leading-tight">Peringatan Inbreeding (Perkawinan Sedarah)</h5>
                    <p id="sheet_inbreeding_msg" class="text-[11px] text-rose-700 font-normal mt-0.5 leading-relaxed"></p>
                </div>
            </div>
            <label class="flex items-center gap-2 pt-1 border-t border-rose-200/60 font-semibold cursor-pointer text-[11px] text-rose-900 select-none">
                <input type="checkbox" name="abaikan_inbreeding" value="1" id="sheet_abaikan_inbreeding" class="rounded text-rose-600 focus:ring-rose-500">
                <span>Saya memahami risiko inbreeding dan tetap ingin melanjutkan</span>
            </label>
        </div>

        <!-- Tanggal Kawin & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="sheet_tanggal_kawin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Tanggal Kawin <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_kawin" id="sheet_tanggal_kawin" value="{{ old('tanggal_kawin', date('Y-m-d')) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @error('tanggal_kawin')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sheet_status_kawin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Status <span class="text-rose-500">*</span>
                </label>
                <select name="status" id="sheet_status_kawin" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="menunggu" {{ old('status', 'menunggu') === 'menunggu' ? 'selected' : '' }}>Menunggu Hasil</option>
                    <option value="berhasil" {{ old('status') === 'berhasil' ? 'selected' : '' }}>Berhasil Bunting</option>
                    <option value="tidak_berhasil" {{ old('status') === 'tidak_berhasil' ? 'selected' : '' }}>Tidak Berhasil</option>
                </select>
                @error('status')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Catatan -->
        <div>
            <label for="sheet_catatan_kawin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Catatan Opsional
            </label>
            <textarea name="catatan" id="sheet_catatan_kawin" rows="2"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                placeholder="Kondisi birahi, kawin suntik / alami...">{{ old('catatan') }}</textarea>
            @error('catatan')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetTambahPerkawinan')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Simpan Perkawinan
            </button>
        </div>
    </form>
</x-bottom-sheet>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pejantanSel = document.getElementById('sheet_pejantan_id');
        const betinaSel = document.getElementById('sheet_betina_id');
        const warningBox = document.getElementById('sheet_inbreeding_box');
        const titleEl = document.getElementById('sheet_inbreeding_title');
        const msgEl = document.getElementById('sheet_inbreeding_msg');
        const checkboxEl = document.getElementById('sheet_abaikan_inbreeding');

        async function verifyInbreeding() {
            const pId = pejantanSel ? pejantanSel.value : '';
            const bId = betinaSel ? betinaSel.value : '';

            if (!pId || !bId || pId === bId) {
                if (warningBox) warningBox.classList.add('hidden');
                return;
            }

            try {
                const res = await fetch(`{{ route('perkawinans.check-inbreeding') }}?betina_id=${bId}&pejantan_id=${pId}`);
                const data = await res.json();

                if (data.is_inbreeding && data.detail) {
                    titleEl.textContent = data.detail.title || 'Peringatan Inbreeding';
                    msgEl.textContent = data.detail.message || 'Kedua kambing memiliki hubungan darah.';
                    warningBox.classList.remove('hidden');
                } else {
                    warningBox.classList.add('hidden');
                    if (checkboxEl) checkboxEl.checked = false;
                }
            } catch (err) {
                console.error('Failed to check inbreeding', err);
            }
        }

        if (pejantanSel) pejantanSel.addEventListener('change', verifyInbreeding);
        if (betinaSel) betinaSel.addEventListener('change', verifyInbreeding);

        // Initial check if values are pre-selected
        if (pejantanSel && betinaSel && pejantanSel.value && betinaSel.value) {
            verifyInbreeding();
        }
    });
</script>

@if(request('action') === 'create' || ($errors->any() && !old('_method')))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetTambahPerkawinan');
    });
</script>
@endif
@endsection
