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
                @include('kelahirans._form_kelahiran', [
                    'prefix' => 'create_',
                    'isModal' => false,
                    'perkawinans' => $perkawinans,
                    'betinas' => $betinas,
                    'pejantans' => $pejantans,
                    'allKambings' => $allKambings,
                    'selectedPerkawinanId' => $selectedPerkawinanId ?? null,
                    'selectedIndukId' => $selectedIndukId ?? null,
                    'selectedPejantanId' => $selectedPejantanId ?? null,
                ])
            </form>
        </x-section-card>
    @endif
</div>
@endsection
