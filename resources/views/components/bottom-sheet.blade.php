@props([
    'id',
    'title' => null,
    'type' => null,
])

{{--
    Bottom Sheet Component — pure inline CSS, zero Tailwind-CDN dependency for critical layout.

    Height behaviour:
    - Content kecil  → tinggi = auto (sheet pas dengan konten)
    - Content besar  → max 80% layar, konten scrollable
    - Keyboard muncul → --vh var di-update JS agar sheet mengecil ikut viewport
--}}

{{-- Overlay --}}
<div id="{{ $id }}Overlay"
     onclick="KR.sheet.close('{{ $id }}')"
     aria-hidden="true"
     style="position:fixed;inset:0;z-index:200;
            background:rgba(15,23,42,.55);
            opacity:0;visibility:hidden;
            transition:opacity .28s ease,visibility .28s ease;
            pointer-events:none;"></div>

{{-- Sheet --}}
<div id="{{ $id }}"
     role="dialog"
     aria-modal="true"
     aria-label="{{ $title ?? 'Panel' }}"
     style="position:fixed;bottom:0;left:0;right:0;z-index:201;
            max-width:32rem;margin:0 auto;
            background:#fff;
            border-radius:1.5rem 1.5rem 0 0;
            box-shadow:0 -4px 30px rgba(0,0,0,.15);
            display:flex;flex-direction:column;
            /* 80vh fallback, --vh var di-update JS saat keyboard muncul */
            max-height:80vh;max-height:calc(var(--vh,1vh)*80);
            overflow:hidden;
            transform:translateY(100%);visibility:hidden;
            transition:transform .3s cubic-bezier(.32,.72,0,1),visibility .3s ease;
            will-change:transform;">

    {{-- Drag handle --}}
    <div onclick="KR.sheet.close('{{ $id }}')"
         style="padding:12px 0 6px;display:flex;justify-content:center;flex-shrink:0;cursor:pointer;">
        <div style="width:48px;height:6px;border-radius:9999px;background:#e2e8f0;"></div>
    </div>

    {{-- Title bar (optional) --}}
    @if($title)
        <div style="padding:10px 20px 10px;border-bottom:1px solid #f1f5f9;
                    display:flex;align-items:center;justify-content:space-between;
                    flex-shrink:0;">
            <h3 style="font-size:.9rem;font-weight:700;color:#0f172a;letter-spacing:-.01em;margin:0;">
                {{ $title }}
            </h3>
            <button type="button"
                    onclick="KR.sheet.close('{{ $id }}')"
                    aria-label="Tutup"
                    style="width:32px;height:32px;border-radius:9999px;border:none;
                           background:transparent;cursor:pointer;
                           display:flex;align-items:center;justify-content:center;
                           color:#94a3b8;font-size:1.1rem;">
                <i class="ti ti-x"></i>
            </button>
        </div>
    @endif

    {{--
        Scrollable body.
        flex:1 + min-height:0 adalah kombinasi WAJIB agar overflow-y bekerja
        di dalam flex column (terutama Safari/WebKit).

        Tanpa min-height:0, flex child default ke min-height:auto → tidak bisa scroll.
    --}}
    <div data-sheet-body
         style="flex:1;min-height:0;overflow-y:auto;
                padding:20px 20px;
                padding-bottom:max(24px,env(safe-area-inset-bottom,0px));
                -webkit-overflow-scrolling:touch;
                overscroll-behavior:contain;">
        {{ $slot }}
    </div>
</div>
