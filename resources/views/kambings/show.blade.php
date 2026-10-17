@extends('layouts.app')

@section('title', 'Detail Kambing - ' . $kambing->nama)

@section('content')
<div class="space-y-4">
    <!-- Top Action & Navigation Bar -->
    <div class="flex items-center justify-between gap-2">
        <a href="{{ route('kambings.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-emerald-600 transition p-1">
            <i class="ti ti-arrow-left text-sm"></i>
            <span>Kembali</span>
        </a>

        <div class="flex items-center gap-1.5">
            <x-action-button href="{{ route('silsilah.index', $kambing) }}" variant="secondary" size="sm" icon="ti ti-git-fork">
                Lihat Silsilah
            </x-action-button>
            <button type="button" onclick="openBottomSheet('sheetEditKambing')"
                    class="inline-flex items-center justify-center gap-1.5 font-bold transition active:scale-95 shadow-xs bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-xl text-xs">
                <i class="ti ti-edit"></i>
                <span>Edit</span>
            </button>
            <form action="{{ route('kambings.destroy', $kambing) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, this, 'Hapus Kambing?', 'Apakah Anda yakin ingin menghapus data kambing {{ $kambing->nama }}?');">
                @csrf
                @method('DELETE')
                <x-action-button type="submit" variant="danger" size="sm" icon="ti ti-trash">
                    Hapus
                </x-action-button>
            </form>
        </div>
    </div>

    <!-- 1. Identitas & Data Dasar Card -->
    <x-section-card>
        <div class="flex flex-col sm:flex-row gap-4 items-center sm:items-start text-center sm:text-left">
            <!-- Foto Avatar -->
            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-emerald-50 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-4xl shadow-sm">
                @if ($kambing->foto)
                    <img src="{{ asset('storage/' . $kambing->foto) }}" alt="{{ $kambing->nama }}" class="w-full h-full object-cover">
                @else
                    <span>🐐</span>
                @endif
            </div>

            <!-- Detail Head -->
            <div class="flex-1 w-full space-y-2">
                <div class="flex items-center justify-center sm:justify-between gap-2 flex-wrap">
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $kambing->nama }}</h1>
                    <x-status-badge :status="$kambing->status" />
                </div>

                <!-- Info Kesiapan Kawin alert jika betina & hidup & tersedia kesiapan_kawin_info -->
                @if ($kambing->jenis_kelamin === 'betina' && $kambing->status === 'hidup' && $kambing->kesiapan_kawin_info)
                    @php $info = $kambing->kesiapan_kawin_info; @endphp
                    <div class="p-3 rounded-xl border text-xs flex items-center justify-between gap-2 text-left {{ $info['status'] === 'siap' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : ($info['status'] === 'pemulihan' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-slate-50 border-slate-200 text-slate-700') }}">
                        <div>
                            <div class="font-bold flex items-center gap-1">
                                <span>💕 Kesiapan Kawin:</span>
                                <span>{{ $info['label'] }}</span>
                            </div>
                            <p class="mt-0.5 text-[11px] opacity-90">
                                @if ($info['status'] === 'siap')
                                    Siap dikawinkan sejak {{ $info['tanggal_mulai_siap']->format('d M Y') }} ({{ $info['lama_sejak_melahirkan'] }} hari sejak melahirkan terakhir)
                                @elseif ($info['status'] === 'pemulihan')
                                    Masa pemulihan: sisa {{ $info['sisa_hari'] }} hari lagi (estimasi siap: {{ $info['estimasi_tanggal_siap']->format('d M Y') }})
                                @else
                                    {{ $info['keterangan'] }}
                                @endif
                            </p>
                        </div>
                        @if ($info['status'] === 'siap')
                            <a href="{{ route('perkawinans.create', ['betina_id' => $kambing->id]) }}" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-[11px] whitespace-nowrap shadow-xs">
                                + Kawinkan
                            </a>
                        @endif
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3 pt-2 text-left">
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Kelamin</span>
                        <span class="text-xs font-bold {{ $kambing->jenis_kelamin === 'jantan' ? 'text-blue-600' : 'text-pink-600' }}">
                            {{ $kambing->jenis_kelamin === 'jantan' ? '♂ Jantan' : '♀ Betina' }}
                        </span>
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Ras</span>
                        <span class="text-xs font-bold text-slate-800">
                            {{ $kambing->ras ?? 'Tidak dicatat' }}
                        </span>
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Tanggal Lahir</span>
                        <span class="text-xs font-bold text-slate-800">
                            {{ $kambing->tanggal_lahir ? $kambing->tanggal_lahir->format('d M Y') : 'Tidak dicatat' }}
                        </span>
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Terdaftar</span>
                        <span class="text-xs font-bold text-slate-800">
                            {{ $kambing->created_at->format('d M Y') }}
                        </span>
                    </div>
                </div>

                @if ($kambing->catatan)
                    <div class="pt-2 text-left">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Catatan</span>
                        <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line">
                            {{ $kambing->catatan }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </x-section-card>

    <!-- 2. Orang Tua & Silsilah -->
    <x-section-card title="Orang Tua (Silsilah)" icon="ti ti-users" actionText="Atur Relasi" actionHref="{{ route('relasi.index', ['kambing_id' => $kambing->id]) }}">
        @if ($kambing->induk || $kambing->pejantan)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="border border-pink-200 bg-pink-50/50 rounded-xl p-3 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-pink-600 uppercase tracking-wider block">Induk (Ibu)</span>
                        @if ($kambing->induk)
                            <a href="{{ route('kambings.show', $kambing->induk) }}" class="font-bold text-slate-900 hover:text-pink-600 text-sm transition">
                                ♀ {{ $kambing->induk->nama }}
                            </a>
                            <span class="text-xs text-slate-400 block">{{ $kambing->induk->ras ?? '-' }}</span>
                        @else
                            <span class="text-xs text-slate-400 italic">Tidak dicatat</span>
                        @endif
                    </div>
                </div>

                <div class="border border-blue-200 bg-blue-50/50 rounded-xl p-3 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">Pejantan (Ayah)</span>
                        @if ($kambing->pejantan)
                            <a href="{{ route('kambings.show', $kambing->pejantan) }}" class="font-bold text-slate-900 hover:text-blue-600 text-sm transition">
                                ♂ {{ $kambing->pejantan->nama }}
                            </a>
                            <span class="text-xs text-slate-400 block">{{ $kambing->pejantan->ras ?? '-' }}</span>
                        @else
                            <span class="text-xs text-slate-400 italic">Tidak dicatat</span>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-2">
                <div>
                    <span class="text-xs font-bold text-slate-700 block">Belum Terhubung Orang Tua</span>
                    <span class="text-[11px] text-slate-400">Hubungkan kambing ini ke pejantan dan induk di silsilah kandang.</span>
                </div>
                <a href="{{ route('relasi.index', ['kambing_id' => $kambing->id]) }}"
                   class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs shadow-xs transition shrink-0">
                    + Hubungkan
                </a>
            </div>
        @endif
    </x-section-card>

    <!-- 3. Riwayat Kelahiran -->
    @php
        $riwayatKelahirans = $kambing->jenis_kelamin === 'betina'
            ? $kambing->kelahiranSebagaiInduk
            : $kambing->kelahiranSebagaiPejantan;
    @endphp

    <x-section-card
        title="Riwayat Kelahiran"
        icon="ti ti-mood-kid"
        :actionText="$kambing->jenis_kelamin === 'betina' ? '+ Catat' : null"
        :actionHref="$kambing->jenis_kelamin === 'betina' ? route('kelahirans.create', ['induk_id' => $kambing->id]) : null"
    >
        @if ($riwayatKelahirans->isEmpty())
            <p class="text-xs text-slate-500 italic py-1">Belum ada riwayat kelahiran yang dicatat.</p>
        @else
            <div class="space-y-3">
                @foreach ($riwayatKelahirans as $k)
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                            <div class="text-xs font-bold text-slate-900">
                                {{ $k->tanggal_kelahiran->format('d M Y') }}
                                <span class="text-slate-500 font-normal">
                                    • Pasangan:
                                    @php $pasangan = $kambing->jenis_kelamin === 'betina' ? $k->pejantan : $k->induk; @endphp
                                    <a href="{{ route('kambings.show', $pasangan) }}" class="font-semibold text-emerald-600 hover:underline">
                                        {{ $pasangan->nama }}
                                    </a>
                                </span>
                            </div>
                            <a href="{{ route('kelahirans.show', $k) }}" class="text-[11px] font-semibold text-emerald-600 hover:underline">
                                Detail &rarr;
                            </a>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Anak lahir:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($k->anaks as $anak)
                                    <a href="{{ route('kambings.show', $anak) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-white border border-slate-200 hover:border-emerald-300 text-slate-800 transition">
                                        <span>{{ $anak->jenis_kelamin === 'jantan' ? '♂' : '♀' }}</span>
                                        <span>{{ $anak->nama }}</span>
                                    </a>
                                @empty
                                    <span class="text-xs text-slate-400 italic">Tidak ada anak terdaftar</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-section-card>

    <!-- 4. Riwayat Perkawinan -->
    @php
        $riwayatPerkawinans = $kambing->jenis_kelamin === 'betina'
            ? $kambing->perkawinanBetina
            : $kambing->perkawinanPejantan;
    @endphp

    <x-section-card
        title="Riwayat Perkawinan"
        icon="ti ti-heart"
        actionText="+ Catat"
        :actionHref="route('perkawinans.create', $kambing->jenis_kelamin === 'betina' ? ['betina_id' => $kambing->id] : ['pejantan_id' => $kambing->id])"
    >
        @if ($riwayatPerkawinans->isEmpty())
            <p class="text-xs text-slate-500 italic py-1">Belum ada riwayat perkawinan yang dicatat.</p>
        @else
            <div class="space-y-2">
                @foreach ($riwayatPerkawinans as $p)
                    @php $pasangan = $kambing->jenis_kelamin === 'betina' ? $p->pejantan : $p->betina; @endphp
                    <div class="p-3 border border-slate-200 rounded-xl bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="text-xs">
                            <span class="font-bold text-slate-900">{{ $p->tanggal_kawin->format('d M Y') }}</span>
                            <span class="text-slate-500">dengan</span>
                            <a href="{{ route('kambings.show', $pasangan) }}" class="font-bold text-emerald-600 hover:underline">
                                {{ $pasangan->nama }}
                            </a>
                            <span class="text-slate-400 text-[11px]">({{ $pasangan->jenis_kelamin === 'jantan' ? 'Pejantan' : 'Betina' }})</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-slate-500 text-[11px]">Lahir: <strong>{{ $p->perkiraan_lahir->format('d M Y') }}</strong></span>
                            <x-status-badge :status="$p->status" />
                            <a href="{{ route('perkawinans.show', $p) }}" class="text-emerald-600 font-semibold hover:underline">Detail</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-section-card>

    <!-- 5. Riwayat Sakit -->
    <x-section-card
        title="Riwayat Sakit"
        icon="ti ti-stethoscope"
        actionText="+ Catat Sakit"
        :actionHref="route('riwayat-sakit.create', ['kambing_id' => $kambing->id])"
    >
        @if ($kambing->riwayatSakit->isEmpty())
            <p class="text-xs text-slate-500 italic py-1">Belum ada riwayat sakit.</p>
        @else
            <div class="space-y-2.5">
                @foreach ($kambing->riwayatSakit as $r)
                    <div class="p-3 border border-slate-200 rounded-xl bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">{{ $r->tanggal_mulai ? $r->tanggal_mulai->format('d M Y') : '-' }}</span>
                                <x-status-badge :status="$r->status" />
                            </div>
                            @if ($r->catatan)
                                <p class="text-xs text-slate-600 mt-1 whitespace-pre-line">{{ $r->catatan }}</p>
                            @endif
                            <div class="text-[11px] text-slate-500 mt-1">
                                <span>Durasi: <strong>{{ $r->durasi_hari }} hari</strong></span>
                                @if ($r->tanggal_selesai)
                                    <span> • Selesai: {{ $r->tanggal_selesai->format('d M Y') }}</span>
                                @endif
                            </div>
                        </div>

                        @if ($r->status === 'sedang_sakit')
                            <div>
                                <a href="{{ route('riwayat-sakit.index') }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-xs font-semibold hover:bg-blue-100 transition inline-block">
                                    Konfirmasi
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-section-card>

    <!-- 6. Riwayat & Jadwal Perawatan -->
    <x-section-card
        title="Perawatan & Rekam Jejak"
        icon="ti ti-pill"
        actionText="+ Catat / Jadwal"
        :actionHref="route('pengingat-perawatan.create', ['kambing_id' => $kambing->id])"
    >
        @if ($kambing->pengingatPerawatan->isEmpty())
            <p class="text-xs text-slate-500 italic py-1">Belum ada rekam jejak atau jadwal perawatan.</p>
        @else
            <div class="space-y-2.5">
                @foreach ($kambing->pengingatPerawatan->sortByDesc('tanggal') as $p)
                    <div class="p-3 border border-slate-200 rounded-xl bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">{{ $p->jenis_perawatan }}</span>
                                <x-status-badge :status="$p->status" />
                            </div>
                            <div class="text-[11px] text-slate-500">
                                @if($p->status === 'sudah_diberikan')
                                    <span>Selesai: <strong>{{ $p->tanggal_pemberian ? $p->tanggal_pemberian->format('d M Y') : ($p->tanggal ? $p->tanggal->format('d M Y') : '-') }}</strong></span>
                                @else
                                    <span>Jadwal: <strong>{{ $p->tanggal ? $p->tanggal->format('d M Y') : '-' }}</strong></span>
                                @endif
                                @if($p->dosis)
                                    <span> • Dosis: {{ $p->dosis }}</span>
                                @endif
                            </div>
                            @if ($p->keterangan)
                                <p class="text-xs text-slate-600 bg-white p-2 rounded-lg border border-slate-100">
                                    {{ $p->keterangan }}
                                </p>
                            @endif
                        </div>

                        @if ($p->status === 'belum_diberikan')
                            <div class="shrink-0 flex items-center gap-2">
                                <form action="{{ route('pengingat-perawatan.selesai', $p) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="tanggal_pemberian" value="{{ date('Y-m-d') }}">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 active:scale-95 transition shadow-xs">
                                        ✓ Sudah Diberikan
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-section-card>
</div>

<!-- Bottom Sheet Edit Data Kambing -->
<x-bottom-sheet id="sheetEditKambing" title="Edit Data: {{ $kambing->nama }}">
    <form action="{{ route('kambings.update', $kambing) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <!-- Nama Kambing -->
        <div>
            <label for="edit_nama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Nama Kambing <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nama" id="edit_nama" value="{{ old('nama', $kambing->nama) }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            @error('nama')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Kelamin & Ras -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="edit_jenis_kelamin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Jenis Kelamin <span class="text-rose-500">*</span>
                </label>
                <select name="jenis_kelamin" id="edit_jenis_kelamin" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="jantan" {{ old('jenis_kelamin', $kambing->jenis_kelamin) === 'jantan' ? 'selected' : '' }}>♂ Jantan</option>
                    <option value="betina" {{ old('jenis_kelamin', $kambing->jenis_kelamin) === 'betina' ? 'selected' : '' }}>♀ Betina</option>
                </select>
                @error('jenis_kelamin')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="edit_ras" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Ras / Jenis
                </label>
                <input type="text" name="ras" id="edit_ras" value="{{ old('ras', $kambing->ras) }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @error('ras')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Tanggal Lahir & Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="edit_tanggal_lahir" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Tanggal Lahir
                </label>
                <input type="date" name="tanggal_lahir" id="edit_tanggal_lahir"
                    value="{{ old('tanggal_lahir', $kambing->tanggal_lahir ? $kambing->tanggal_lahir->format('Y-m-d') : '') }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @error('tanggal_lahir')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="edit_status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Status <span class="text-rose-500">*</span>
                </label>
                <select name="status" id="edit_status" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="hidup" {{ old('status', $kambing->status) === 'hidup' ? 'selected' : '' }}>Hidup</option>
                    <option value="mati" {{ old('status', $kambing->status) === 'mati' ? 'selected' : '' }}>Mati</option>
                    <option value="dijual" {{ old('status', $kambing->status) === 'dijual' ? 'selected' : '' }}>Dijual</option>
                </select>
                @error('status')
                    <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Foto -->
        <div>
            <label for="edit_foto" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Ganti Foto
            </label>
            <div id="container-preview-show-edit" class="hidden mb-2">
                <div class="relative inline-block w-20 h-20 rounded-2xl overflow-hidden border-2 border-emerald-500 shadow-sm bg-slate-50">
                    <img id="preview-foto-show-edit" src="" alt="Preview Foto Baru" class="w-full h-full object-cover">
                    <button type="button" onclick="clearFotoUpload('edit_foto', 'container-preview-show-edit', 'preview-foto-show-edit')"
                            class="absolute top-1 right-1 bg-black/60 hover:bg-black/80 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                        <i class="ti ti-x text-[10px]"></i>
                    </button>
                </div>
            </div>
            @if ($kambing->foto)
                <div class="flex items-center gap-2 mb-2">
                    <img src="{{ asset('storage/' . $kambing->foto) }}" alt="Foto saat ini" class="w-10 h-10 object-cover rounded-lg border border-slate-200">
                    <span class="text-[11px] text-slate-500">Foto saat ini. Pilih file baru jika ingin mengganti.</span>
                </div>
            @endif
            <input type="file" name="foto" id="edit_foto" accept="image/*"
                data-preview="#preview-foto-show-edit"
                data-preview-container="#container-preview-show-edit"
                class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, WEBP, HEIC (Maksimal 15MB. Otomatis dioptimalkan).</p>
            @error('foto')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Catatan -->
        <div>
            <label for="edit_catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Catatan
            </label>
            <textarea name="catatan" id="edit_catatan" rows="3"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('catatan', $kambing->catatan) }}</textarea>
            @error('catatan')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetEditKambing')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Perbarui Data
            </button>
        </div>
    </form>
</x-bottom-sheet>

@if(request('action') === 'edit' || ($errors->any() && old('_method') === 'PUT'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetEditKambing');
    });
</script>
@endif
@endsection
