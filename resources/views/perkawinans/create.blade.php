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
@php
    $betinasJson = $betinas->map(fn($b) => [
        'id' => $b->id,
        'nama' => $b->nama,
        'induk_id' => $b->induk_id,
        'pejantan_id' => $b->pejantan_id,
    ])->toJson();

    $pejantansJson = $pejantans->map(fn($p) => [
        'id' => $p->id,
        'nama' => $p->nama,
        'ras' => $p->ras,
        'induk_id' => $p->induk_id,
        'pejantan_id' => $p->pejantan_id,
    ])->toJson();
@endphp

        <x-section-card title="Catat Perkawinan Baru" icon="ti ti-heart">
            <form action="{{ route('perkawinans.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Pilihan Metode Perkawinan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Metode Perkawinan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <label class="cursor-pointer border border-slate-200 rounded-xl p-2.5 flex items-center gap-2 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/80 transition select-none">
                            <input type="radio" name="metode_kawin" value="alami" checked class="radio-metode text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Pejantan Kandang</span>
                                <span class="text-[10px] text-slate-500">Kawin alami sesama kandang</span>
                            </div>
                        </label>
                        <label class="cursor-pointer border border-slate-200 rounded-xl p-2.5 flex items-center gap-2 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/80 transition select-none">
                            <input type="radio" name="metode_kawin" value="inseminasi" class="radio-metode text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Inseminasi Buatan (IB)</span>
                                <span class="text-[10px] text-slate-500">Kawin suntik straw sperma</span>
                            </div>
                        </label>
                        <label class="cursor-pointer border border-slate-200 rounded-xl p-2.5 flex items-center gap-2 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/80 transition select-none">
                            <input type="radio" name="metode_kawin" value="luar" class="radio-metode text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Pejantan Luar</span>
                                <span class="text-[10px] text-slate-500">Kambing pejantan pihak luar</span>
                            </div>
                        </label>
                    </div>
                </div>

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

                    <!-- Pejantan Kandang (Alami) -->
                    <div id="wrapper_pejantan_kandang">
                        <label for="pejantan_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Pejantan Kandang <span class="text-rose-500">*</span>
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
                        <p id="info_inbreeding_filter" class="hidden text-[10px] text-rose-600 font-semibold mt-1"></p>
                        @error('pejantan_id')
                            <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pejantan Luar / Inseminasi Buatan Input Text (Opsional) -->
                    <div id="wrapper_pejantan_luar" class="hidden">
                        <label id="label_pejantan_luar" for="nama_pejantan_luar" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Nama Pejantan / Kode Straw IB <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" name="nama_pejantan_luar" id="nama_pejantan_luar" value="{{ old('nama_pejantan_luar') }}"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                            placeholder="Contoh: IB - Boer Super B901 atau Si Hitam (Opsional)">
                        <p class="text-[10px] text-slate-400 mt-1">Bisa dikosongkan jika tidak diketahui atau tidak ada kode khusus.</p>
                        @error('nama_pejantan_luar')
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
                        placeholder="Kondisi birahi, reaksi kambing, atau keterangan khusus...">{{ old('catatan') }}</textarea>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const betinas = {!! $betinasJson ?? '[]' !!};
        const pejantans = {!! $pejantansJson ?? '[]' !!};

        const pejantanSel = document.getElementById('pejantan_id');
        const betinaSel = document.getElementById('betina_id');
        const wrapperKandang = document.getElementById('wrapper_pejantan_kandang');
        const wrapperLuar = document.getElementById('wrapper_pejantan_luar');
        const labelLuar = document.getElementById('label_pejantan_luar');
        const inputLuar = document.getElementById('nama_pejantan_luar');
        const infoInbreeding = document.getElementById('info_inbreeding_filter');
        const radiosMetode = document.querySelectorAll('.radio-metode');

        // Ganti tampilan input sesuai metode perkawinan
        function updateMetodeUI() {
            let activeMetode = 'alami';
            radiosMetode.forEach(r => {
                if (r.checked) activeMetode = r.value;
            });

            if (activeMetode === 'alami') {
                wrapperKandang.classList.remove('hidden');
                wrapperLuar.classList.add('hidden');
                pejantanSel.disabled = false;
                pejantanSel.setAttribute('required', 'required');
                inputLuar.disabled = true;
                inputLuar.removeAttribute('required');
                filterInbreedingOptions();
            } else {
                // Sembunyikan & nonaktifkan selector jantan kandang
                wrapperKandang.classList.add('hidden');
                wrapperLuar.classList.remove('hidden');
                pejantanSel.disabled = true;
                pejantanSel.removeAttribute('required');
                pejantanSel.value = '';
                if (infoInbreeding) infoInbreeding.classList.add('hidden');

                // Kolom nama pejantan luar / IB aktif dan bersifat opsional
                inputLuar.disabled = false;
                inputLuar.removeAttribute('required');

                if (activeMetode === 'inseminasi') {
                    labelLuar.innerHTML = 'Kode Batch Straw / Nama Pejantan IB <span class="text-slate-400 font-normal">(Opsional)</span>';
                    inputLuar.placeholder = 'Contoh: IB - Boer Super B901 (Boleh kosong)';
                } else {
                    labelLuar.innerHTML = 'Nama Pejantan Luar Kandang <span class="text-slate-400 font-normal">(Opsional)</span>';
                    inputLuar.placeholder = 'Contoh: Si Hitam (Pak Slamet) (Boleh kosong)';
                }
            }
        }

        radiosMetode.forEach(r => r.addEventListener('change', updateMetodeUI));
        updateMetodeUI();

        // Cek sedarah: Pejantan anak betina, Betina anak pejantan, Se-Induk, Se-Bapak
        function isSedarah(betina, pejantan) {
            if (!betina || !pejantan) return false;
            if (pejantan.induk_id && String(pejantan.induk_id) === String(betina.id)) return true;
            if (betina.pejantan_id && String(betina.pejantan_id) === String(pejantan.id)) return true;
            if (betina.induk_id && pejantan.induk_id && String(betina.induk_id) === String(pejantan.induk_id)) return true;
            if (betina.pejantan_id && pejantan.pejantan_id && String(betina.pejantan_id) === String(pejantan.pejantan_id)) return true;
            return false;
        }

        // Filter opsi pejantan agar yang sedarah tidak ditampilkan sama sekali
        function filterInbreedingOptions() {
            if (!betinaSel || !pejantanSel) return;
            const bId = betinaSel.value;
            const selectedBetina = betinas.find(b => String(b.id) === String(bId));

            let hiddenCount = 0;

            Array.from(pejantanSel.options).forEach(opt => {
                if (!opt.value) return; // Skip placeholder
                const p = pejantans.find(k => String(k.id) === String(opt.value));

                if (selectedBetina && p && isSedarah(selectedBetina, p)) {
                    opt.hidden = true;
                    opt.disabled = true;
                    hiddenCount++;
                    if (pejantanSel.value === opt.value) {
                        pejantanSel.value = '';
                    }
                } else {
                    opt.hidden = false;
                    opt.disabled = false;
                }
            });

            if (infoInbreeding) {
                if (hiddenCount > 0) {
                    infoInbreeding.textContent = `⚠️ ${hiddenCount} pejantan memiliki relasi sedarah dan disembunyikan otomatis untuk mencegah inbreeding.`;
                    infoInbreeding.classList.remove('hidden');
                } else {
                    infoInbreeding.classList.add('hidden');
                }
            }
        }

        if (betinaSel) {
            betinaSel.addEventListener('change', filterInbreedingOptions);
            filterInbreedingOptions();
        }
    });
</script>
@endsection
