@php
    $prefix = $prefix ?? '';
    $isModal = $isModal ?? false;
    $today = date('Y-m-d');
    $allKambingsJson = isset($allKambings) ? $allKambings->toJson() : '[]';
    $pejantansJson = isset($pejantans) ? $pejantans->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama, 'ras' => $p->ras])->toJson() : '[]';
@endphp

<div id="{{ $prefix }}kelahiran_app" class="space-y-4">
    <!-- 1. Pilihan Riwayat Perkawinan (Rekomendasi) -->
    <div class="bg-emerald-50/80 border border-emerald-200 rounded-2xl p-3.5 space-y-1.5">
        <label for="{{ $prefix }}perkawinan_id" class="block text-xs font-bold text-emerald-950 uppercase tracking-wider">
            Pilih Dari Riwayat Perkawinan <span class="text-[10px] font-normal text-emerald-700">(Rekomendasi)</span>
        </label>
        <p class="text-[11px] text-emerald-800">
            Pilih perkawinan untuk mengisi otomatis induk betina dan pejantan.
        </p>
        <select name="perkawinan_id" id="{{ $prefix }}perkawinan_id"
            class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
            <option value="">-- Tanpa Perkawinan Tercatat (Pilih Manual) --</option>
            @foreach ($perkawinans as $p)
                <option value="{{ $p->id }}"
                    data-betina="{{ $p->betina_id }}"
                    data-pejantan="{{ $p->pejantan_id }}"
                    {{ old('perkawinan_id', $selectedPerkawinanId ?? null) == $p->id ? 'selected' : '' }}>
                    {{ $p->betina->nama }} &times; {{ $p->pejantan->nama }} (Kawin: {{ $p->tanggal_kawin->format('d M Y') }})
                </option>
            @endforeach
        </select>
        @error('perkawinan_id')
            <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- 2. Pasangan Induk & Pejantan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label for="{{ $prefix }}induk_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Induk Betina <span class="text-rose-500">*</span>
            </label>
            <select name="induk_id" id="{{ $prefix }}induk_id" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                <option value="">-- Pilih Induk Betina --</option>
                @foreach ($betinas as $b)
                    <option value="{{ $b->id }}" data-ras="{{ $b->ras }}"
                        {{ old('induk_id', $selectedIndukId ?? null) == $b->id ? 'selected' : '' }}>
                        ♀ {{ $b->nama }} {{ $b->ras ? '(' . $b->ras . ')' : '' }}
                    </option>
                @endforeach
            </select>
            @error('induk_id')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="{{ $prefix }}pejantan_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Pejantan Utama
                </label>
                <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-semibold text-slate-500 hover:text-slate-800 select-none">
                    <input type="checkbox" id="{{ $prefix }}chk_tanpa_pejantan" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5">
                    <span>Tanpa Pejantan Utama</span>
                </label>
            </div>
            <select name="pejantan_id" id="{{ $prefix }}pejantan_id"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                <option value="">-- Pilih Pejantan --</option>
                @foreach ($pejantans as $p)
                    <option value="{{ $p->id }}" data-ras="{{ $p->ras }}"
                        {{ old('pejantan_id', $selectedPejantanId ?? null) == $p->id ? 'selected' : '' }}>
                        ♂ {{ $p->nama }} {{ $p->ras ? '(' . $p->ras . ')' : '' }}
                    </option>
                @endforeach
            </select>
            <p id="{{ $prefix }}help_tanpa_pejantan" class="text-[10px] text-slate-400 mt-1 hidden">
                Pejantan utama dinonaktifkan. Bapak dapat ditentukan per anak di bawah (atau dibiarkan kosong).
            </p>
            @error('pejantan_id')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- 3. Banner Suggestion: Anak Yang Sudah Terdaftar di Kandang dari Induk / Pejantan -->
    <div id="{{ $prefix }}panel_relasi" class="hidden">
        <div class="bg-blue-50/90 border border-blue-200 rounded-2xl p-3.5 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-extrabold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="ti ti-link text-sm text-blue-600"></i>
                    <span>Anak Terdaftar dari Induk Ini</span>
                </span>
                <button type="button" id="{{ $prefix }}btn_masukkan_semua"
                    class="text-[11px] font-bold text-blue-700 hover:text-blue-900 bg-white border border-blue-300 px-2.5 py-1 rounded-xl shadow-2xs active:scale-95 transition">
                    + Masukkan Semua
                </button>
            </div>
            <p class="text-[11px] text-blue-700">
                Kambing di bawah sudah memiliki relasi dengan induk ini di database. Klik untuk langsung memuat datanya tanpa ketik ulang:
            </p>
            <div id="{{ $prefix }}list_relasi" class="flex flex-wrap gap-1.5 pt-1"></div>
        </div>
    </div>

    <!-- 4. Section Data Anak Kambing -->
    <div class="border-t border-slate-100 pt-3.5 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Data Anak Kambing</h4>
                <p class="text-[11px] text-slate-500">Ketik nama untuk memuat kambing yang ada atau tambah cempe baru</p>
            </div>
            <button type="button" id="{{ $prefix }}btn_tambah_anak"
                class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-300 font-bold px-3 py-1.5 rounded-xl text-xs transition active:scale-95 shadow-2xs">
                <i class="ti ti-plus text-xs"></i>
                <span>+ Tambah Anak</span>
            </button>
        </div>

        <div id="{{ $prefix }}container_anak" class="space-y-3">
            <!-- Dynamic Child Items -->
        </div>
    </div>

    <!-- 5. Catatan Peristiwa Kelahiran -->
    <div>
        <label for="{{ $prefix }}catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Catatan Peristiwa Kelahiran
        </label>
        <textarea name="catatan" id="{{ $prefix }}catatan" rows="2"
            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
            placeholder="Kondisi proses kelahiran, bantuan medis, kondisi anak...">{{ old('catatan') }}</textarea>
        @error('catatan')
            <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- 6. Form Actions -->
    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
        @if ($isModal)
            <button type="button" onclick="closeBottomSheet('sheetTambahKelahiran')"
                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
        @else
            <a href="{{ route('kelahirans.index') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition text-center">
                Batal
            </a>
        @endif
        <button type="submit"
            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
            Simpan Kelahiran & Anak
        </button>
    </div>
</div>

<!-- Datalist semua kambing untuk autocomplete nama -->
<datalist id="{{ $prefix }}datalist_kambings">
    @foreach ($allKambings as $k)
        <option value="{{ $k->nama }}">{{ $k->nama }} ({{ $k->jenis_kelamin === 'jantan' ? '♂ Jantan' : '♀ Betina' }}{{ $k->ras ? ' · ' . $k->ras : '' }})</option>
    @endforeach
</datalist>

<script>
(function() {
    const P = "{{ $prefix }}";
    const allKambings = {!! $allKambingsJson !!};
    const pejantansList = {!! $pejantansJson !!};

    const elPerkawinan = document.getElementById(P + 'perkawinan_id');
    const elInduk = document.getElementById(P + 'induk_id');
    const elPejantan = document.getElementById(P + 'pejantan_id');
    const elChkTanpaPejantan = document.getElementById(P + 'chk_tanpa_pejantan');
    const elHelpTanpaPejantan = document.getElementById(P + 'help_tanpa_pejantan');
    const elContainerAnak = document.getElementById(P + 'container_anak');
    const elBtnTambah = document.getElementById(P + 'btn_tambah_anak');
    const elPanelRelasi = document.getElementById(P + 'panel_relasi');
    const elListRelasi = document.getElementById(P + 'list_relasi');
    const elBtnMasukkanSemua = document.getElementById(P + 'btn_masukkan_semua');

    let currentRelasiKambings = [];

    // Helper: Cari kambing di memori lokal berdasarkan nama
    function findKambingByName(nama) {
        if (!nama) return null;
        const clean = nama.trim().toLowerCase();
        return allKambings.find(k => k.nama && k.nama.trim().toLowerCase() === clean) || null;
    }

    // Update state Pejantan Utama (Disabled vs Enabled)
    function updatePejantanUtamaState() {
        if (!elPejantan) return;
        const isTanpa = elChkTanpaPejantan && elChkTanpaPejantan.checked;
        elPejantan.disabled = isTanpa;
        if (isTanpa) {
            elPejantan.value = '';
            if (elHelpTanpaPejantan) elHelpTanpaPejantan.classList.remove('hidden');
        } else {
            if (elHelpTanpaPejantan) elHelpTanpaPejantan.classList.add('hidden');
        }
        updateAllChildFatherOptions();
        refreshRelasiSuggestions();
    }

    if (elChkTanpaPejantan) {
        elChkTanpaPejantan.addEventListener('change', updatePejantanUtamaState);
    }

    // Perkawinan select -> auto isi induk & pejantan
    if (elPerkawinan) {
        elPerkawinan.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                if (elChkTanpaPejantan) {
                    elChkTanpaPejantan.checked = false;
                    updatePejantanUtamaState();
                }
                const bId = opt.getAttribute('data-betina');
                const pId = opt.getAttribute('data-pejantan');
                if (bId && elInduk) elInduk.value = bId;
                if (pId && elPejantan) elPejantan.value = pId;
                updateAllChildFatherOptions();
                refreshRelasiSuggestions();
            }
        });
    }

    if (elInduk) elInduk.addEventListener('change', refreshRelasiSuggestions);
    if (elPejantan) elPejantan.addEventListener('change', function() {
        updateAllChildFatherOptions();
        refreshRelasiSuggestions();
    });

    // Helper: dapatkan label untuk opsi 'Ikuti Pejantan Utama'
    function getMainPejantanLabel() {
        if (elChkTanpaPejantan && elChkTanpaPejantan.checked) {
            return '-- Tanpa Bapak / Kosong --';
        }
        if (elPejantan && elPejantan.value && elPejantan.selectedOptions[0]) {
            const pNama = elPejantan.selectedOptions[0].textContent.trim();
            return `-- Ikuti Pejantan Utama (${pNama}) --`;
        }
        return '-- Ikuti Pejantan Utama (Belum dipilih) --';
    }

    // Update teks opsi pertama di semua kartu anak
    function updateAllChildFatherOptions() {
        const defaultLabel = getMainPejantanLabel();
        const selects = elContainerAnak.querySelectorAll('.field-anak-pejantan');
        selects.forEach(sel => {
            if (sel.options[0]) {
                sel.options[0].textContent = defaultLabel;
            }
        });
    }

    // Refresh banner suggestion anak terdaftar
    function refreshRelasiSuggestions() {
        const indukId = elInduk ? elInduk.value : '';
        const pejantanId = (!elChkTanpaPejantan || !elChkTanpaPejantan.checked) && elPejantan ? elPejantan.value : '';

        if (!indukId) {
            elPanelRelasi.classList.add('hidden');
            elListRelasi.innerHTML = '';
            currentRelasiKambings = [];
            return;
        }

        // Cari kambing yang punya induk_id ini
        let matches = allKambings.filter(k => String(k.induk_id) === String(indukId));

        // Jika pejantan juga dipilih, persempit jika ada yang cocok dengan pejantan
        if (pejantanId) {
            const strict = matches.filter(k => String(k.pejantan_id) === String(pejantanId));
            if (strict.length > 0) {
                matches = strict;
            }
        }

        currentRelasiKambings = matches;

        if (matches.length === 0) {
            elPanelRelasi.classList.add('hidden');
            elListRelasi.innerHTML = '';
            return;
        }

        elListRelasi.innerHTML = '';
        matches.forEach(k => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-blue-300 text-xs font-bold text-blue-800 hover:bg-blue-100 active:scale-95 transition shadow-2xs';
            const jk = k.jenis_kelamin === 'jantan' ? '♂' : '♀';
            let tglStr = '';
            if (k.tanggal_lahir) {
                const parts = k.tanggal_lahir.split('-');
                if (parts.length === 3) tglStr = ` · ${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            btn.innerHTML = `<span>+ ${jk} ${k.nama}${tglStr}</span>`;
            btn.addEventListener('click', function() {
                addAnakItem({
                    kambing_id: k.id,
                    nama: k.nama,
                    jenis_kelamin: k.jenis_kelamin,
                    tanggal_lahir: k.tanggal_lahir,
                    ras: k.ras,
                    pejantan_id: k.pejantan_id
                });
            });
            elListRelasi.appendChild(btn);
        });

        elPanelRelasi.classList.remove('hidden');
    }

    if (elBtnMasukkanSemua) {
        elBtnMasukkanSemua.addEventListener('click', function() {
            if (!currentRelasiKambings.length) return;
            currentRelasiKambings.forEach(k => {
                addAnakItem({
                    kambing_id: k.id,
                    nama: k.nama,
                    jenis_kelamin: k.jenis_kelamin,
                    tanggal_lahir: k.tanggal_lahir,
                    ras: k.ras,
                    pejantan_id: k.pejantan_id
                });
            });
        });
    }

    // Buat HTML kartu anak
    function renderAnakCard(index, data = {}) {
        const today = new Date().toISOString().slice(0, 10);
        const nama = data.nama || '';
        const jk = data.jenis_kelamin || 'jantan';
        const tgl = data.tanggal_lahir || today;
        const ras = data.ras || (elInduk && elInduk.selectedOptions[0] ? elInduk.selectedOptions[0].dataset.ras || '' : '');
        const kambingId = data.kambing_id || '';
        const catatan = data.catatan || '';
        const pejantanChildId = data.pejantan_id !== undefined ? String(data.pejantan_id) : '';
        const namaPejantanLuar = data.nama_pejantan_luar || '';

        const isMatched = !!kambingId;
        const defaultPejantanLabel = getMainPejantanLabel();

        // Generate options for Pejantans
        let pejantanOptionsHtml = `<option value="">${defaultPejantanLabel}</option>`;
        pejantanOptionsHtml += `<option value="none" ${pejantanChildId === 'none' ? 'selected' : ''}>-- Tanpa Bapak / Tidak Diketahui --</option>`;

        pejantansList.forEach(p => {
            const isSel = pejantanChildId && String(p.id) === pejantanChildId ? 'selected' : '';
            pejantanOptionsHtml += `<option value="${p.id}" ${isSel}>♂ ${p.nama} ${p.ras ? '(' + p.ras + ')' : ''}</option>`;
        });

        const isLuar = !!namaPejantanLuar || pejantanChildId === '__luar__';
        pejantanOptionsHtml += `<option value="__luar__" ${isLuar ? 'selected' : ''}>+ Pejantan Luar Kandang (Ketik Nama)</option>`;

        const card = document.createElement('div');
        card.className = 'item-anak-card bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-3 transition';
        card.dataset.index = index;

        card.innerHTML = `
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                <span class="text-xs font-extrabold text-slate-700 uppercase judul-anak-card">Anak #${index + 1}</span>
                <button type="button" class="btn-hapus-anak text-xs font-semibold text-rose-600 hover:text-rose-800">
                    Hapus
                </button>
            </div>

            <input type="hidden" name="anak[${index}][kambing_id]" class="field-kambing-id" value="${kambingId}">

            <!-- VIEW 1: Form Input Mode (Saat kambing belum dipilih atau sedang diedit) -->
            <div class="box-child-edit ${isMatched ? 'hidden' : 'space-y-3'}">
                <!-- Nama Anak -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                        Nama Anak <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="anak[${index}][nama]" list="${P}datalist_kambings" value="${nama}"
                        autocomplete="off"
                        class="field-nama w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        placeholder="Ketik nama anak atau pilih kambing yang sudah ada...">
                    <p class="text-[10px] text-slate-400 mt-1">Pilih dari saran nama jika anak sudah terdaftar di kandang</p>
                </div>

                <!-- Jenis Kelamin & Tanggal Lahir -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <select name="anak[${index}][jenis_kelamin]"
                            class="field-jk w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="jantan" ${jk === 'jantan' ? 'selected' : ''}>♂ Jantan</option>
                            <option value="betina" ${jk === 'betina' ? 'selected' : ''}>♀ Betina</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                            Tanggal Lahir <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="anak[${index}][tanggal_lahir]" value="${tgl}"
                            class="field-tgl w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Ras -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Ras (Opsional)</label>
                    <input type="text" name="anak[${index}][ras]" value="${ras}" list="list-ras"
                        class="field-ras w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        placeholder="Ikuti ras induk jika kosong">
                </div>
            </div>

            <!-- VIEW 2: Selected Child Card View (Collapsed saat kambing terpilih dari database) -->
            <div class="box-child-selected ${isMatched ? '' : 'hidden'} bg-emerald-50/90 border border-emerald-300 rounded-xl p-3 flex items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="badge-jk-icon w-8 h-8 rounded-lg ${jk === 'jantan' ? 'bg-blue-600' : 'bg-pink-600'} text-white font-extrabold text-sm flex items-center justify-center flex-shrink-0 shadow-2xs">
                        ${jk === 'jantan' ? '♂' : '♀'}
                    </div>
                    <div class="overflow-hidden leading-tight">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="preview-nama text-xs font-extrabold text-emerald-950 truncate">${nama}</span>
                            <span class="text-[9px] font-extrabold bg-emerald-200 text-emerald-900 px-1.5 py-0.5 rounded uppercase">Sudah Ada di Kandang</span>
                        </div>
                        <p class="preview-info text-[11px] text-emerald-800 mt-0.5">
                            Lahir: <span class="preview-tgl font-semibold">${tgl}</span> · Ras: <span class="preview-ras font-semibold">${ras || '-'}</span>
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-ubah-anak text-[11px] font-bold text-emerald-800 hover:text-emerald-950 bg-white border border-emerald-300 px-2.5 py-1.5 rounded-lg shadow-2xs hover:bg-emerald-100 active:scale-95 transition whitespace-nowrap">
                    Ubah
                </button>
            </div>

            <!-- BAGIAN BAPAK / PEJANTAN ANAK (Selalu Aktif & Terlihat) -->
            <div class="pt-2 border-t border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                        Bapak / Pejantan Anak Ini
                    </label>
                    <span class="text-[10px] text-slate-500">Bisa diatur beda tiap anak</span>
                </div>

                <select name="anak[${index}][pejantan_id]"
                    class="field-anak-pejantan w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    ${pejantanOptionsHtml}
                </select>

                <!-- Input Khusus Jika Pejantan Luar Kandang -->
                <div class="box-pejantan-luar ${isLuar ? '' : 'hidden'} space-y-1">
                    <input type="text" name="anak[${index}][nama_pejantan_luar]" value="${namaPejantanLuar}"
                        class="field-nama-pejantan-luar w-full px-3 py-2 bg-amber-50/80 border border-amber-300 rounded-xl text-xs font-semibold text-amber-950 focus:outline-none focus:ring-2 focus:ring-amber-500 placeholder-amber-500/70"
                        placeholder="Ketik nama pejantan luar (misal: Si Hitam - Kambing Pak RT)...">
                    <p class="text-[10px] text-amber-700">
                        Pejantan luar akan otomatis dicatat ke sistem dan terhubung di pohon silsilah keluarga besar.
                    </p>
                </div>
            </div>

            <!-- Catatan Anak -->
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Catatan Anak (Opsional)</label>
                <input type="text" name="anak[${index}][catatan]" value="${catatan}"
                    class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    placeholder="Ciri fisik khusus, warna bulu, bobot lahir...">
            </div>
        `;

        // Element references
        const boxEdit = card.querySelector('.box-child-edit');
        const boxSelected = card.querySelector('.box-child-selected');
        const inputNama = card.querySelector('.field-nama');
        const inputKambingId = card.querySelector('.field-kambing-id');
        const inputJk = card.querySelector('.field-jk');
        const inputTgl = card.querySelector('.field-tgl');
        const inputRas = card.querySelector('.field-ras');
        const selectPejantan = card.querySelector('.field-anak-pejantan');
        const boxPejantanLuar = card.querySelector('.box-pejantan-luar');
        const inputPejantanLuar = card.querySelector('.field-nama-pejantan-luar');
        const btnUbah = card.querySelector('.btn-ubah-anak');

        const previewNama = card.querySelector('.preview-nama');
        const previewTgl = card.querySelector('.preview-tgl');
        const previewRas = card.querySelector('.preview-ras');
        const badgeJkIcon = card.querySelector('.badge-jk-icon');

        // Handler untuk pilihan Pejantan Anak
        selectPejantan.addEventListener('change', function() {
            if (this.value === '__luar__') {
                boxPejantanLuar.classList.remove('hidden');
                inputPejantanLuar.focus();
            } else {
                boxPejantanLuar.classList.add('hidden');
                inputPejantanLuar.value = '';
            }
        });

        // Function: Switch to Collapsed Selected View
        function switchToSelected(kambing) {
            inputKambingId.value = kambing.id;
            inputNama.value = kambing.nama;
            if (kambing.jenis_kelamin) inputJk.value = kambing.jenis_kelamin;
            if (kambing.tanggal_lahir) inputTgl.value = kambing.tanggal_lahir;
            if (kambing.ras) inputRas.value = kambing.ras;

            previewNama.textContent = kambing.nama;
            previewTgl.textContent = kambing.tanggal_lahir || '-';
            previewRas.textContent = kambing.ras || '-';

            const isJ = kambing.jenis_kelamin === 'jantan';
            badgeJkIcon.textContent = isJ ? '♂' : '♀';
            badgeJkIcon.className = `badge-jk-icon w-8 h-8 rounded-lg ${isJ ? 'bg-blue-600' : 'bg-pink-600'} text-white font-extrabold text-sm flex items-center justify-center flex-shrink-0 shadow-2xs`;

            boxEdit.classList.add('hidden');
            boxSelected.classList.remove('hidden');

            // Jika kambing punya bapak di DB, set select pejantan anak
            if (kambing.pejantan_id) {
                selectPejantan.value = kambing.pejantan_id;
                boxPejantanLuar.classList.add('hidden');
            }

            // Jika induk form belum dipilih dan kambing punya induk_id, auto-select induk form
            if (kambing.induk_id && elInduk && !elInduk.value) {
                elInduk.value = kambing.induk_id;
                refreshRelasiSuggestions();
            }
        }

        // Function: Switch back to Edit Mode
        function switchToEdit() {
            inputKambingId.value = '';
            boxSelected.classList.add('hidden');
            boxEdit.classList.remove('hidden');
            inputNama.focus();
        }

        btnUbah.addEventListener('click', switchToEdit);

        // Realtime typing check: jika user memilih atau mengetik nama kambing terdaftar
        function checkNamaMatch() {
            const val = inputNama.value.trim();
            if (!val) return;
            const matched = findKambingByName(val);
            if (matched) {
                switchToSelected(matched);
            }
        }

        inputNama.addEventListener('change', checkNamaMatch);
        inputNama.addEventListener('input', function() {
            const val = this.value.trim();
            const matched = findKambingByName(val);
            if (matched) {
                switchToSelected(matched);
            }
        });

        // Jika initial data sudah punya kambing_id
        if (data.kambing_id) {
            const matched = allKambings.find(k => String(k.id) === String(data.kambing_id));
            if (matched) {
                switchToSelected(matched);
            }
        }

        return card;
    }

    // Tambah item anak
    function addAnakItem(data = {}) {
        // Cek apakah kambing_id sudah ada di form
        if (data.kambing_id) {
            const existing = elContainerAnak.querySelector(`input.field-kambing-id[value="${data.kambing_id}"]`);
            if (existing) {
                existing.closest('.item-anak-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }

        // Jika hanya ada 1 kartu anak dan masih kosong nama-nya, isi kartu tersebut
        const currentCards = elContainerAnak.querySelectorAll('.item-anak-card');
        if (currentCards.length === 1) {
            const firstInputNama = currentCards[0].querySelector('.field-nama');
            const firstKambingId = currentCards[0].querySelector('.field-kambing-id');
            if (firstInputNama && !firstInputNama.value.trim() && !firstKambingId.value && data.nama) {
                firstInputNama.value = data.nama;
                firstInputNama.dispatchEvent(new Event('change'));
                if (data.pejantan_id) {
                    const selPejantan = currentCards[0].querySelector('.field-anak-pejantan');
                    if (selPejantan) selPejantan.value = data.pejantan_id;
                }
                return;
            }
        }

        const newIndex = elContainerAnak.querySelectorAll('.item-anak-card').length;
        const card = renderAnakCard(newIndex, data);
        elContainerAnak.appendChild(card);
        updateCardNumbers();

        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Update numbering & name attributes
    function updateCardNumbers() {
        const cards = elContainerAnak.querySelectorAll('.item-anak-card');
        cards.forEach((card, idx) => {
            card.dataset.index = idx;
            const judul = card.querySelector('.judul-anak-card');
            if (judul) judul.textContent = `Anak #${idx + 1}`;

            const btnHapus = card.querySelector('.btn-hapus-anak');
            if (btnHapus) {
                btnHapus.classList.toggle('hidden', cards.length <= 1);
            }

            card.querySelectorAll('input, select').forEach(field => {
                const name = field.getAttribute('name');
                if (name) {
                    field.setAttribute('name', name.replace(/anak\[\d+\]/, `anak[${idx}]`));
                }
            });
        });
    }

    if (elBtnTambah) {
        elBtnTambah.addEventListener('click', function() {
            addAnakItem({});
        });
    }

    elContainerAnak.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-hapus-anak')) {
            const card = e.target.closest('.item-anak-card');
            if (card) {
                card.remove();
                updateCardNumbers();
            }
        }
    });

    // Inisialisasi kartu pertama saat load
    if (elContainerAnak.children.length === 0) {
        addAnakItem({});
    }

    // Inisialisasi state awal
    updatePejantanUtamaState();

    // Jika sudah ada induk yang dipilih saat load, trigger refresh
    if (elInduk && elInduk.value) {
        refreshRelasiSuggestions();
    }
})();
</script>
