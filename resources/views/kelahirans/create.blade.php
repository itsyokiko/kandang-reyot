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
            <form action="{{ route('kelahirans.store') }}" method="POST" id="form-kelahiran" class="space-y-5">
                @csrf

                <!-- Pilihan Perkawinan (Rekomendasi) -->
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

                <!-- Induk & Pejantan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="induk_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Induk Betina <span class="text-rose-500">*</span>
                        </label>
                        <select name="induk_id" id="induk_id" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                            <option value="">-- Pilih Induk Betina --</option>
                            @foreach ($betinas as $betina)
                                <option value="{{ $betina->id }}" data-ras="{{ $betina->ras }}"
                                    {{ old('induk_id', $selectedIndukId) == $betina->id ? 'selected' : '' }}>
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
                                <option value="{{ $pejantan->id }}"
                                    {{ old('pejantan_id', $selectedPejantanId) == $pejantan->id ? 'selected' : '' }}>
                                    ♂ {{ $pejantan->nama }} {{ $pejantan->ras ? '(' . $pejantan->ras . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('pejantan_id')
                            <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Panel: Anak yang sudah punya relasi (muncul saat induk/pejantan dipilih) -->
                <div id="panel-relasi" class="hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3.5 space-y-2">
                        <p class="text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ti ti-link text-sm text-blue-600"></i>
                            Anak Dengan Relasi Terdaftar
                        </p>
                        <p class="text-[11px] text-blue-700">Kambing berikut sudah tercatat sebagai anak dari pasangan ini. Klik untuk langsung menambahkan ke daftar kelahiran.</p>
                        <div id="list-relasi" class="flex flex-wrap gap-2 pt-1"></div>
                    </div>
                </div>

                <!-- Section Data Anak -->
                <div class="border-t border-slate-100 pt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Data Anak Kambing</h3>
                            <p class="text-[11px] text-slate-500">Anak dengan tanggal lahir berbeda → otomatis dicatat sebagai kelahiran terpisah</p>
                        </div>
                        <button type="button" id="btn-tambah-anak"
                            class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-300 font-bold px-3 py-1.5 rounded-xl text-xs transition active:scale-95">
                            <i class="ti ti-plus text-xs"></i>
                            <span>Tambah Anak</span>
                        </button>
                    </div>

                    <div id="container-anak" class="space-y-3">
                        <!-- kartu anak pertama (default) -->
                        <div class="item-anak bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-3" data-index="0">
                            @include('kelahirans._anak_card', ['index' => 0, 'item' => old('anak.0', [])])
                        </div>
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
                        Simpan Kelahiran &amp; Anak
                    </x-action-button>
                </div>
            </form>
        </x-section-card>

        <!-- Template kartu anak (digunakan JS untuk clone) -->
        <template id="tmpl-anak-card">
            <div class="item-anak bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-3" data-index="__IDX__">
                @include('kelahirans._anak_card', ['index' => '__IDX__', 'item' => []])
            </div>
        </template>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const perkawinanSelect = document.getElementById('perkawinan_id');
            const indukSelect      = document.getElementById('induk_id');
            const pejantanSelect   = document.getElementById('pejantan_id');
            const containerAnak    = document.getElementById('container-anak');
            const btnTambahAnak    = document.getElementById('btn-tambah-anak');
            const panelRelasi      = document.getElementById('panel-relasi');
            const listRelasi       = document.getElementById('list-relasi');
            const AUTOCOMPLETE_URL = '{{ route('api.kambings.autocomplete') }}';
            const ANAK_URL         = '{{ route('api.kambings.anak') }}';

            // ── Perkawinan select → auto-fill induk & pejantan ──────────────
            perkawinanSelect?.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const betinaId   = opt.getAttribute('data-betina');
                const pejantanId = opt.getAttribute('data-pejantan');
                if (betinaId)   indukSelect.value    = betinaId;
                if (pejantanId) pejantanSelect.value = pejantanId;
                fetchRelasi();
            });

            // ── Induk / Pejantan change → fetch relasi ───────────────────────
            indukSelect?.addEventListener('change', fetchRelasi);
            pejantanSelect?.addEventListener('change', fetchRelasi);

            function fetchRelasi() {
                const indukId    = indukSelect?.value;
                const pejantanId = pejantanSelect?.value;
                if (!indukId && !pejantanId) {
                    panelRelasi.classList.add('hidden');
                    listRelasi.innerHTML = '';
                    return;
                }
                const params = new URLSearchParams();
                if (indukId)    params.set('induk_id', indukId);
                if (pejantanId) params.set('pejantan_id', pejantanId);

                fetch(ANAK_URL + '?' + params)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.length) {
                            panelRelasi.classList.add('hidden');
                            listRelasi.innerHTML = '';
                            return;
                        }
                        listRelasi.innerHTML = '';
                        data.forEach(k => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.dataset.kambing = JSON.stringify(k);
                            btn.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-blue-300 text-xs font-semibold text-blue-800 hover:bg-blue-100 active:scale-95 transition';
                            const jkIcon = k.jenis_kelamin === 'jantan' ? '♂' : '♀';
                            btn.innerHTML = `<i class="ti ti-plus text-[10px]"></i> ${jkIcon} ${k.nama}`;
                            if (k.tanggal_lahir) {
                                const tgl = new Date(k.tanggal_lahir);
                                btn.innerHTML += ` <span class="font-normal text-blue-500">${tgl.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'})}</span>`;
                            }
                            btn.addEventListener('click', () => addAnakFromRelasi(k));
                            listRelasi.appendChild(btn);
                        });
                        panelRelasi.classList.remove('hidden');
                    })
                    .catch(() => {
                        panelRelasi.classList.add('hidden');
                    });
            }

            // ── Tambah anak dari relasi yang sudah ada ────────────────────────
            function addAnakFromRelasi(k) {
                // Cek apakah sudah ada di daftar (berdasarkan kambing_id)
                const existing = containerAnak.querySelector(`input[name*="[kambing_id]"][value="${k.id}"]`);
                if (existing) {
                    existing.closest('.item-anak')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                const idx = containerAnak.querySelectorAll('.item-anak').length;
                addAnakCard(idx, k);
            }

            // ── Tambah anak baru (kosong) ─────────────────────────────────────
            btnTambahAnak.addEventListener('click', function () {
                const idx = containerAnak.querySelectorAll('.item-anak').length;
                addAnakCard(idx, null);
            });

            function addAnakCard(idx, prefill) {
                // Clone dari template server-rendered (PHP), replace placeholder index
                const tmpl = document.getElementById('tmpl-anak-card');
                const html = tmpl ? tmpl.innerHTML.replaceAll('__IDX__', idx) : buildCardHTML(idx);
                containerAnak.insertAdjacentHTML('beforeend', html);

                const card = containerAnak.lastElementChild;
                card.dataset.index = idx;

                if (prefill) {
                    fillAnakCard(card, prefill);
                }

                updateHapusButtons();
                initAutocomplete(card);
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            // ── Build raw HTML if template unavailable ────────────────────────
            function buildCardHTML(idx) {
                const today = new Date().toISOString().slice(0, 10);
                return `<div class="item-anak bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-3" data-index="${idx}">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                        <span class="text-xs font-extrabold text-slate-700 uppercase judul-anak">Anak #${idx + 1}</span>
                        <button type="button" class="btn-hapus-anak text-xs font-semibold text-rose-600 hover:text-rose-800">Hapus</button>
                    </div>
                    <input type="hidden" name="anak[${idx}][kambing_id]" value="">
                    <div class="grid grid-cols-2 gap-2 mb-1">
                        <button type="button" class="btn-mode btn-mode-existing py-2 rounded-xl text-xs font-bold border border-slate-300 bg-white text-slate-600 hover:border-emerald-400 hover:text-emerald-700 transition">🐐 Kambing Ada</button>
                        <button type="button" class="btn-mode btn-mode-new py-2 rounded-xl text-xs font-bold border border-emerald-400 bg-emerald-50 text-emerald-700 transition">✏️ Input Baru</button>
                    </div>
                    <div class="mode-existing hidden">
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Cari Nama Kambing</label>
                        <div class="relative">
                            <input type="text" class="ac-search w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Ketik nama...">
                            <ul class="ac-dropdown absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-lg mt-1 hidden max-h-48 overflow-y-auto text-xs"></ul>
                        </div>
                        <div class="ac-selected hidden mt-2 p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs space-y-1">
                            <p class="font-bold text-emerald-800 ac-nama"></p>
                            <p class="text-emerald-600 ac-detail"></p>
                        </div>
                    </div>
                    <div class="mode-new">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Nama Anak <span class="text-rose-500">*</span></label>
                                <input type="text" name="anak[${idx}][nama]" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Contoh: Bimo">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                                <select name="anak[${idx}][jenis_kelamin]" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="jantan">♂ Jantan</option>
                                    <option value="betina">♀ Betina</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Ras (Opsional)</label>
                            <input type="text" name="anak[${idx}][ras]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Ikuti induk jika kosong">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tanggal Lahir <span class="text-rose-500">*</span></label>
                        <input type="date" name="anak[${idx}][tanggal_lahir]" value="${today}" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400">Anak dengan tanggal berbeda otomatis dicatat sebagai kelahiran terpisah</span>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Catatan Anak</label>
                        <input type="text" name="anak[${idx}][catatan]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Kondisi fisik saat lahir...">
                    </div>
                </div>`;
            }

            // ── Isi kartu anak dari data kambing existing ─────────────────────
            function fillAnakCard(card, k) {
                const modeExisting = card.querySelector('.mode-existing');
                const modeNew      = card.querySelector('.mode-new');
                const btnExisting  = card.querySelector('.btn-mode-existing');
                const btnNew       = card.querySelector('.btn-mode-new');

                // Switch ke mode existing
                modeExisting?.classList.remove('hidden');
                modeNew?.classList.add('hidden');
                btnExisting?.classList.add('border-emerald-400', 'bg-emerald-50', 'text-emerald-700');
                btnExisting?.classList.remove('border-slate-300', 'bg-white', 'text-slate-600');
                btnNew?.classList.remove('border-emerald-400', 'bg-emerald-50', 'text-emerald-700');
                btnNew?.classList.add('border-slate-300', 'bg-white', 'text-slate-600');

                applyKambingToCard(card, k);
            }

            function applyKambingToCard(card, k) {
                const idInput = card.querySelector('input[name*="[kambing_id]"]');
                if (idInput) idInput.value = k.id;

                // Matikan required pada field nama/jk
                card.querySelectorAll('.mode-new input[required], .mode-new select[required]').forEach(el => {
                    el.removeAttribute('required');
                });

                // Tampilkan info kambing yang dipilih
                const acSelected = card.querySelector('.ac-selected');
                const acNama     = card.querySelector('.ac-nama');
                const acDetail   = card.querySelector('.ac-detail');
                if (acSelected) {
                    acSelected.classList.remove('hidden');
                    if (acNama) acNama.textContent = (k.jenis_kelamin === 'jantan' ? '♂ ' : '♀ ') + k.nama;
                    const parts = [];
                    if (k.ras) parts.push(k.ras);
                    if (k.tanggal_lahir) {
                        const tgl = new Date(k.tanggal_lahir);
                        parts.push('Lahir: ' + tgl.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}));
                        // Set tanggal lahir di input
                        const tglInput = card.querySelector('input[name*="[tanggal_lahir]"]');
                        if (tglInput) tglInput.value = k.tanggal_lahir;
                    }
                    if (acDetail) acDetail.textContent = parts.join(' · ');
                }
            }

            // ── Mode toggle (Kambing Ada / Input Baru) ───────────────────────
            containerAnak.addEventListener('click', function (e) {
                const btnExisting = e.target.closest('.btn-mode-existing');
                const btnNew      = e.target.closest('.btn-mode-new');
                const btnHapus    = e.target.closest('.btn-hapus-anak');

                if (btnHapus) {
                    const item = btnHapus.closest('.item-anak');
                    if (item) { item.remove(); updateHapusButtons(); reindex(); }
                    return;
                }

                if (btnExisting || btnNew) {
                    const card       = (btnExisting || btnNew).closest('.item-anak');
                    const isExisting = !!btnExisting;
                    const modeExisting = card.querySelector('.mode-existing');
                    const modeNew      = card.querySelector('.mode-new');

                    modeExisting?.classList.toggle('hidden', !isExisting);
                    modeNew?.classList.toggle('hidden', isExisting);

                    // Styling toggle buttons
                    const bE = card.querySelector('.btn-mode-existing');
                    const bN = card.querySelector('.btn-mode-new');
                    const ACTIVE   = ['border-emerald-400', 'bg-emerald-50', 'text-emerald-700'];
                    const INACTIVE = ['border-slate-300', 'bg-white', 'text-slate-600'];

                    if (isExisting) {
                        bE?.classList.add(...ACTIVE); bE?.classList.remove(...INACTIVE);
                        bN?.classList.add(...INACTIVE); bN?.classList.remove(...ACTIVE);
                        // clear nama/jk required
                        card.querySelectorAll('.mode-new input[name], .mode-new select[name]').forEach(el => el.removeAttribute('required'));
                    } else {
                        bN?.classList.add(...ACTIVE); bN?.classList.remove(...INACTIVE);
                        bE?.classList.add(...INACTIVE); bE?.classList.remove(...ACTIVE);
                        // restore required
                        const namaInput = card.querySelector('.mode-new input[name*="[nama]"]');
                        const jkSelect  = card.querySelector('.mode-new select[name*="[jenis_kelamin]"]');
                        if (namaInput) namaInput.required = true;
                        if (jkSelect)  jkSelect.required  = true;
                        // clear kambing_id
                        const idInput = card.querySelector('input[name*="[kambing_id]"]');
                        if (idInput) idInput.value = '';
                        // clear ac
                        const acSearch   = card.querySelector('.ac-search');
                        const acSelected = card.querySelector('.ac-selected');
                        if (acSearch)   acSearch.value = '';
                        if (acSelected) acSelected.classList.add('hidden');
                    }
                    return;
                }
            });

            // ── Autocomplete search ───────────────────────────────────────────
            function initAutocomplete(card) {
                const searchInput = card.querySelector('.ac-search');
                const dropdown    = card.querySelector('.ac-dropdown');
                if (!searchInput || !dropdown) return;

                let timer;
                searchInput.addEventListener('input', function () {
                    clearTimeout(timer);
                    const q = this.value.trim();
                    if (q.length < 1) { dropdown.classList.add('hidden'); return; }
                    timer = setTimeout(() => {
                        const excludeId = indukSelect?.value || '';
                        fetch(`${AUTOCOMPLETE_URL}?q=${encodeURIComponent(q)}&exclude_id=${excludeId}`)
                            .then(r => r.json())
                            .then(results => {
                                dropdown.innerHTML = '';
                                if (!results.length) {
                                    dropdown.innerHTML = '<li class="px-3 py-2 text-slate-400">Tidak ditemukan</li>';
                                    dropdown.classList.remove('hidden');
                                    return;
                                }
                                results.forEach(k => {
                                    const li = document.createElement('li');
                                    li.className = 'px-3 py-2 hover:bg-emerald-50 cursor-pointer flex items-center gap-2';
                                    const jk = k.jenis_kelamin === 'jantan' ? '♂' : '♀';
                                    li.innerHTML = `<span class="font-semibold">${jk} ${k.nama}</span>${k.ras ? `<span class="text-slate-400">${k.ras}</span>` : ''}`;
                                    li.addEventListener('mousedown', () => {
                                        searchInput.value = k.nama;
                                        dropdown.classList.add('hidden');
                                        applyKambingToCard(card, k);
                                    });
                                    dropdown.appendChild(li);
                                });
                                dropdown.classList.remove('hidden');
                            });
                    }, 250);
                });

                searchInput.addEventListener('blur', () => {
                    setTimeout(() => dropdown.classList.add('hidden'), 200);
                });
            }

            // Init autocomplete on first card
            initAutocomplete(containerAnak.querySelector('.item-anak'));

            // ── Helpers ───────────────────────────────────────────────────────
            function updateHapusButtons() {
                const items = containerAnak.querySelectorAll('.item-anak');
                items.forEach(item => {
                    const btn = item.querySelector('.btn-hapus-anak');
                    if (btn) btn.classList.toggle('hidden', items.length <= 1);
                });
            }

            function reindex() {
                containerAnak.querySelectorAll('.item-anak').forEach((item, i) => {
                    item.dataset.index = i;
                    const title = item.querySelector('.judul-anak');
                    if (title) title.textContent = `Anak #${i + 1}`;
                    item.querySelectorAll('input[name], select[name], textarea[name]').forEach(el => {
                        el.name = el.name.replace(/anak\[\d+\]/, `anak[${i}]`);
                    });
                });
            }
        });
        </script>
    @endif
</div>
@endsection
