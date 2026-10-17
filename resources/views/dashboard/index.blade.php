@extends('layouts.app')

@section('title', 'Dashboard Kandang')

@section('content')
@php
    $hour = (int) now()->format('H');
    $timeGreeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));

    $user = auth()->user();
    $rawName = trim($user?->name ?? '');
    if (empty($rawName) && !empty($user?->email)) {
        $rawName = explode('@', $user->email)[0];
        $rawName = str_replace(['.', '_', '-'], ' ', $rawName);
    }
    $firstName = !empty($rawName) ? explode(' ', $rawName)[0] : 'Peternak';
    $firstName = ucfirst($firstName);
@endphp

<div class="relative w-full">
    <!-- 1. Integrated Header & Green Hero Container (Sticky in background) -->
    <div class="sticky top-[56px] z-0 bg-gradient-to-b from-emerald-800 via-emerald-700 to-emerald-600 px-5 pt-3 pb-16 text-white relative overflow-hidden">
        <div class="absolute -right-3 -bottom-5 w-28 h-28 opacity-15 select-none pointer-events-none">
            <img src="{{ asset('images/goat_logo.png') }}" alt="" class="w-full h-full object-contain">
        </div>
        <div class="relative z-10 space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-emerald-100 backdrop-blur-xs">
                    <span>🟢</span> Kandang Reyot • Dashboard Kandang
                </span>
                <span class="text-[11px] text-emerald-200 font-medium">{{ now()->translatedFormat('d M Y') }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white mt-1">
                {{ $timeGreeting }}, {{ $firstName }}!
            </h1>
            <p class="text-xs text-emerald-100/90 font-medium">
                Kelola peternakan kambing dengan mudah &amp; praktis.
            </p>
        </div>
    </div>

    <!-- 2. White Sheet (Bottom Sheet Style) covering the lower part of green container -->
    <div class="relative z-10 -mt-8 bg-slate-50 rounded-t-[32px] shadow-[0_-10px_35px_rgba(0,0,0,0.14)] border-t border-slate-200/80 px-3.5 pt-2 pb-6 space-y-4">
        <!-- Sheet Handle Bar -->
        <div class="pt-1.5 pb-2 flex justify-center cursor-pointer select-none">
            <div class="w-12 h-1.5 rounded-full bg-slate-300"></div>
        </div>

        <!-- 2. Interactive Grid Menu (Fitur & Modul Utama) -->
        <div>
        <div class="flex items-center justify-between mb-2.5 px-0.5">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Fitur & Layanan</h2>
            <span class="text-[11px] text-slate-400 font-medium">6 Modul</span>
        </div>

        <div class="grid grid-cols-3 gap-2.5">
            <!-- 1. Perkawinan -->
            <a href="{{ route('perkawinans.index') }}" class="flex flex-col items-center justify-center p-3.5 bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:border-rose-400 active:scale-95 transition group text-center min-h-[88px]">
                <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mb-1.5 group-hover:scale-105 transition shadow-2xs">
                    <i class="ti ti-heart"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 leading-tight">Perkawinan</span>
            </a>

            <!-- 2. Kelahiran -->
            <a href="{{ route('kelahirans.index') }}" class="flex flex-col items-center justify-center p-3.5 bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:border-purple-400 active:scale-95 transition group text-center min-h-[88px]">
                <div class="w-11 h-11 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl mb-1.5 group-hover:scale-105 transition shadow-2xs">
                    <i class="ti ti-mood-kid"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 leading-tight">Kelahiran</span>
            </a>

            <!-- 3. Kesiapan Kawin -->
            <a href="{{ route('kesiapan-kawin.index') }}" class="flex flex-col items-center justify-center p-3.5 bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:border-pink-400 active:scale-95 transition group text-center min-h-[88px]">
                <div class="w-11 h-11 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-xl mb-1.5 group-hover:scale-105 transition shadow-2xs">
                    <i class="ti ti-activity"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 leading-tight">Kesiapan Kawin</span>
            </a>

            <!-- 4. Kesehatan (Riwayat Sakit) -->
            <a href="{{ route('riwayat-sakit.index') }}" class="flex flex-col items-center justify-center p-3.5 bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:border-rose-400 active:scale-95 transition group text-center min-h-[88px]">
                <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mb-1.5 group-hover:scale-105 transition shadow-2xs">
                    <i class="ti ti-stethoscope"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 leading-tight">Kesehatan</span>
            </a>

            <!-- 5. Semprot Kandang -->
            <a href="{{ route('semprot-kandang.index') }}" class="flex flex-col items-center justify-center p-3.5 bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:border-teal-400 active:scale-95 transition group text-center min-h-[88px]">
                <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl mb-1.5 group-hover:scale-105 transition shadow-2xs">
                    <i class="ti ti-droplet"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 leading-tight">Semprot Kandang</span>
            </a>

            <!-- 6. Kandang & Fasilitas -->
            <a href="#kondisi-kandang" class="flex flex-col items-center justify-center p-3.5 bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-400 active:scale-95 transition group text-center min-h-[88px]">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-1.5 group-hover:scale-105 transition shadow-2xs">
                    <i class="ti ti-building-warehouse"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 leading-tight">Kandang</span>
            </a>
        </div>
    </div>

    <!-- 4. Ringkasan Singkat Populasi -->
    <x-section-card title="Penghuni Kandang" icon="ti ti-building-warehouse" actionText="Semua Kambing" actionHref="{{ route('kambings.index', ['status' => 'hidup']) }}">
        <div class="grid grid-cols-3 gap-2.5">
            <!-- Penghuni Kandang (Aktif / Hidup Saja) -->
            <a href="{{ route('kambings.index', ['status' => 'hidup']) }}" class="p-3 rounded-2xl bg-emerald-50/80 border border-emerald-200/90 hover:bg-emerald-100 active:scale-95 transition">
                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-tight block">Penghuni Kandang</span>
                <span class="text-xl font-black text-emerald-950 leading-tight">{{ $totalHidup }}</span>
                <span class="text-[10px] text-emerald-600 block mt-0.5">ekor aktif di kandang</span>
            </a>

            <!-- Kelamin Penghuni Aktif -->
            <div class="p-3 rounded-2xl bg-blue-50/70 border border-blue-100">
                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-tight block">Kelamin</span>
                <div class="text-xs font-bold text-blue-900 mt-1 space-y-0.5">
                    <div>♂ Jantan: <strong>{{ $totalJantan }}</strong></div>
                    <div>♀ Betina: <strong>{{ $totalBetina }}</strong></div>
                </div>
            </div>

            <!-- Total Terdaftar Keseluruhan (Arsip & Aktif) -->
            <a href="{{ route('kambings.index') }}" class="p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100 active:scale-95 transition">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight block">Total Riwayat</span>
                <span class="text-xl font-black text-slate-800 leading-tight">{{ $totalKambing }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">termasuk arsip</span>
            </a>

            <!-- Dijual (Bukan Penghuni Kandang) -->
            <a href="{{ route('kambings.index', ['status' => 'dijual']) }}" class="p-2.5 rounded-xl bg-amber-50/60 border border-amber-100 text-center hover:bg-amber-100 active:scale-95 transition">
                <span class="text-[10px] font-bold text-amber-700 block">Dijual</span>
                <span class="text-sm font-black text-amber-900">{{ $totalDijual }}</span>
            </a>

            <!-- Mati (Bukan Penghuni Kandang) -->
            <a href="{{ route('kambings.index', ['status' => 'mati']) }}" class="p-2.5 rounded-xl bg-rose-50/60 border border-rose-100 text-center hover:bg-rose-100 active:scale-95 transition">
                <span class="text-[10px] font-bold text-rose-700 block">Mati</span>
                <span class="text-sm font-black text-rose-900">{{ $totalMati }}</span>
            </a>

            <!-- Anak Lahir -->
            <div class="p-2.5 rounded-xl bg-purple-50/60 border border-purple-100 text-center">
                <span class="text-[10px] font-bold text-purple-700 block">Total Anak</span>
                <span class="text-sm font-black text-purple-900">{{ $totalAnak }}</span>
            </div>
        </div>
    </x-section-card>

    <!-- 5. Pengingat Perawatan Aktif -->
    @php
        $hasReminders = $pengingatTerlambat->isNotEmpty() || $pengingatHariIni->isNotEmpty() || $pengingatBesok->isNotEmpty();
    @endphp
    <x-section-card title="Pengingat Perawatan" icon="ti ti-bell-ringing" actionText="Kelola" actionHref="{{ route('pengingat-perawatan.index') }}">
        @if (!$hasReminders)
            <div class="text-center py-5 text-slate-400 text-xs">
                <p>Tidak ada pengingat perawatan.</p>
            </div>
        @else
            <div class="space-y-3">
                <!-- Terlambat -->
                @if ($pengingatTerlambat->isNotEmpty())
                    <div>
                        <span class="text-[10px] font-extrabold text-rose-600 uppercase tracking-wider block mb-1.5">Terlambat</span>
                        <div class="space-y-2">
                            @foreach ($pengingatTerlambat as $p)
                                <div class="flex items-center justify-between p-3 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs">
                                    <div>
                                        <span class="font-bold text-slate-900">{{ optional($p->kambing)->nama }}</span> —
                                        <span class="font-medium text-rose-800">{{ $p->jenis_perawatan }}</span>
                                        <span class="text-[11px] text-slate-500 block mt-0.5">Jadwal: {{ $p->tanggal->format('d M Y') }}</span>
                                    </div>
                                    <form action="{{ route('pengingat-perawatan.selesai', $p) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="tanggal_pemberian" value="{{ date('Y-m-d') }}">
                                        <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-bold text-[11px] shadow-2xs transition">
                                            ✓ Sudah diberikan
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Hari Ini -->
                @if ($pengingatHariIni->isNotEmpty())
                    <div>
                        <span class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider block mb-1.5">Hari ini</span>
                        <div class="space-y-2">
                            @foreach ($pengingatHariIni as $p)
                                <div class="flex items-center justify-between p-3 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs">
                                    <div>
                                        <span class="font-bold text-slate-900">{{ optional($p->kambing)->nama }}</span> —
                                        <span class="font-medium text-emerald-800">{{ $p->jenis_perawatan }}</span>
                                    </div>
                                    <form action="{{ route('pengingat-perawatan.selesai', $p) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="tanggal_pemberian" value="{{ date('Y-m-d') }}">
                                        <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-bold text-[11px] shadow-2xs transition">
                                            ✓ Sudah diberikan
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Besok -->
                @if ($pengingatBesok->isNotEmpty())
                    <div>
                        <span class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider block mb-1.5">Besok</span>
                        <div class="space-y-2">
                            @foreach ($pengingatBesok as $p)
                                <div class="flex items-center justify-between p-3 rounded-2xl bg-blue-50 border border-blue-200/80 text-xs">
                                    <div>
                                        <span class="font-bold text-slate-900">{{ optional($p->kambing)->nama }}</span> —
                                        <span class="font-medium text-blue-800">{{ $p->jenis_perawatan }}</span>
                                    </div>
                                    <form action="{{ route('pengingat-perawatan.selesai', $p) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="tanggal_pemberian" value="{{ date('Y-m-d') }}">
                                        <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-bold text-[11px] shadow-2xs transition">
                                            ✓ Sudah diberikan
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </x-section-card>

    <!-- 6. Kambing Sedang Sakit -->
    <x-section-card title="Kambing Sedang Sakit" icon="ti ti-stethoscope" actionText="Semua Riwayat" actionHref="{{ route('riwayat-sakit.index') }}">
        @if ($kambingSedangSakit->isEmpty())
            <div class="text-center py-4 text-slate-400 text-xs">
                <p>Tidak ada kambing sedang sakit.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($kambingSedangSakit as $sakit)
                    <div class="py-2.5 flex items-center justify-between text-xs">
                        <div>
                            <a href="{{ route('kambings.show', $sakit->kambing) }}" class="font-bold text-slate-900 hover:text-emerald-600">
                                {{ optional($sakit->kambing)->nama ?? 'Kambing Dihapus' }}
                            </a>
                            <span class="text-slate-400 text-[11px] block mt-0.5">
                                Mulai: {{ $sakit->tanggal_mulai ? $sakit->tanggal_mulai->format('d M Y') : '-' }} • Durasi: <strong>{{ $sakit->durasi_hari }} hari</strong>
                            </span>
                        </div>
                        <a href="{{ route('riwayat-sakit.index') }}" class="px-2.5 py-1 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-bold text-[11px] active:scale-95 transition">
                            Konfirmasi
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </x-section-card>

    <!-- 7. Kondisi Kandang & Kesiapan Kawin -->
    <div id="kondisi-kandang">
        <x-section-card title="Kondisi Kandang & Kesiapan" icon="ti ti-building-warehouse" actionText="Kesiapan Kawin" actionHref="{{ route('kesiapan-kawin.index') }}">
            <div class="space-y-2.5 text-xs text-slate-700">
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50/60 border border-emerald-100">
                    <span class="font-medium text-emerald-900">Betina Siap Dikawinkan</span>
                    <a href="{{ route('kesiapan-kawin.index') }}" class="font-bold text-emerald-700 hover:underline">
                        {{ $betinaSiapKawinCount }} ekor &rarr;
                    </a>
                </div>

                <div class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50/60 border border-amber-100">
                    <span class="font-medium text-amber-900">Betina Masa Pemulihan</span>
                    <a href="{{ route('kesiapan-kawin.index') }}" class="font-bold text-amber-700 hover:underline">
                        {{ $betinaMasaPemulihanCount }} ekor &rarr;
                    </a>
                </div>

                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-2 mt-3">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-tight block">Distribusi Ras</span>
                    @if ($statistikRas->isEmpty())
                        <p class="text-xs text-slate-400 py-2 text-center">Belum ada data kambing tersimpan.</p>
                    @else
                        <div class="space-y-2">
                            @foreach ($statistikRas as $ras)
                                @php
                                    $percentage = $totalHidup > 0 ? round(($ras->total / $totalHidup) * 100, 1) : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-xs font-semibold text-slate-700 mb-0.5">
                                        <span>{{ $ras->nama_ras }}</span>
                                        <span>{{ $ras->total }} ekor ({{ $percentage }}%)</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </x-section-card>
    </div>

    <!-- 8. Aktivitas Breeding Terbaru -->
    <x-section-card title="Aktivitas Breeding Terbaru" icon="ti ti-activity" actionText="Semua Riwayat" actionHref="{{ route('perkawinans.index') }}">
        <!-- Perkawinan Terbaru -->
        <div class="mb-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Perkawinan Terbaru</span>
            @if ($perkawinanTerbaru->isEmpty())
                <p class="text-xs text-slate-400 py-2">Belum ada catatan perkawinan.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($perkawinanTerbaru as $p)
                        <div class="py-2 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-900">{{ optional($p->betina)->nama ?? 'Betina Deleted' }} & {{ optional($p->pejantan)->nama ?? 'Pejantan Deleted' }}</span>
                                <span class="text-[11px] text-slate-400 block">Kawin: {{ $p->tanggal_kawin ? \Carbon\Carbon::parse($p->tanggal_kawin)->format('d M Y') : '-' }}</span>
                            </div>
                            <a href="{{ route('perkawinans.show', $p) }}" class="text-xs text-emerald-600 font-bold hover:underline">Detail</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Kelahiran Terbaru -->
        <div class="pt-2 border-t border-slate-100">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Kelahiran Terbaru</span>
            @if ($kelahiranTerbaru->isEmpty())
                <p class="text-xs text-slate-400 py-2">Belum ada catatan kelahiran.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($kelahiranTerbaru as $k)
                        <div class="py-2 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-900">Induk: {{ optional($k->induk)->nama ?? 'Betina Deleted' }}</span>
                                <span class="text-[11px] text-slate-400 block">
                                    Lahir: {{ $k->tanggal_kelahiran ? \Carbon\Carbon::parse($k->tanggal_kelahiran)->format('d M Y') : '-' }} • Jumlah Anak: <strong>{{ $k->anaks->count() }} ekor</strong>
                                </span>
                            </div>
                            <a href="{{ route('kelahirans.show', $k) }}" class="text-xs text-purple-600 font-bold hover:underline">Detail</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </x-section-card>
    </div>
</div>
@endsection
