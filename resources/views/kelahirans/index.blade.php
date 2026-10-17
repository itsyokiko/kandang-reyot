@extends('layouts.app')

@section('title', 'Catatan Kelahiran')

@section('content')
<div class="space-y-4">
    <!-- Top Action Header -->
    <div class="flex items-center justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Catatan Kelahiran</h1>
            <p class="text-xs text-slate-500">Peristiwa kelahiran & anak yang lahir</p>
        </div>
        <button type="button" onclick="openBottomSheet('sheetTambahKelahiran')"
                class="inline-flex items-center justify-center gap-1.5 font-bold transition active:scale-95 shadow-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs">
            <i class="ti ti-plus"></i>
            <span>Catat</span>
        </button>
    </div>

    @if ($kelahirans->isEmpty())
        <div class="p-6 text-center bg-white rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                <i class="ti ti-mood-kid"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-800">Belum ada catatan kelahiran</h3>
            <p class="text-xs text-slate-500 max-w-xs mx-auto">Catat peristiwa kelahiran ketika induk kambing melahirkan anak.</p>
            <button type="button" onclick="openBottomSheet('sheetTambahKelahiran')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95">
                + Catat Kelahiran Pertama
            </button>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($kelahirans as $kelahiran)
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs hover:border-emerald-300 transition-all flex flex-col gap-3">
                    <!-- Header -->
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-1.5 text-xs text-slate-900 font-bold">
                            <i class="ti ti-calendar text-emerald-600"></i>
                            <span>{{ $kelahiran->tanggal_kelahiran->format('d M Y') }}</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            {{ $kelahiran->anaks->count() }} Ekor Anak
                        </span>
                    </div>

                    <!-- Pasangan Induk & Pejantan -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-2 bg-pink-50/60 border border-pink-100 rounded-xl text-xs">
                            <span class="text-[10px] font-extrabold text-pink-600 uppercase tracking-wider block">Induk Betina</span>
                            <a href="{{ route('kambings.show', $kelahiran->induk) }}" class="font-bold text-slate-900 hover:text-pink-600 truncate block mt-0.5">
                                ♀ {{ $kelahiran->induk->nama }}
                            </a>
                        </div>

                        <div class="p-2 bg-blue-50/60 border border-blue-100 rounded-xl text-xs">
                            <span class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider block">Pejantan</span>
                            <a href="{{ route('kambings.show', $kelahiran->pejantan) }}" class="font-bold text-slate-900 hover:text-blue-600 truncate block mt-0.5">
                                ♂ {{ $kelahiran->pejantan->nama }}
                            </a>
                        </div>
                    </div>

                    <!-- Daftar Anak -->
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Anak yang Lahir:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($kelahiran->anaks as $anak)
                                <a href="{{ route('kambings.show', $anak) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-semibold {{ $anak->jenis_kelamin === 'jantan' ? 'bg-blue-50 text-blue-800 border border-blue-200' : 'bg-pink-50 text-pink-800 border border-pink-200' }} hover:opacity-80 transition">
                                    <span>{{ $anak->jenis_kelamin === 'jantan' ? '♂' : '♀' }}</span>
                                    <span>{{ $anak->nama }}</span>
                                </a>
                            @empty
                                <span class="text-xs text-slate-400 italic">Tidak ada data anak</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs font-semibold">
                        <div>
                            @if ($kelahiran->perkawinan)
                                <a href="{{ route('perkawinans.show', $kelahiran->perkawinan) }}" class="text-slate-500 hover:text-emerald-600 text-[11px] flex items-center gap-1">
                                    <i class="ti ti-link"></i>
                                    <span>Dari Kawin {{ $kelahiran->perkawinan->tanggal_kawin->format('d/m/Y') }}</span>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('kelahirans.show', $kelahiran) }}" class="text-emerald-600 hover:text-emerald-700 p-1">
                                Detail
                            </a>
                            <a href="{{ route('kelahirans.edit', $kelahiran) }}" class="text-amber-600 hover:text-amber-700 p-1">
                                Edit
                            </a>
                            <form action="{{ route('kelahirans.destroy', $kelahiran) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, this, 'Hapus Catatan Kelahiran?', 'Apakah Anda yakin ingin menghapus catatan kelahiran ini? (Data anak tidak akan dihapus, hanya tautan kelahirannya yang dilepas)');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-700 p-1">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Bottom Sheet Catat Kelahiran -->
<x-bottom-sheet id="sheetTambahKelahiran" title="Catat Kelahiran Baru">
    <form action="{{ route('kelahirans.store') }}" method="POST" id="form-kelahiran-sheet" class="space-y-4">
        @csrf
        @include('kelahirans._form_kelahiran', [
            'prefix' => 'sheet_',
            'isModal' => true,
            'perkawinans' => $perkawinans,
            'betinas' => $betinas,
            'pejantans' => $pejantans,
            'allKambings' => $allKambings,
        ])
    </form>
</x-bottom-sheet>

@if(request('action') === 'create' || ($errors->any() && !old('_method')))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetTambahKelahiran');
    });
</script>
@endif
@endsection
