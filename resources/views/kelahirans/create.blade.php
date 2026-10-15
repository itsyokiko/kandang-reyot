@extends('layouts.app')

@section('title', 'Catat Kelahiran')

@section('content')
<div class="space-y-4">
    <!-- Back Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('kelahirans.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-600 transition p-1">
            <i class="ti ti-arrow-left text-sm"></i>
            <span>Kembali ke Catatan</span>
        </a>
    </div>

    @if ($betinas->isEmpty() || $pejantans->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-amber-900 space-y-3">
            <h3 class="font-bold text-sm flex items-center gap-1.5">
                <i class="ti ti-alert-circle text-base text-amber-600"></i>
                <span>Data Kambing Belum Cukup</span>
            </h3>
            <p class="text-xs leading-relaxed text-amber-800">
                Untuk mencatat kelahiran, peternakan harus memiliki minimal 1 ekor kambing berjenis kelamin <strong>betina</strong> dan 1 ekor <strong>jantan</strong>.
            </p>
            <div class="pt-2">
                <x-action-button href="{{ route('kambings.create') }}" variant="amber" size="sm" icon="ti ti-plus">
                    Tambah Data Kambing
                </x-action-button>
            </div>
        </div>
    @else
        <x-section-card title="Catat Peristiwa Kelahiran" icon="ti ti-mood-kid">
            <form action="{{ route('kelahirans.store') }}" method="POST" id="form-kelahiran" class="space-y-4">
                @csrf

                <!-- Pilihan Perkawinan (Prioritas) -->
                <div class="bg-emerald-50/70 border border-emerald-200 rounded-xl p-3.5 space-y-2">
                    <label for="perkawinan_id" class="block text-xs font-bold text-emerald-950 uppercase tracking-wider">
                        Pilih Dari Riwayat Perkawinan <span class="text-[10px] font-normal text-emerald-700">(Rekomendasi)</span>
                    </label>
                    <p class="text-[11px] text-emerald-800">
                        Memilih perkawinan akan otomatis mengisi induk dan pejantan.
                    </p>
                    <select name="perkawinan_id" id="perkawinan_id"
                        class="w-full px-3.5 py-2.5 bg-white border border-emerald-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        <option value="">-- Tanpa Perkawinan Tercatat (Pilih Manual) --</option>
                        @foreach ($perkawinans as $p)
                            <option value="{{ $p->id }}"
                                data-betina="{{ $p->betina_id }}"
                                data-pejantan="{{ $p->pejantan_id }}"
                                {{ old('perkawinan_id', $selectedPerkawinanId) == $p->id ? 'selected' : '' }}>
                                {{ $p->betina->nama }} (Induk) &times; {{ $p->pejantan->nama }} (Pejantan) — Kawin: {{ $p->tanggal_kawin->format('d M Y') }}
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
                        <label for="induk_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Induk Betina <span class="text-rose-500">*</span>
                        </label>
                        <select name="induk_id" id="induk_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                            <option value="">-- Pilih Induk Betina --</option>
                            @foreach ($betinas as $betina)
                                <option value="{{ $betina->id }}" {{ old('induk_id', $selectedIndukId) == $betina->id ? 'selected' : '' }}>
                                    ♀ {{ $betina->nama }} {{ $betina->ras ? '(' . $betina->ras . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('induk_id')
                            <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

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

                <!-- Tanggal Kelahiran -->
                <div>
                    <label for="tanggal_kelahiran" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Tanggal Kelahiran <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_kelahiran" id="tanggal_kelahiran"
                        value="{{ old('tanggal_kelahiran', date('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    @error('tanggal_kelahiran')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Section Pendaftaran Anak -->
                <div class="border-t border-slate-100 pt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Data Anak Kambing</h3>
                            <p class="text-[11px] text-slate-500">Anak akan langsung terdaftar ke populasi</p>
                        </div>
                        <button type="button" id="btn-tambah-anak"
                            class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-300 font-bold px-3 py-1.5 rounded-xl text-xs transition active:scale-95">
                            <i class="ti ti-plus text-xs"></i>
                            <span>Tambah Anak</span>
                        </button>
                    </div>

                    <div id="container-anak" class="space-y-3">
                        @php
                            $oldAnak = old('anak', [['nama' => '', 'jenis_kelamin' => 'jantan', 'ras' => '', 'catatan' => '']]);
                        @endphp

                        @foreach ($oldAnak as $index => $item)
                            <div class="item-anak bg-slate-50 border border-slate-200 rounded-2xl p-3.5 relative space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                                    <span class="text-xs font-extrabold text-slate-700 uppercase judul-anak">Anak #{{ $index + 1 }}</span>
                                    <button type="button" class="btn-hapus-anak text-xs font-semibold text-rose-600 hover:text-rose-800 {{ count($oldAnak) <= 1 ? 'hidden' : '' }}">
                                        Hapus
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                            Nama Anak <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" name="anak[{{ $index }}][nama]" value="{{ $item['nama'] ?? '' }}" required
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

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                            Ras (Opsional)
                                        </label>
                                        <input type="text" name="anak[{{ $index }}][ras]" value="{{ $item['ras'] ?? '' }}"
                                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                            placeholder="Bawaan induk jika kosong">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                            Catatan Anak
                                        </label>
                                        <input type="text" name="anak[{{ $index }}][catatan]" value="{{ $item['catatan'] ?? '' }}"
                                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                            placeholder="Kondisi fisik saat lahir...">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Catatan Kelahiran -->
                <div>
                    <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Catatan Peristiwa Kelahiran
                    </label>
                    <textarea name="catatan" id="catatan" rows="3"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                        placeholder="Kondisi proses kelahiran, bantuan medis, dll...">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <x-action-button href="{{ route('kelahirans.index') }}" variant="secondary">
                        Batal
                    </x-action-button>
                    <x-action-button type="submit" variant="primary">
                        Simpan Kelahiran & Anak
                    </x-action-button>
                </div>
            </form>
        </x-section-card>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const perkawinanSelect = document.getElementById('perkawinan_id');
                const indukSelect = document.getElementById('induk_id');
                const pejantanSelect = document.getElementById('pejantan_id');
                const containerAnak = document.getElementById('container-anak');
                const btnTambahAnak = document.getElementById('btn-tambah-anak');

                if (perkawinanSelect) {
                    perkawinanSelect.addEventListener('change', function () {
                        const selectedOption = perkawinanSelect.options[perkawinanSelect.selectedIndex];
                        const betinaId = selectedOption.getAttribute('data-betina');
                        const pejantanId = selectedOption.getAttribute('data-pejantan');

                        if (betinaId && indukSelect) {
                            indukSelect.value = betinaId;
                        }
                        if (pejantanId && pejantanSelect) {
                            pejantanSelect.value = pejantanId;
                        }
                    });
                }

                function updateIndex() {
                    const items = containerAnak.querySelectorAll('.item-anak');
                    items.forEach((item, index) => {
                        const title = item.querySelector('.judul-anak');
                        if (title) title.textContent = 'Anak #' + (index + 1);

                        const btnHapus = item.querySelector('.btn-hapus-anak');
                        if (btnHapus) {
                            if (items.length > 1) {
                                btnHapus.classList.remove('hidden');
                            } else {
                                btnHapus.classList.add('hidden');
                            }
                        }

                        item.querySelectorAll('input, select').forEach(field => {
                            const name = field.getAttribute('name');
                            if (name) {
                                field.setAttribute('name', name.replace(/anak\[\d+\]/, 'anak[' + index + ']'));
                            }
                        });
                    });
                }

                btnTambahAnak.addEventListener('click', function () {
                    const items = containerAnak.querySelectorAll('.item-anak');
                    const newIndex = items.length;
                    const template = `
                        <div class="item-anak bg-slate-50 border border-slate-200 rounded-2xl p-3.5 relative space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                                <span class="text-xs font-extrabold text-slate-700 uppercase judul-anak">Anak #${newIndex + 1}</span>
                                <button type="button" class="btn-hapus-anak text-xs font-semibold text-rose-600 hover:text-rose-800">
                                    Hapus
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                        Nama Anak <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="anak[${newIndex}][nama]" required
                                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        placeholder="Contoh: Bima">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                        Jenis Kelamin <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="anak[${newIndex}][jenis_kelamin]" required
                                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        <option value="jantan">♂ Jantan</option>
                                        <option value="betina">♀ Betina</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                        Ras (Opsional)
                                    </label>
                                    <input type="text" name="anak[${newIndex}][ras]"
                                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        placeholder="Bawaan induk jika kosong">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                        Catatan Anak
                                    </label>
                                    <input type="text" name="anak[${newIndex}][catatan]"
                                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        placeholder="Kondisi fisik saat lahir...">
                                </div>
                            </div>
                        </div>
                    `;
                    containerAnak.insertAdjacentHTML('beforeend', template);
                    updateIndex();
                });

                containerAnak.addEventListener('click', function (e) {
                    if (e.target.classList.contains('btn-hapus-anak')) {
                        const item = e.target.closest('.item-anak');
                        if (item) {
                            item.remove();
                            updateIndex();
                        }
                    }
                });
            });
        </script>
    @endif
</div>
@endsection
