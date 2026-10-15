<div id="appDrawerOverlay"
     onclick="toggleAppDrawer()"
     class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs opacity-0 pointer-events-none transition-opacity duration-300"></div>

<aside id="appDrawer"
       class="fixed top-0 bottom-0 right-0 z-50 w-80 max-w-[85vw] bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">
    <!-- Header Drawer -->
    <div class="p-4 bg-emerald-800 text-white flex items-center justify-between">
        @auth
            <div class="flex items-center gap-3 min-w-0">
                @if(Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-2xl object-cover border border-white/20 shadow-inner flex-shrink-0">
                @else
                    <span class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-2xl shadow-inner flex-shrink-0">🐐</span>
                @endif
                <div class="min-w-0 flex-1">
                    <h3 class="font-extrabold text-sm leading-tight tracking-tight truncate">{{ Auth::user()->name }}</h3>
                    <p class="text-[11px] text-emerald-200 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
        @else
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-2xl shadow-inner">🐐</span>
                <div>
                    <h3 class="font-extrabold text-sm leading-tight tracking-tight">Kandang Reyot</h3>
                    <p class="text-[11px] text-emerald-200">Menu Sekunder & Pengaturan</p>
                </div>
            </div>
        @endauth
        <button type="button"
                onclick="toggleAppDrawer()"
                class="w-8 h-8 rounded-full flex items-center justify-center bg-white/10 hover:bg-white/20 text-white active:scale-95 transition flex-shrink-0"
                aria-label="Tutup Menu">
            <i class="ti ti-x text-lg"></i>
        </button>
    </div>

    <!-- Menu List (Sekunder & Pengaturan Only - Tidak Duplikasi Navigasi Utama) -->
    <div class="flex-1 overflow-y-auto p-4 space-y-5">
        <!-- 1. BANTUAN & PANDUAN -->
        <div>
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider px-2 block mb-1.5">BANTUAN & PANDUAN</span>
            <div class="space-y-1">
                <button type="button"
                        onclick="toggleAppDrawer(); openBottomSheet('sheetPanduan')"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 active:scale-98 transition text-left">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-base">
                        <i class="ti ti-book"></i>
                    </div>
                    <div>
                        <span class="block text-slate-900">Panduan Alur Kerja</span>
                        <span class="text-[10px] text-slate-400 font-normal">Siklus breeding & perawatan</span>
                    </div>
                </button>

                <a href="{{ route('dashboard') }}#kondisi-kandang" onclick="toggleAppDrawer()"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 active:scale-98 transition">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center text-base">
                        <i class="ti ti-building-warehouse"></i>
                    </div>
                    <div>
                        <span class="block text-slate-900">Kondisi Kandang</span>
                        <span class="text-[10px] text-slate-400 font-normal">Statistik ras & populasi</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- 2. PENGATURAN KANDANG -->
        <div>
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider px-2 block mb-1.5">PENGATURAN KANDANG</span>
            <div class="space-y-1">
                <button type="button"
                        onclick="toggleAppDrawer(); openBottomSheet('sheetPengaturan')"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 active:scale-98 transition text-left">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center text-base">
                        <i class="ti ti-adjustments"></i>
                    </div>
                    <div>
                        <span class="block text-slate-900">Parameter Siklus</span>
                        <span class="text-[10px] text-slate-400 font-normal">Masa pemulihan & semprot</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- 3. TENTANG APLIKASI -->
        <div>
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider px-2 block mb-1.5">INFORMASI SISTEM</span>
            <div class="space-y-1">
                <button type="button"
                        onclick="toggleAppDrawer(); openBottomSheet('sheetTentang')"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 active:scale-98 transition text-left">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-base">
                        <i class="ti ti-info-circle"></i>
                    </div>
                    <div>
                        <span class="block text-slate-900">Tentang Aplikasi</span>
                        <span class="text-[10px] text-slate-400 font-normal">PWA versi 1.0</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- 4. AKUN PENGGUNA -->
        <div>
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider px-2 block mb-1.5">AKUN & SESI</span>
            <div class="space-y-1">
                @auth
                    <form action="{{ route('logout') }}" method="POST" onsubmit="return confirmDelete(event, this, 'Keluar Akun?', 'Apakah Anda yakin ingin mengakhiri sesi login?');">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 active:scale-98 transition text-left">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-base">
                                <i class="ti ti-logout"></i>
                            </div>
                            <div>
                                <span class="block text-rose-700 font-bold">Keluar (Logout)</span>
                                <span class="text-[10px] text-slate-400 font-normal">Akhiri sesi login</span>
                            </div>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" onclick="toggleAppDrawer()"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 active:scale-98 transition">
                        <div class="w-8 h-8 rounded-lg bg-white text-emerald-700 flex items-center justify-center text-base shadow-2xs">
                            <i class="ti ti-login"></i>
                        </div>
                        <div>
                            <span class="block text-emerald-950 font-bold">Masuk dengan Google</span>
                            <span class="text-[10px] text-emerald-700 font-normal">Hubungkan akun Google</span>
                        </div>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Footer Drawer -->
    <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-xs text-slate-500">
        <span class="font-medium text-[11px]">Kandang Reyot v1.0</span>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">PWA Mobile</span>
    </div>
</aside>

<!-- Modal / Bottom Sheet Panduan -->
<x-bottom-sheet id="sheetPanduan" title="Panduan Alur Kerja Kandang">
    <div class="space-y-3.5 text-xs text-slate-700">
        <div class="p-3 bg-emerald-50/70 border border-emerald-100 rounded-2xl space-y-1">
            <span class="font-bold text-emerald-900 text-xs block">1. Siklus Breeding</span>
            <p class="text-emerald-800 leading-relaxed text-[11px]">
                Catat kambing &rarr; Pantau kesiapan kawin betina &rarr; Catat perkawinan &rarr; Catat kelahiran ketika melahirkan &rarr; Eksplor silsilah pohon keluarga.
            </p>
        </div>

        <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-2xl space-y-1">
            <span class="font-bold text-blue-900 text-xs block">2. Siklus Perawatan & Kesehatan</span>
            <p class="text-blue-800 leading-relaxed text-[11px]">
                Buat pengingat vitamin/obat cacing &rarr; Konfirmasi pemberian jika selesai &rarr; Catat jika kambing sakit hingga sembuh/mati &rarr; Jadwal rutin semprot kandang.
            </p>
        </div>

        <button type="button" onclick="closeBottomSheet('sheetPanduan')"
                class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl font-bold text-xs transition">
            Mengerti
        </button>
    </div>
</x-bottom-sheet>

<!-- Modal / Bottom Sheet Pengaturan -->
<x-bottom-sheet id="sheetPengaturan" title="Pengaturan Parameter Kandang">
    <div class="space-y-4 text-xs text-slate-700">
        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
            <span class="font-bold text-slate-900 block mb-1">Masa Pemulihan Betina</span>
            <p class="text-slate-500 mb-2 leading-relaxed">Interval waktu betina memulihkan diri pasca melahirkan sebelum siap kawin kembali.</p>
            <div class="flex items-center justify-between font-semibold">
                <span class="text-slate-600">Nilai Parameter:</span>
                <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">{{ config('kandang.masa_pemulihan_hari', 60) }} Hari</span>
            </div>
        </div>

        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
            <span class="font-bold text-slate-900 block mb-1">Interval Semprot Kandang</span>
            <p class="text-slate-500 mb-2 leading-relaxed">Jadwal disinfeksi kandang rutin otomatis setiap periode hari.</p>
            <div class="flex items-center justify-between font-semibold">
                <span class="text-slate-600">Nilai Parameter:</span>
                <span class="font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-lg border border-teal-200">{{ config('kandang.interval_semprot_kandang_hari', 14) }} Hari</span>
            </div>
        </div>

        <button type="button" onclick="closeBottomSheet('sheetPengaturan')"
                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-xs shadow-xs transition">
            Tutup
        </button>
    </div>
</x-bottom-sheet>

<!-- Modal / Bottom Sheet Tentang -->
<x-bottom-sheet id="sheetTentang" title="Tentang Kandang Reyot">
    <div class="space-y-4 text-center py-2 text-slate-700">
        <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-800 text-3xl flex items-center justify-center mx-auto shadow-inner">
            🐐
        </div>
        <div>
            <h4 class="font-extrabold text-base text-slate-900 tracking-tight">Kandang Reyot</h4>
            <p class="text-xs text-slate-500 mt-0.5">Sistem Manajemen Data & Breeding Kambing PWA</p>
        </div>
        <p class="text-xs text-slate-600 max-w-xs mx-auto leading-relaxed">
            Aplikasi pencatatan individu kambing, pelacakan silsilah keturunan, perkawinan, kelahiran, serta pengingat perawatan kandang.
        </p>
        <button type="button" onclick="closeBottomSheet('sheetTentang')"
                class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl font-bold text-xs transition">
            Tutup
        </button>
    </div>
</x-bottom-sheet>

<script>
    function toggleAppDrawer() {
        const drawer = document.getElementById('appDrawer');
        const overlay = document.getElementById('appDrawerOverlay');
        if (!drawer || !overlay) return;

        const isClosed = drawer.classList.contains('translate-x-full');
        if (isClosed) {
            drawer.classList.remove('translate-x-full');
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100');
            document.body.classList.add('overflow-hidden');
        } else {
            drawer.classList.add('translate-x-full');
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('overflow-hidden');
        }
    }
</script>
