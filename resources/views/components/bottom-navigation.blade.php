<nav id="appBottomNav"
     class="fixed bottom-0 left-0 right-0 z-40 max-w-lg mx-auto bg-white/95 backdrop-blur-lg rounded-t-3xl border-t border-x border-slate-200/80 shadow-[0_-6px_25px_rgba(0,0,0,0.08)] pb-[env(safe-area-inset-bottom)] transition-all duration-300 ease-out will-change-transform"
     aria-label="Navigasi Bawah">
    <div class="grid grid-cols-5 h-16 items-center px-1">
        <!-- 1. Home -->
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center justify-center h-full py-1 text-xs font-medium transition-all group {{ request()->routeIs('dashboard') ? 'text-emerald-700 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <div class="w-10 h-7 flex items-center justify-center rounded-full transition-all {{ request()->routeIs('dashboard') ? 'bg-emerald-100 text-emerald-800 scale-105' : 'group-hover:bg-slate-100' }}">
                <i class="ti ti-smart-home text-lg"></i>
            </div>
            <span class="text-[10px] mt-0.5 tracking-tight">Home</span>
        </a>

        <!-- 2. Kambing -->
        <a href="{{ route('kambings.index') }}"
           class="flex flex-col items-center justify-center h-full py-1 text-xs font-medium transition-all group {{ request()->routeIs('kambings.*') ? 'text-emerald-700 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <div class="w-10 h-7 flex items-center justify-center rounded-full transition-all {{ request()->routeIs('kambings.*') ? 'bg-emerald-100 text-emerald-800 scale-105' : 'group-hover:bg-slate-100' }}">
                <i class="ti ti-clipboard-list text-lg"></i>
            </div>
            <span class="text-[10px] mt-0.5 tracking-tight">Kambing</span>
        </a>

        <!-- 3. Perawatan -->
        <a href="{{ route('pengingat-perawatan.index') }}"
           class="flex flex-col items-center justify-center h-full py-1 text-xs font-medium transition-all group {{ request()->routeIs('pengingat-perawatan.*') || request()->routeIs('riwayat-sakit.*') ? 'text-emerald-700 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <div class="w-10 h-7 flex items-center justify-center rounded-full transition-all {{ request()->routeIs('pengingat-perawatan.*') || request()->routeIs('riwayat-sakit.*') ? 'bg-emerald-100 text-emerald-800 scale-105' : 'group-hover:bg-slate-100' }}">
                <i class="ti ti-pill text-lg"></i>
            </div>
            <span class="text-[10px] mt-0.5 tracking-tight">Perawatan</span>
        </a>

        <!-- 4. Silsilah -->
        <a href="{{ route('silsilah.index') }}"
           class="flex flex-col items-center justify-center h-full py-1 text-xs font-medium transition-all group {{ request()->routeIs('silsilah.*') ? 'text-emerald-700 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <div class="w-10 h-7 flex items-center justify-center rounded-full transition-all {{ request()->routeIs('silsilah.*') ? 'bg-emerald-100 text-emerald-800 scale-105' : 'group-hover:bg-slate-100' }}">
                <i class="ti ti-sitemap text-lg"></i>
            </div>
            <span class="text-[10px] mt-0.5 tracking-tight">Silsilah</span>
        </a>

        <!-- 5. Menu -->
        <button type="button"
                onclick="toggleAppDrawer()"
                class="flex flex-col items-center justify-center h-full py-1 text-xs font-medium transition-all text-slate-500 hover:text-slate-800 active:scale-95 group">
            <div class="w-10 h-7 flex items-center justify-center rounded-full group-hover:bg-slate-100 transition-all">
                <i class="ti ti-menu-2 text-lg"></i>
            </div>
            <span class="text-[10px] mt-0.5 tracking-tight">Menu</span>
        </button>
    </div>
</nav>
