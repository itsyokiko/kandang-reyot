<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 sm:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Kandang Reyot</title>

    <!-- [PWA Meta Tags] -->
    <meta name="theme-color" content="#047857">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Kandang Reyot">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- [Favicon] -->
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192x192.png') }}">

    <!-- [Instant Navigation Speculation Rules] -->
    <script type="speculationrules">
    {
      "prefetch": [
        {
          "source": "list",
          "urls": [
            "{{ route('dashboard') }}",
            "{{ route('kambings.index') }}",
            "{{ route('pengingat-perawatan.index') }}",
            "{{ route('silsilah.index') }}",
            "{{ route('perkawinans.index') }}",
            "{{ route('kelahirans.index') }}",
            "{{ route('kesiapan-kawin.index') }}",
            "{{ route('riwayat-sakit.index') }}",
            "{{ route('semprot-kandang.index') }}",
            "{{ route('relasi.index') }}"
          ],
          "eagerness": "moderate"
        }
      ]
    }
    </script>

    <!-- [Google Font] Public Sans -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap">

    <!-- [Icons] Tabler & Feather -->
    <link rel="stylesheet" href="{{ asset('assets/dist/assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/dist/assets/fonts/feather.css') }}">

    <!-- [SweetAlert2] -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- [Tailwind CSS CDN] -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Public Sans"', 'system-ui', 'sans-serif'],
                    },
                    boxShadow: {
                        'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                        '2xs': '0 1px 1px 0 rgba(0, 0, 0, 0.03)',
                    },
                    scale: {
                        '98': '0.98',
                        '97': '0.97',
                    }
                }
            }
        }
    </script>

    <style>
        /* Native App Feel & View Transitions */
        @view-transition {
            navigation: auto;
        }

        ::view-transition-old(root) {
            animation: 90ms cubic-bezier(0.4, 0, 1, 1) both fade-out;
        }
        ::view-transition-new(root) {
            animation: 160ms cubic-bezier(0, 0, 0.2, 1) both fade-in;
        }

        @keyframes fade-out {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(3px); }
            to { opacity: 1; transform: translateY(0); }
        }

        html, body {
            font-family: 'Public Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
            overscroll-behavior-y: none;
        }

        /* Safe area insets for iOS notch & home bar */
        .safe-top {
            padding-top: env(safe-area-inset-top, 0px);
        }
        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }

        /* Subtle page entrance without full reload flash (opacity only, no transform to preserve fixed viewport coordinates) */
        .app-screen-content {
            animation: appScreenEnter 0.16s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes appScreenEnter {
            from {
                opacity: 0.85;
            }
            to {
                opacity: 1;
            }
        }

        /* Smooth scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3);
            border-radius: 9999px;
        }

        /* Utility to hide scrollbar */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Compact SweetAlert2 for mobile */
        .swal2-container.swal2-center > .swal2-popup.swal-compact,
        .swal-compact {
            max-width: 300px !important;
            font-size: 13px !important;
            padding: 1.2rem !important;
        }
        .swal-compact .swal2-title {
            font-size: 1rem !important;
        }
        .swal-compact .swal2-html-container,
        .swal-compact .swal2-content {
            font-size: 0.78rem !important;
        }
        .swal-compact .swal2-icon {
            width: 3.5rem !important;
            height: 3.5rem !important;
            margin: 0.5rem auto 0.75rem !important;
        }
        .swal-compact .swal2-icon .swal2-icon-content {
            font-size: 2rem !important;
        }

        /*
         * Bottom sheet visual viewport fix.
         * When the soft keyboard opens, window.innerHeight shrinks but dvh units
         * may not update on older Android. We set --vh via JS below and use it.
         */
        .bottom-sheet-host {
            max-height: calc(var(--vh, 1dvh) * 100);
        }
    </style>

</head>
<body class="h-full bg-slate-50 sm:bg-slate-950 antialiased select-none text-slate-800">

    <!-- Mobile Device Container Wrapper (Centered on Desktop with native card frame) -->
    <div class="w-full max-w-lg mx-auto min-h-screen min-h-[100dvh] bg-slate-50 relative flex flex-col shadow-2xl border-x border-slate-200/60 sm:border-slate-800/40 overflow-x-clip">

        <!-- Top App Header (Hidden on Login) -->
        @unless(request()->routeIs('login'))
            <x-app-header :title="View::hasSection('title') ? trim(View::getSection('title')) : 'Kandang Reyot'" />
        @endunless

        <!-- Notification Banner / Flash Messages -->
        @if (session('success'))
            <div class="px-3.5 pt-3">
                <div class="p-3.5 rounded-2xl bg-emerald-600 text-white text-xs font-semibold shadow-sm flex items-center justify-between gap-2 animate-fade-in">
                    <div class="flex items-center gap-2">
                        <i class="ti ti-circle-check text-lg"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="opacity-80 hover:opacity-100 p-1">
                        <i class="ti ti-x text-sm"></i>
                    </button>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="px-3.5 pt-3">
                <div class="p-3.5 rounded-2xl bg-rose-600 text-white text-xs font-medium shadow-sm">
                    <div class="font-bold flex items-center gap-2 mb-1">
                        <i class="ti ti-alert-triangle text-base"></i>
                        <span>Periksa input form:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 opacity-95">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Main Content Area with Seamless Transition & Safe Spacing -->
        <main class="flex-1 w-full {{ request()->routeIs('login') ? 'px-6 py-12 flex flex-col justify-center' : (request()->routeIs('dashboard') ? 'px-0 pt-0 pb-24' : 'px-3.5 py-4 pb-24') }} app-screen-content">
            @yield('content')
        </main>

        <!-- Global Datalist for Ras / Breed Suggestions -->
        <datalist id="list-ras">
            @foreach(\App\Models\Kambing::getAvailableRas() as $rasItem)
                <option value="{{ $rasItem }}"></option>
            @endforeach
        </datalist>

        <!-- Slide-over Drawer Menu (Secondary & Settings only) -->
        <x-drawer />

        <!-- Fixed Bottom Navigation Bar (Primary screens only) -->
        @unless(request()->routeIs('login'))
            <x-bottom-navigation />
        @endunless

    </div>

    <!-- [Global Centered Loading Spinner] -->
    <div id="globalAppLoader"
         class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-950/70 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200"
         aria-hidden="true">
        <div id="globalAppLoaderCard" class="bg-white/95 rounded-3xl p-6 shadow-2xl flex flex-col items-center gap-3 border border-slate-100 max-w-[220px] w-full text-center transform scale-95 transition-transform duration-200">
            <div class="relative w-14 h-14 flex items-center justify-center">
                <div class="absolute inset-0 rounded-full border-4 border-emerald-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-emerald-600 border-t-transparent animate-spin"></div>
                <img src="{{ asset('images/goat_logo.png') }}" alt="Logo" class="w-7 h-7 object-contain relative z-10 select-none">
            </div>
            <div class="space-y-0.5">
                <h4 id="globalLoaderTitle" class="text-xs font-bold text-slate-800">Memproses Data</h4>
                <p id="globalLoaderSubtitle" class="text-[10px] text-slate-500 font-medium">Mohon tunggu sebentar...</p>
            </div>
        </div>
    </div>

    <!-- PWA Service Worker Registration & Global App Helpers -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        // --vh: visual viewport height in px/100, updated when keyboard appears/disappears
        // Bottom sheets use calc(var(--vh)*80) for accurate sizing on all browsers
        function _updateVh() {
            const h = window.visualViewport ? window.visualViewport.height : window.innerHeight;
            document.documentElement.style.setProperty('--vh', (h * 0.01) + 'px');
        }
        _updateVh();
        (window.visualViewport || window).addEventListener('resize', _updateVh);

        // ── KR namespace ────────────────────────────────────────────────────────
        window.KR = {};

        KR.sheet = {
            open(id) {
                const el = document.getElementById(id);
                const ov = document.getElementById(id + 'Overlay');
                if (!el) return;

                // 1. Hide bottom nav
                const nav = document.getElementById('appBottomNav');
                if (nav) {
                    nav.style.transform     = 'translateY(110%)';
                    nav.style.opacity       = '0';
                    nav.style.pointerEvents = 'none';
                }

                // 2. Hide active FAB buttons
                document.querySelectorAll('[data-fab]').forEach(f => {
                    f.style.transform     = 'scale(0.8)';
                    f.style.opacity       = '0';
                    f.style.pointerEvents = 'none';
                });

                // 3. Show overlay (block first, reflow, then opacity)
                if (ov) {
                    ov.style.display       = 'block';
                    ov.style.pointerEvents = 'auto';
                    ov.offsetHeight; // reflow
                    ov.style.opacity       = '1';
                }

                // 4. Show sheet (flex first, reflow, then slide up)
                el.style.display       = 'flex';
                el.offsetHeight; // reflow
                el.style.transform     = 'translateY(0)';

                // 5. Lock background scroll
                document.body.style.overflow = 'hidden';

                // 6. Reset scroll content back to top
                const body = el.querySelector('[data-sheet-body]');
                if (body) body.scrollTop = 0;
            },

            close(id) {
                const el = document.getElementById(id);
                const ov = document.getElementById(id + 'Overlay');
                if (!el) return;

                // 1. Slide sheet away
                el.style.transform = 'translateY(100%)';

                // 2. Fade overlay
                if (ov) {
                    ov.style.opacity       = '0';
                    ov.style.pointerEvents = 'none';
                }

                // 3. Restore bottom nav
                const nav = document.getElementById('appBottomNav');
                if (nav) {
                    nav.style.transform     = '';
                    nav.style.opacity       = '';
                    nav.style.pointerEvents = '';
                }

                // 4. Restore active FAB buttons
                document.querySelectorAll('[data-fab]').forEach(f => {
                    f.style.transform     = '';
                    f.style.opacity       = '';
                    f.style.pointerEvents = '';
                });

                // 5. Unlock background scroll
                document.body.style.overflow = '';

                // 6. After transition finishes, remove completely from layout (0px footprint)
                setTimeout(() => {
                    el.style.display = 'none';
                    if (ov) ov.style.display = 'none';
                }, 320);
            }
        };

        // Backward-compat: views still call openBottomSheet() / closeBottomSheet()
        window.openBottomSheet  = (id) => KR.sheet.open(id);
        window.closeBottomSheet = (id) => KR.sheet.close(id);

        // Global Centered Loader Controller
        let loaderTimer = null;
        function showAppLoader(title = 'Memproses Data...', subtitle = 'Mohon tunggu sebentar...') {
            const loader = document.getElementById('globalAppLoader');
            const card = document.getElementById('globalAppLoaderCard');
            const titleEl = document.getElementById('globalLoaderTitle');
            const subEl = document.getElementById('globalLoaderSubtitle');

            if (!loader) return;
            if (titleEl) titleEl.textContent = title;
            if (subEl) subEl.textContent = subtitle;

            loader.classList.remove('opacity-0', 'pointer-events-none');
            if (card) card.classList.remove('scale-95');

            clearTimeout(loaderTimer);
            loaderTimer = setTimeout(() => {
                hideAppLoader();
            }, 10000); // 10s fallback
        }

        function hideAppLoader() {
            clearTimeout(loaderTimer);
            const loader = document.getElementById('globalAppLoader');
            const card = document.getElementById('globalAppLoaderCard');
            if (!loader) return;
            loader.classList.add('opacity-0', 'pointer-events-none');
            if (card) card.classList.add('scale-95');
        }

        window.addEventListener('pageshow', hideAppLoader);
        window.addEventListener('load', hideAppLoader);

        // Instant Page Prefetcher on touch/hover for snappy transitions
        const prefetchedUrls = new Set();
        function prefetchPage(url) {
            if (!url || prefetchedUrls.has(url)) return;
            try {
                const u = new URL(url, window.location.origin);
                if (u.origin !== window.location.origin || u.pathname === window.location.pathname) return;
                prefetchedUrls.add(url);
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                link.as = 'document';
                document.head.appendChild(link);
            } catch (e) {}
        }

        // Prefetch on pointer hover / touch start (natural 100-200ms lead before click)
        document.addEventListener('pointerover', function(e) {
            const link = e.target.closest('a');
            if (link && link.href) prefetchPage(link.href);
        }, { passive: true });

        document.addEventListener('touchstart', function(e) {
            const link = e.target.closest('a');
            if (link && link.href) prefetchPage(link.href);
        }, { passive: true });

        // Auto show loader on internal page navigation links (debounced 140ms so cached pages load without flicker)
        let navLoaderDebounce = null;
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            const target = link.getAttribute('target');

            if (!href || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank' || link.hasAttribute('download')) {
                return;
            }

            try {
                const url = new URL(href, window.location.origin);
                if (url.origin === window.location.origin && (url.pathname !== window.location.pathname || url.search !== window.location.search)) {
                    clearTimeout(navLoaderDebounce);
                    navLoaderDebounce = setTimeout(() => {
                        showAppLoader('Memuat Halaman...', 'Menyiapkan tampilan...');
                    }, 140);
                }
            } catch (err) {}
        });

        // Auto show loader on form submit (skip forms that have a Swal confirm handler)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.classList.contains('no-loader')) return;
            if (form.hasAttribute('data-swal-confirm')) return; // handled by confirmDelete
            showAppLoader('Menyimpan Data...', 'Sedang memproses permintaan...');
        });

        // Global SweetAlert Confirm Helper (delete / logout / destructive actions)
        window.confirmDelete = function(event, form, title = 'Hapus Data?', text = 'Data yang dihapus tidak dapat dikembalikan.', confirmText = 'Ya, Hapus!') {
            if (event && event.preventDefault) event.preventDefault();
            form.setAttribute('data-swal-confirm', '1');

            Swal.fire({
                title,
                text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: confirmText,
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'swal-compact rounded-3xl border border-slate-100 shadow-2xl',
                    confirmButton: 'rounded-xl font-bold px-4 py-2.5 text-xs',
                    cancelButton: 'rounded-xl font-medium px-4 py-2.5 text-xs'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showAppLoader('Memproses...', 'Mohon tunggu sebentar...');
                    form.submit();
                } else {
                    form.removeAttribute('data-swal-confirm');
                }
            });
            return false;
        };

        // SweetAlert Flash Notifications (compact)
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: {!! json_encode(session('success')) !!},
                timer: 2200,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: { popup: 'swal-compact rounded-3xl border border-slate-100 shadow-2xl' }
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: {!! json_encode(session('error')) !!},
                confirmButtonColor: '#059669',
                confirmButtonText: 'Tutup',
                customClass: { popup: 'swal-compact rounded-3xl border border-slate-100 shadow-2xl' }
            });
        @endif

        // Global Auto-Compression & Downscaling for Image Uploads (Camera / Phone Snapshots)
        document.addEventListener('change', function(e) {
            const input = e.target;
            if (input.tagName === 'INPUT' && input.type === 'file' && input.accept && input.accept.includes('image')) {
                const file = input.files && input.files[0];
                if (!file || !file.type.startsWith('image/')) return;
                if (file.type === 'image/gif' || file.type === 'image/svg+xml') return;

                const previewTarget = input.dataset.preview ? document.querySelector(input.dataset.preview) : null;
                const containerTarget = input.dataset.previewContainer ? document.querySelector(input.dataset.previewContainer) : null;
                const infoTarget = input.dataset.infoTarget ? document.querySelector(input.dataset.infoTarget) : null;

                const formatSize = (bytes) => {
                    if (bytes < 1024) return bytes + ' B';
                    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
                };

                const reader = new FileReader();
                reader.onload = function(event) {
                    if (previewTarget) {
                        previewTarget.src = event.target.result;
                        if (containerTarget) containerTarget.classList.remove('hidden');
                    }
                    if (infoTarget) {
                        infoTarget.textContent = `${formatSize(file.size)}`;
                    }

                    const img = new Image();
                    img.onload = function() {
                        const maxDim = 1600;
                        let w = img.width;
                        let h = img.height;

                        // Only compress/resize if large (exceeds maxDim or over 600KB)
                        if (w > maxDim || h > maxDim || file.size > 600 * 1024) {
                            if (w > h) {
                                if (w > maxDim) {
                                    h = Math.round((h * maxDim) / w);
                                    w = maxDim;
                                }
                            } else {
                                if (h > maxDim) {
                                    w = Math.round((w * maxDim) / h);
                                    h = maxDim;
                                }
                            }

                            const canvas = document.createElement('canvas');
                            canvas.width = w;
                            canvas.height = h;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, w, h);

                            canvas.toBlob(function(blob) {
                                if (blob && window.DataTransfer) {
                                    try {
                                        const cleanName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                                        const optimizedFile = new File([blob], cleanName, {
                                            type: 'image/jpeg',
                                            lastModified: Date.now()
                                        });
                                        const dt = new DataTransfer();
                                        dt.items.add(optimizedFile);
                                        input.files = dt.files;

                                        // Update preview & info if available
                                        if (previewTarget) {
                                            previewTarget.src = URL.createObjectURL(blob);
                                        }
                                        if (infoTarget) {
                                            infoTarget.textContent = `${formatSize(blob.size)} (Dioptimalkan)`;
                                        }
                                    } catch (err) {
                                        console.warn('DataTransfer unavailable:', err);
                                    }
                                }
                            }, 'image/jpeg', 0.85);
                        }
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Sync camera input directly to main form input
        window.handleCameraCapture = function(cameraInput, mainInputId) {
            if (!cameraInput.files || !cameraInput.files[0]) return;
            const file = cameraInput.files[0];
            const mainInput = document.getElementById(mainInputId);
            if (!mainInput) return;

            if (window.DataTransfer) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    mainInput.files = dt.files;
                    mainInput.dispatchEvent(new Event('change', { bubbles: true }));
                } catch (e) {
                    console.warn('DataTransfer failed on camera capture', e);
                }
            }
        };

        // Reset photo upload helper
        window.clearPhotoUpload = function(inputId, cameraInputId, containerId, previewId) {
            const input = document.getElementById(inputId);
            if (input) input.value = '';
            if (cameraInputId) {
                const cameraInput = document.getElementById(cameraInputId);
                if (cameraInput) cameraInput.value = '';
            }
            const container = document.getElementById(containerId);
            if (container) container.classList.add('hidden');
            const preview = document.getElementById(previewId);
            if (preview) preview.src = '';
        };

        // Global Helper for backward compatibility
        window.clearFotoUpload = function(inputId, containerId, previewId) {
            window.clearPhotoUpload(inputId, null, containerId, previewId);
        };

        // Auto clear previews on form reset
        document.addEventListener('reset', function(e) {
            if (e.target && e.target.querySelectorAll) {
                e.target.querySelectorAll('.photo-upload-wrapper').forEach(w => {
                    const id = w.dataset.uploadId;
                    if (id && window.clearPhotoUpload) {
                        window.clearPhotoUpload(id, id + '_camera', 'preview_box_' + id, 'preview_img_' + id);
                    }
                });
            }
        });
    </script>
</body>
</html>
