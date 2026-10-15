<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name', 'Kandang Reyot') }} - @yield('title', 'Aplikasi Mobile')</title>

    <!-- [PWA Meta Tags] -->
    <meta name="theme-color" content="#047857">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Kandang Reyot">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- [Favicon] -->
    <link rel="icon" href="{{ asset('assets/dist/assets/images/favicon.svg') }}" type="image/x-icon">

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

        /* Subtle page entrance without full reload flash */
        .app-screen-content {
            animation: appScreenEnter 0.16s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes appScreenEnter {
            from {
                opacity: 0.85;
                transform: translateY(2px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
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
    </style>
</head>
<body class="h-full bg-slate-950 antialiased select-none text-slate-800">

    <!-- Mobile Device Container Wrapper (Centered on Desktop with native card frame) -->
    <div class="w-full max-w-lg mx-auto min-h-screen bg-slate-50 relative flex flex-col shadow-2xl border-x border-slate-800/40 overflow-x-hidden">

        <!-- Top App Header -->
        <x-app-header :title="View::hasSection('title') ? trim(View::getSection('title')) : 'Kandang Reyot'" />

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
        <main class="flex-1 w-full px-3.5 py-4 pb-28 sm:pb-32 app-screen-content">
            @yield('content')
        </main>

        <!-- Slide-over Drawer Menu (Secondary & Settings only) -->
        <x-drawer />

        <!-- Fixed Bottom Navigation Bar (Primary screens only) -->
        <x-bottom-navigation />

    </div>

    <!-- [Global Centered Loading Spinner] -->
    <div id="globalAppLoader"
         class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-950/70 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200"
         aria-hidden="true">
        <div id="globalAppLoaderCard" class="bg-white/95 rounded-3xl p-6 shadow-2xl flex flex-col items-center gap-3 border border-slate-100 max-w-[220px] w-full text-center transform scale-95 transition-transform duration-200">
            <div class="relative w-14 h-14 flex items-center justify-center">
                <div class="absolute inset-0 rounded-full border-4 border-emerald-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-emerald-600 border-t-transparent animate-spin"></div>
                <span class="text-xl select-none">🐐</span>
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

        // Auto show loader on internal page navigation links
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
                    showAppLoader('Memuat Halaman...', 'Menyiapkan tampilan...');
                }
            } catch (err) {}
        });

        // Auto show loader on form submit (unless opted out)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.classList.contains('no-loader')) return;
            showAppLoader('Menyimpan Data...', 'Sedang memproses permintaan...');
        });

        // Global SweetAlert Confirm Delete Helper
        window.confirmDelete = function(event, form, title = 'Hapus Data?', text = 'Data yang dihapus tidak dapat dikembalikan.') {
            if (event && event.preventDefault) {
                event.preventDefault();
            }
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-3xl border border-slate-100 shadow-2xl',
                    confirmButton: 'rounded-xl font-bold px-4 py-2.5 text-xs',
                    cancelButton: 'rounded-xl font-medium px-4 py-2.5 text-xs'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showAppLoader('Menghapus Data...', 'Sedang menghapus record...');
                    form.submit();
                }
            });
            return false;
        };

        // SweetAlert Flash Notifications
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: {!! json_encode(session('success')) !!},
                timer: 2400,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-3xl border border-slate-100 shadow-2xl'
                }
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: {!! json_encode(session('error')) !!},
                confirmButtonColor: '#059669',
                confirmButtonText: 'Tutup',
                customClass: {
                    popup: 'rounded-3xl border border-slate-100 shadow-2xl'
                }
            });
        @endif
    </script>
</body>
</html>
