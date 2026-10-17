@php
    $prefix = $prefix ?? '';
    $isModal = $isModal ?? false;
    $today = date('Y-m-d');
    $allKambingsJson = isset($allKambings) ? $allKambings->toJson() : '[]';
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
            <label for="{{ $prefix }}pejantan_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Pejantan <span class="text-rose-500">*</span>
            </label>
            <select name="pejantan_id" id="{{ $prefix }}pejantan_id" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                <option value="">-- Pilih Pejantan --</option>
                @foreach ($pejantans as $p)
                    <option value="{{ $p->id }}" data-ras="{{ $p->ras }}"
                        {{ old('pejantan_id', $selectedPejantanId ?? null) == $p->id ? 'selected' : '' }}>
                        ♂ {{ $p->nama }} {{ $p->ras ? '(' . $p->ras . ')' : '' }}
                    </option>
                @endforeach
            </select>
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

    const elPerkawinan = document.getElementById(P + 'perkawinan_id');
    const elInduk = document.getElementById(P + 'induk_id');
    const elPejantan = document.getElementById(P + 'pejantan_id');
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

    // Helper: Cari kambing di memori lokal berdasarkan ID
    function findKambingById(id) {
        return allKambings.find(k => String(k.id) === String(id)) || null;
    }

    // Perkawinan select -> auto isi induk & pejantan
    if (elPerkawinan) {
        elPerkawinan.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                const bId = opt.getAttribute('data-betina');
                const pId = opt.getAttribute('data-pejantan');
                if (bId && elInduk) elInduk.value = bId;
                if (pId && elPejantan) elPejantan.value = pId;
                refreshRelasiSuggestions();
            }
        });
    }

    if (elInduk) elInduk.addEventListener('change', refreshRelasiSuggestions);
    if (elPejantan) elPejantan.addEventListener('change', refreshRelasiSuggestions);

    // Refresh banner suggestion anak terdaftar
    function refreshRelasiSuggestions() {
        const indukId = elInduk ? elInduk.value : '';
        const pejantanId = elPejantan ? elPejantan.value : '';

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
        const isMatched = !!kambingId;

        const card = document.createElement('div');
        card.className = 'item-anak-card bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-3';
        card.dataset.index = index;

        card.innerHTML = `
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                <span class="text-xs font-extrabold text-slate-700 uppercase judul-anak-card">Anak #${index + 1}</span>
                <button type="button" class="btn-hapus-anak text-xs font-semibold text-rose-600 hover:text-rose-800">
                    Hapus
                </button>
            </div>

            <input type="hidden" name="anak[${index}][kambing_id]" class="field-kambing-id" value="${kambingId}">

            <!-- Nama & Badge -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                    Nama Anak <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="anak[${index}][nama]" list="${P}datalist_kambings" value="${nama}" required
                    autocomplete="off"
                    class="field-nama w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    placeholder="Ketik nama atau pilih kambing yang sudah ada...">

                <!-- Indikator Auto-Match -->
                <div class="badge-matched ${isMatched ? '' : 'hidden'} mt-1.5 flex items-center gap-1.5 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg">
                    <i class="ti ti-check text-xs"></i>
                    <span>Kambing terdaftar di kandang (Data otomatis terisi)</span>
                </div>
            </div>

            <!-- Jenis Kelamin & Tanggal Lahir -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                        Jenis Kelamin <span class="text-rose-500">*</span>
                    </label>
                    <select name="anak[${index}][jenis_kelamin]" required
                        class="field-jk w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="jantan" ${jk === 'jantan' ? 'selected' : ''}>♂ Jantan</option>
                        <option value="betina" ${jk === 'betina' ? 'selected' : ''}>♀ Betina</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                        Tanggal Lahir <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="anak[${index}][tanggal_lahir]" value="${tgl}" required
                        class="field-tgl w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <span class="text-[10px] text-slate-400">Anak dengan tanggal berbeda dicatat sebagai kelahiran terpisah</span>
                </div>
            </div>

            <!-- Ras & Catatan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Ras (Opsional)</label>
                    <input type="text" name="anak[${index}][ras]" value="${ras}" list="list-ras"
                        class="field-ras w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        placeholder="Ikuti ras induk jika kosong">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Catatan Anak</label>
                    <input type="text" name="anak[${index}][catatan]" value="${catatan}"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        placeholder="Kondisi fisik, ciri khusus...">
                </div>
            </div>
        `;

        // Event listener saat user mengetik nama anak -> auto populate jika cocok kambing di database
        const inputNama = card.querySelector('.field-nama');
        const inputKambingId = card.querySelector('.field-kambing-id');
        const inputJk = card.querySelector('.field-jk');
        const inputTgl = card.querySelector('.field-tgl');
        const inputRas = card.querySelector('.field-ras');
        const badgeMatched = card.querySelector('.badge-matched');

        function onNamaChange() {
            const val = inputNama.value.trim();
            const matched = findKambingByName(val);

            if (matched) {
                inputKambingId.value = matched.id;
                if (matched.jenis_kelamin) inputJk.value = matched.jenis_kelamin;
                if (matched.tanggal_lahir) inputTgl.value = matched.tanggal_lahir;
                if (matched.ras) inputRas.value = matched.ras;
                badgeMatched.classList.remove('hidden');

                // Jika kambing punya bapak di DB, dan pejantan form belum dipilih -> set pejantan form
                if (matched.pejantan_id && elPejantan && !elPejantan.value) {
                    elPejantan.value = matched.pejantan_id;
                }
                // Jika kambing punya induk di DB, dan induk form belum dipilih -> set induk form
                if (matched.induk_id && elInduk && !elInduk.value) {
                    elInduk.value = matched.induk_id;
                }
            } else {
                inputKambingId.value = '';
                badgeMatched.classList.add('hidden');
                // Jika ras kosong, ambil default dari induk yang dipilih
                if (!inputRas.value && elInduk && elInduk.selectedOptions[0]) {
                    inputRas.value = elInduk.selectedOptions[0].dataset.ras || '';
                }
            }
        }

        inputNama.addEventListener('input', onNamaChange);
        inputNama.addEventListener('change', onNamaChange);

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
            if (firstInputNama && !firstInputNama.value.trim() && data.nama) {
                firstInputNama.value = data.nama;
                firstInputNama.dispatchEvent(new Event('change'));
                if (data.pejantan_id && elPejantan && !elPejantan.value) {
                    elPejantan.value = data.pejantan_id;
                }
                return;
            }
        }

        const newIndex = elContainerAnak.querySelectorAll('.item-anak-card').length;
        const card = renderAnakCard(newIndex, data);
        elContainerAnak.appendChild(card);
        updateCardNumbers();

        // Jika data punya pejantan, auto-select pejantan form
        if (data.pejantan_id && elPejantan && !elPejantan.value) {
            elPejantan.value = data.pejantan_id;
        }

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

    // Jika sudah ada induk yang dipilih saat load, trigger refresh
    if (elInduk && elInduk.value) {
        refreshRelasiSuggestions();
    }
})();
</script>
