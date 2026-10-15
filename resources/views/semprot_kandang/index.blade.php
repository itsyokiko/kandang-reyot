@extends('layouts.app')

@section('title', 'Semprot Kandang')

@section('content')
<div class="space-y-4">
    <!-- Header / Top Bar -->
    <div class="flex items-center justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Semprot Kandang</h1>
            <p class="text-xs text-slate-500">Sanitasi & disinfeksi rutin fasilitas kandang</p>
        </div>
        <div class="flex items-center gap-1.5">
            <button type="button" onclick="openBottomSheet('sheetAturSemprot')"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold active:scale-95 transition shadow-xs">
                <i class="ti ti-plus"></i>
                <span>Atur Jadwal</span>
            </button>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold active:scale-95 transition">
                <i class="ti ti-arrow-left"></i>
                <span>Home</span>
            </a>
        </div>
    </div>

    <!-- Segmented Tab: Perawatan Kambing vs Semprot Kandang -->
    <div class="flex p-1 bg-slate-200/70 rounded-xl gap-1">
        <a href="{{ route('pengingat-perawatan.index') }}" class="flex-1 py-1.5 px-3 rounded-lg text-center text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
            Perawatan Kambing
        </a>
        <a href="{{ route('semprot-kandang.index') }}" class="flex-1 py-1.5 px-3 rounded-lg text-center text-xs font-bold bg-white text-emerald-800 shadow-2xs">
            Semprot Kandang
        </a>
    </div>

    <!-- 1. Jadwal Semprot Aktif -->
    <x-section-card title="Jadwal Semprot Berikutnya" icon="ti ti-calendar-event">
        @if ($jadwalAktif)
            @php
                $isOverdue = $jadwalAktif->tanggal && $jadwalAktif->tanggal->isPast() && !$jadwalAktif->tanggal->isToday();
                $isToday = $jadwalAktif->tanggal && $jadwalAktif->tanggal->isToday();
            @endphp
            <div class="p-4 rounded-2xl border {{ $isOverdue ? 'bg-rose-50/70 border-rose-200' : ($isToday ? 'bg-amber-50/70 border-amber-200' : 'bg-emerald-50/70 border-emerald-200') }} space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl {{ $isOverdue ? 'bg-rose-100 text-rose-700' : ($isToday ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }} flex items-center justify-center text-xl">
                            <i class="ti ti-droplet"></i>
                        </div>
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-wide {{ $isOverdue ? 'text-rose-800' : ($isToday ? 'text-amber-800' : 'text-emerald-800') }}">
                                {{ $isOverdue ? 'Jadwal Terlewat' : ($isToday ? 'Jadwal Hari Ini' : 'Terjadwal') }}
                            </span>
                            <h3 class="text-base font-black text-slate-900">
                                {{ $jadwalAktif->tanggal ? $jadwalAktif->tanggal->translatedFormat('l, d F Y') : '-' }}
                            </h3>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isOverdue ? 'bg-rose-200 text-rose-900' : ($isToday ? 'bg-amber-200 text-amber-900' : 'bg-emerald-200 text-emerald-900') }}">
                        {{ $jadwalAktif->tanggal ? $jadwalAktif->tanggal->diffForHumans() : 'Belum selesai' }}
                    </span>
                </div>

                @if ($jadwalAktif->catatan)
                    <div class="p-2.5 rounded-xl bg-white/80 border border-slate-200/60 text-xs text-slate-700">
                        {{ $jadwalAktif->catatan }}
                    </div>
                @endif

                <!-- Form Konfirmasi Selesai Disemprot -->
                <form action="{{ route('pengingat-perawatan.selesai', $jadwalAktif) }}" method="POST" class="pt-2 border-t border-slate-200/60 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                    @csrf
                    <div class="flex items-center gap-2">
                        <label for="tanggal_pemberian" class="text-[11px] font-bold text-slate-600 shrink-0">Tgl Disemprot:</label>
                        <input type="date" name="tanggal_pemberian" id="tanggal_pemberian" value="{{ date('Y-m-d') }}"
                               class="px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-xs transition">
                        <i class="ti ti-check"></i>
                        <span>Konfirmasi Sudah Disemprot</span>
                    </button>
                </form>
            </div>
            <p class="text-[11px] text-slate-500 mt-2 px-1">
                <i class="ti ti-info-circle text-emerald-600"></i> Setelah dikonfirmasi, sistem otomatis membuat jadwal semprot berikutnya sesuai interval ({{ $intervalDefault }} hari).
            </p>
        @else
            <div class="p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-center space-y-2">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-lg">
                    <i class="ti ti-droplet"></i>
                </div>
                <h4 class="text-xs font-bold text-slate-700">Belum Ada Jadwal Semprot Aktif</h4>
                <p class="text-[11px] text-slate-500 max-w-xs mx-auto">
                    Atur jadwal pertama untuk memulai disinfeksi rutin fasilitas kandang.
                </p>
                <div class="pt-1">
                    <button type="button" onclick="openBottomSheet('sheetAturSemprot')"
                            class="px-3.5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-xs active:scale-95 transition inline-flex items-center gap-1.5">
                        <i class="ti ti-plus"></i>
                        <span>Atur Jadwal Pertama</span>
                    </button>
                </div>
            </div>
        @endif
    </x-section-card>

    <!-- 2. Riwayat Penyemprotan Kandang -->
    <x-section-card title="Riwayat Penyemprotan Selesai" icon="ti ti-history">
        @if ($riwayatSemprot->isEmpty())
            <div class="text-center py-6 text-slate-400">
                <i class="ti ti-clipboard text-3xl mb-1 block"></i>
                <p class="text-xs">Belum ada riwayat penyemprotan yang diselesaikan.</p>
            </div>
        @else
            <div class="space-y-2.5">
                @foreach ($riwayatSemprot as $r)
                    <div class="p-3 bg-white border border-slate-100 rounded-xl shadow-2xs flex items-start justify-between gap-3">
                        <div class="flex items-start gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm shrink-0 mt-0.5">
                                <i class="ti ti-check"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">
                                    Disemprot: {{ $r->tanggal_pemberian ? $r->tanggal_pemberian->translatedFormat('d M Y') : ($r->tanggal ? $r->tanggal->translatedFormat('d M Y') : '-') }}
                                </span>
                                @if ($r->catatan)
                                    <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $r->catatan }}</p>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0">
                            Selesai
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-section-card>
</div>

<!-- Bottom Sheet Atur Jadwal Semprot -->
<x-bottom-sheet id="sheetAturSemprot" title="Atur Jadwal Semprot Kandang">
    <form action="{{ route('semprot-kandang.store') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Tanggal Semprot -->
        <div>
            <label for="sheet_tanggal_semprot" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Tanggal Jadwal Semprot <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal" id="sheet_tanggal_semprot" value="{{ old('tanggal', date('Y-m-d')) }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
            @error('tanggal')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Interval Hari -->
        <div>
            <label for="sheet_interval_hari" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Interval Rutin (Hari)
            </label>
            <div class="flex items-center gap-2">
                <input type="number" name="interval_hari" id="sheet_interval_hari" min="1" max="365"
                       value="{{ old('interval_hari', $intervalDefault) }}"
                       class="w-28 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition">
                <span class="text-xs font-semibold text-slate-500">Hari sekali (Default: {{ $intervalDefault }} hari)</span>
            </div>
            @error('interval_hari')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Catatan Fasilitas -->
        <div>
            <label for="sheet_catatan_semprot" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Catatan Fasilitas (Opsional)
            </label>
            <textarea name="catatan" id="sheet_catatan_semprot" rows="2" placeholder="Contoh: Disinfeksi seluruh lantai, sekat bambu & tempat pakan..."
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition">{{ old('catatan') }}</textarea>
            @error('catatan')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetAturSemprot')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Simpan Jadwal Semprot
            </button>
        </div>
    </form>
</x-bottom-sheet>

@if(request('action') === 'create' || $errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetAturSemprot');
    });
</script>
@endif
@endsection
