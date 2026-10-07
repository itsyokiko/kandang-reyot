@extends('layouts.app')

@section('title', 'Kesiapan Betina untuk Dikawinkan')

@section('content')
<div class="space-y-4">
    <!-- Top Action Header -->
    <div class="flex items-center justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Kesiapan Kawin Betina</h1>
            <p class="text-xs text-slate-500">Estimasi kesiapan pasca melahirkan (Pemulihan: {{ $intervalHari }} hari)</p>
        </div>
        <x-action-button href="{{ route('perkawinans.create') }}" icon="ti ti-plus" size="sm">
            Catat
        </x-action-button>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-3 gap-2">
        <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-3 text-center shadow-2xs">
            <span class="text-[10px] font-extrabold text-emerald-700 uppercase tracking-wider block truncate">Siap</span>
            <span class="text-lg font-black text-emerald-900 mt-0.5 block">{{ $siapDikawinkan->count() }}</span>
        </div>
        <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-3 text-center shadow-2xs">
            <span class="text-[10px] font-extrabold text-amber-700 uppercase tracking-wider block truncate">Pemulihan</span>
            <span class="text-lg font-black text-amber-900 mt-0.5 block">{{ $masaPemulihan->count() }}</span>
        </div>
        <div class="bg-slate-100 border border-slate-200/80 rounded-2xl p-3 text-center shadow-2xs">
            <span class="text-[10px] font-extrabold text-slate-600 uppercase tracking-wider block truncate">Belum</span>
            <span class="text-lg font-black text-slate-800 mt-0.5 block">{{ $belumAdaRiwayat->count() }}</span>
        </div>
    </div>

    <!-- Kategori 1: SIAP DIKAWINKAN -->
    <x-section-card title="1. Siap Dikawinkan ({{ $siapDikawinkan->count() }})" icon="ti ti-heart" badge="Siap">
        @if ($siapDikawinkan->isEmpty())
            <p class="text-xs text-slate-500 italic py-1">Tidak ada kambing betina yang siap dikawinkan saat ini.</p>
        @else
            <div class="space-y-2.5">
                @foreach ($siapDikawinkan as $item)
                    <div class="p-3 border border-emerald-200 bg-emerald-50/40 rounded-xl flex items-center justify-between gap-2">
                        <div class="overflow-hidden">
                            <a href="{{ route('kambings.show', $item['kambing']) }}" class="font-bold text-slate-900 hover:text-emerald-600 text-xs block truncate">
                                ♀ {{ $item['kambing']->nama }}
                            </a>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Lahir: {{ $item['tanggal_kelahiran_terakhir']->format('d M Y') }}
                                <span class="text-emerald-700 font-semibold">• {{ $item['lama_sejak_melahirkan'] }} hari lalu</span>
                            </div>
                        </div>

                        <a href="{{ route('perkawinans.create', ['betina_id' => $item['kambing']->id]) }}"
                           class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs whitespace-nowrap shadow-xs active:scale-95 transition">
                            + Kawinkan
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </x-section-card>

    <!-- Kategori 2: MASA PEMULIHAN -->
    <x-section-card title="2. Masa Pemulihan ({{ $masaPemulihan->count() }})" icon="ti ti-clock" badge="Pemulihan">
        @if ($masaPemulihan->isEmpty())
            <p class="text-xs text-slate-500 italic py-1">Tidak ada kambing betina dalam masa pemulihan.</p>
        @else
            <div class="space-y-2.5">
                @foreach ($masaPemulihan as $item)
                    <div class="p-3 border border-amber-200 bg-amber-50/40 rounded-xl flex items-center justify-between gap-2">
                        <div class="overflow-hidden">
                            <a href="{{ route('kambings.show', $item['kambing']) }}" class="font-bold text-slate-900 hover:text-amber-700 text-xs block truncate">
                                ♀ {{ $item['kambing']->nama }}
                            </a>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Lahir: {{ $item['tanggal_kelahiran_terakhir']->format('d M Y') }} • Estimasi Siap: {{ $item['estimasi_tanggal_siap']->format('d M Y') }}
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold whitespace-nowrap">
                            Sisa {{ $item['sisa_hari'] }} hr
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-section-card>

    <!-- Kategori 3: BELUM ADA RIWAYAT KELAHIRAN -->
    <x-section-card title="3. Belum Ada Riwayat Kelahiran ({{ $belumAdaRiwayat->count() }})" icon="ti ti-history">
        @if ($belumAdaRiwayat->isEmpty())
            <p class="text-xs text-slate-500 italic py-1">Semua kambing betina sudah pernah melahirkan.</p>
        @else
            <div class="space-y-2.5">
                @foreach ($belumAdaRiwayat as $item)
                    <div class="p-3 border border-slate-200 bg-slate-50/50 rounded-xl flex items-center justify-between gap-2">
                        <div class="overflow-hidden">
                            <a href="{{ route('kambings.show', $item['kambing']) }}" class="font-bold text-slate-900 hover:text-emerald-600 text-xs block truncate">
                                ♀ {{ $item['kambing']->nama }}
                            </a>
                            <div class="text-[11px] text-slate-400 mt-0.5 truncate">
                                Ras: {{ $item['kambing']->ras ?? '-' }} • {{ $item['keterangan'] }}
                            </div>
                        </div>

                        <a href="{{ route('perkawinans.create', ['betina_id' => $item['kambing']->id]) }}"
                           class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs whitespace-nowrap shadow-xs active:scale-95 transition">
                            + Kawinkan
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </x-section-card>
</div>
@endsection
