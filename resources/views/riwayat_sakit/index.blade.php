@extends('layouts.app')

@section('title', 'Daftar Riwayat Sakit')

@section('content')
<div class="space-y-4">
    <!-- Top Action Header -->
    <div class="flex items-center justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Riwayat Sakit</h1>
            <p class="text-xs text-slate-500">Pencatatan & pemantauan kambing sakit</p>
        </div>
        <button type="button" onclick="openBottomSheet('sheetTambahSakit')"
                class="inline-flex items-center justify-center gap-1.5 font-bold transition active:scale-95 shadow-xs bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-xl text-xs">
            <i class="ti ti-plus"></i>
            <span>Catat Sakit</span>
        </button>
    </div>

    @if ($riwayatSakits->isEmpty())
        <div class="p-6 text-center bg-white rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                <i class="ti ti-stethoscope"></i>
            </div>
            <h3 class="text-sm font-extrabold text-slate-800">Belum ada riwayat sakit</h3>
            <p class="text-xs text-slate-500 max-w-xs mx-auto">Seluruh kambing dalam kondisi sehat atau belum pernah dicatat sakit.</p>
            <button type="button" onclick="openBottomSheet('sheetTambahSakit')"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95">
                + Catat Kambing Sakit
            </button>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($riwayatSakits as $r)
                <div class="bg-white border {{ $r->status === 'sedang_sakit' ? 'border-rose-300 shadow-rose-100' : 'border-slate-200/90' }} rounded-2xl p-4 shadow-xs transition-all flex flex-col gap-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900">
                                @if ($r->kambing)
                                    <a href="{{ route('kambings.show', $r->kambing) }}" class="hover:text-emerald-600 transition">
                                        🐐 {{ $r->kambing->nama }}
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">Kambing Dihapus</span>
                                @endif
                            </span>
                        </div>
                        <x-status-badge :status="$r->status" />
                    </div>

                    <div class="text-xs text-slate-600 space-y-1">
                        <div class="flex items-center gap-2 text-slate-500">
                            <span>Mulai Sakit: <strong>{{ $r->tanggal_mulai ? $r->tanggal_mulai->format('d M Y') : '-' }}</strong></span>
                            <span>• Durasi: <strong>{{ $r->durasi_hari }} hari</strong></span>
                        </div>

                        @if ($r->tanggal_selesai)
                            <div class="text-slate-500">
                                Tanggal Selesai: {{ $r->tanggal_selesai->format('d M Y') }}
                            </div>
                        @endif

                        @if ($r->catatan)
                            <p class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 mt-1 whitespace-pre-line text-slate-700">
                                {{ $r->catatan }}
                            </p>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 text-xs font-semibold">
                        @if ($r->status === 'sedang_sakit')
                            <button type="button"
                                    onclick="openKonfirmasiSelesai('{{ $r->id }}', '{{ addslashes($r->kambing->nama) }}', '{{ $r->tanggal_mulai->format('Y-m-d') }}')"
                                    class="text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-xl border border-emerald-200 select-none active:scale-95 transition">
                                Selesaikan Riwayat
                            </button>
                        @endif

                        <form action="{{ route('riwayat-sakit.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, this, 'Hapus Catatan Sakit?', 'Apakah Anda yakin ingin menghapus catatan sakit ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-700 p-1">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($riwayatSakits->hasPages())
            <div class="pt-2">
                {{ $riwayatSakits->links() }}
            </div>
        @endif
    @endif
</div>

<!-- Bottom Sheet Catat Kambing Sakit -->
<x-bottom-sheet id="sheetTambahSakit" title="Catat Kambing Sakit">
    <form action="{{ route('riwayat-sakit.store') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Pilih Kambing -->
        <div>
            <label for="sheet_kambing_sakit_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Pilih Kambing <span class="text-rose-500">*</span>
            </label>
            <select name="kambing_id" id="sheet_kambing_sakit_id" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition">
                <option value="">-- Pilih Kambing Sakit --</option>
                @foreach ($kambings as $k)
                    <option value="{{ $k->id }}" {{ old('kambing_id') == $k->id ? 'selected' : '' }}>
                        🐐 {{ $k->nama }} ({{ $k->jenis_kelamin === 'jantan' ? 'Jantan' : 'Betina' }}{{ $k->ras ? ' - ' . $k->ras : '' }})
                    </option>
                @endforeach
            </select>
            @error('kambing_id')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanggal Mulai Sakit -->
        <div>
            <label for="sheet_tanggal_mulai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Tanggal Mulai Sakit <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal_mulai" id="sheet_tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition">
            @error('tanggal_mulai')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Catatan Gejala -->
        <div>
            <label for="sheet_catatan_sakit" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Catatan / Gejala Sakit
            </label>
            <textarea name="catatan" id="sheet_catatan_sakit" rows="3"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition"
                placeholder="Contoh: Nafsu makan turun, mata berair, lemas...">{{ old('catatan') }}</textarea>
            @error('catatan')
                <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetTambahSakit')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Simpan Catatan Sakit
            </button>
        </div>
    </form>
</x-bottom-sheet>

<!-- Bottom Sheet Konfirmasi Hasil Sakit -->
<x-bottom-sheet id="sheetKonfirmasiSelesai" title="Konfirmasi Selesai Sakit">
    <form id="formKonfirmasiSelesai" action="" method="POST" class="space-y-4">
        @csrf

        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl">
            <span class="text-xs text-slate-500 font-medium block">Kambing Sakit:</span>
            <strong id="konfirmasiKambingNama" class="text-sm font-bold text-slate-800"></strong>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Hasil Perawatan <span class="text-rose-500">*</span>
            </label>
            <select name="status" id="konfirmasiStatus" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="sembuh">🟢 Sembuh</option>
                <option value="mati">🔴 Mati</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Tanggal Selesai / Mati <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal_selesai" id="konfirmasiTanggalSelesai" value="{{ date('Y-m-d') }}" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeBottomSheet('sheetKonfirmasiSelesai')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition">
                Simpan Hasil
            </button>
        </div>
    </form>
</x-bottom-sheet>

<script>
    function openKonfirmasiSelesai(id, namaKambing, tglMulai) {
        const form = document.getElementById('formKonfirmasiSelesai');
        form.action = `/riwayat-sakit/${id}/konfirmasi`;
        document.getElementById('konfirmasiKambingNama').textContent = namaKambing;
        const tglInput = document.getElementById('konfirmasiTanggalSelesai');
        if (tglMulai) {
            tglInput.min = tglMulai;
        }
        openBottomSheet('sheetKonfirmasiSelesai');
    }
</script>

@if(request('action') === 'create' || ($errors->any() && !old('status_hasil')))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openBottomSheet('sheetTambahSakit');
    });
</script>
@endif
@endsection
