@props([
    'name' => 'foto',
    'id' => 'foto',
    'currentPhoto' => null,
    'label' => 'Foto Kambing',
    'helpText' => 'Pilih dari kamera ponsel atau jelajahi file galeri.'
])

@php
    $inputId = $id;
    $cameraInputId = $id . '_camera';
    $previewContainerId = 'preview_box_' . $id;
    $previewImgId = 'preview_img_' . $id;
    $infoId = 'preview_info_' . $id;
@endphp

<div class="space-y-2 photo-upload-wrapper" data-upload-id="{{ $inputId }}">
    @if($label)
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
            {{ $label }}
        </label>
    @endif

    <!-- Foto Saat Ini (Jika Mode Edit) -->
    @if ($currentPhoto)
        <div id="existing_box_{{ $inputId }}" class="flex items-center gap-3 p-2.5 rounded-2xl bg-slate-50 border border-slate-200">
            <img src="{{ asset('storage/' . $currentPhoto) }}" alt="Foto saat ini" class="w-12 h-12 object-cover rounded-xl border border-slate-300 shrink-0 shadow-2xs">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold text-slate-700 block">Foto Saat Ini</span>
                <span class="text-[11px] text-slate-400 block truncate">Pilih opsi di bawah untuk mengganti foto</span>
            </div>
        </div>
    @endif

    <!-- Pratinjau Foto Baru (Muncul saat foto dipilih / diambil) -->
    <div id="{{ $previewContainerId }}" class="hidden">
        <div class="flex items-center gap-3 p-2.5 rounded-2xl bg-emerald-50/80 border border-emerald-200">
            <div class="relative w-14 h-14 rounded-xl overflow-hidden border border-emerald-300 bg-white shrink-0 shadow-2xs">
                <img id="{{ $previewImgId }}" src="" alt="Pratinjau Foto" class="w-full h-full object-cover">
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold text-emerald-800 block">Foto Siap Unggah</span>
                <span id="{{ $infoId }}" class="text-[11px] text-emerald-600 font-medium block">Memproses...</span>
            </div>
            <button type="button"
                    onclick="clearPhotoUpload('{{ $inputId }}', '{{ $cameraInputId }}', '{{ $previewContainerId }}', '{{ $previewImgId }}')"
                    class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 active:scale-95 transition shrink-0"
                    title="Hapus foto">
                <i class="ti ti-trash text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Hidden Native Inputs: Galeri & Kamera Langsung -->
    <input type="file"
           name="{{ $name }}"
           id="{{ $inputId }}"
           accept="image/*"
           class="hidden"
           data-preview="#{{ $previewImgId }}"
           data-preview-container="#{{ $previewContainerId }}"
           data-info-target="#{{ $infoId }}">

    <input type="file"
           id="{{ $cameraInputId }}"
           accept="image/*"
           capture="environment"
           class="hidden"
           onchange="handleCameraCapture(this, '{{ $inputId }}')">

    <!-- Pilihan Ambil Kamera / Jelajahi Galeri -->
    <div class="grid grid-cols-2 gap-2">
        <!-- 1. Tombol Kamera Ponsel -->
        <button type="button"
                onclick="document.getElementById('{{ $cameraInputId }}').click()"
                class="flex items-center justify-center gap-2 px-3.5 py-3 rounded-2xl bg-emerald-50 hover:bg-emerald-100 active:bg-emerald-200 border border-emerald-200 text-emerald-800 font-bold text-xs shadow-2xs active:scale-97 transition cursor-pointer">
            <i class="ti ti-camera text-base text-emerald-600"></i>
            <span>Ambil Kamera</span>
        </button>

        <!-- 2. Tombol Browse / Galeri File -->
        <button type="button"
                onclick="document.getElementById('{{ $inputId }}').click()"
                class="flex items-center justify-center gap-2 px-3.5 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 active:bg-slate-200 border border-slate-200 text-slate-700 font-bold text-xs shadow-2xs active:scale-97 transition cursor-pointer">
            <i class="ti ti-photo text-base text-slate-500"></i>
            <span>Pilih Galeri</span>
        </button>
    </div>

    @if($helpText)
        <p class="text-[11px] text-slate-400 mt-1">{{ $helpText }}</p>
    @endif

    @error($name)
        <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>
    @enderror
</div>
