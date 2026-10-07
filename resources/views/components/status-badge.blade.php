@props([
    'status' => 'default',
    'label' => null,
])

@php
    $normalizedStatus = strtolower(trim((string)$status));

    $config = match($normalizedStatus) {
        'hidup' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-500', 'text' => 'Hidup'],
        'mati' => ['bg' => 'bg-slate-100 text-slate-800 border-slate-300', 'dot' => 'bg-slate-500', 'text' => 'Mati'],
        'dijual' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-200', 'dot' => 'bg-amber-500', 'text' => 'Dijual'],
        'siap' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-500', 'text' => 'Siap Kawin'],
        'pemulihan' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-200', 'dot' => 'bg-amber-500', 'text' => 'Pemulihan'],
        'sedang_sakit' => ['bg' => 'bg-rose-100 text-rose-800 border-rose-200 animate-pulse', 'dot' => 'bg-rose-500', 'text' => 'Sedang Sakit'],
        'sembuh' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-500', 'text' => 'Sembuh'],
        'menunggu' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-200', 'dot' => 'bg-amber-500', 'text' => 'Menunggu'],
        'berhasil' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-500', 'text' => 'Berhasil'],
        'tidak_berhasil' => ['bg' => 'bg-rose-100 text-rose-800 border-rose-200', 'dot' => 'bg-rose-500', 'text' => 'Tidak Berhasil'],
        'belum_diberikan' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-200', 'dot' => 'bg-amber-500', 'text' => 'Belum Diberikan'],
        'sudah_diberikan' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-500', 'text' => 'Sudah Diberikan'],
        default => ['bg' => 'bg-slate-100 text-slate-800 border-slate-200', 'dot' => 'bg-slate-400', 'text' => ucfirst($normalizedStatus)],
    };

    $displayText = $label ?? $config['text'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border shadow-2xs {$config['bg']}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
    <span>{{ $displayText }}</span>
</span>
