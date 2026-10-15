@props([
    'title' => 'Kandang Reyot',
    'hasBack' => false,
    'backUrl' => null,
    'showLogo' => true,
    'subtitle' => null,
])

<header class="sticky top-0 z-30 w-full {{ request()->routeIs('dashboard') ? 'bg-emerald-800 shadow-none' : 'bg-emerald-700/95 shadow-md' }} backdrop-blur-md text-white transition-all">
    <div class="px-4 py-3 flex items-center justify-between min-h-[56px]">
        <div class="flex items-center gap-3">
            @if($hasBack)
                <a href="{{ $backUrl ?? url()->previous() }}"
                   class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 transition text-white"
                   aria-label="Kembali">
                    <i class="ti ti-chevron-left text-2xl"></i>
                </a>
            @elseif(request()->routeIs('dashboard'))
                @auth
                    @if(auth()->user()->avatar)
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-emerald-400/40 shadow-sm flex items-center justify-center bg-emerald-600 text-white shrink-0">
                            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name ?? 'User' }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-full border-2 border-emerald-400/40 shadow-sm flex items-center justify-center bg-emerald-600 text-white font-bold text-sm shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email ?? 'P', 0, 1)) }}
                        </div>
                    @endif
                @else
                    <div class="w-10 h-10 rounded-full border-2 border-emerald-400/40 shadow-sm flex items-center justify-center bg-emerald-600 text-white shrink-0">
                        <i class="ti ti-user text-lg"></i>
                    </div>
                @endauth
            @elseif($showLogo)
                <a href="{{ route('dashboard') }}" class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-xl shadow-inner active:scale-95 transition">
                    <span>🐐</span>
                </a>
            @endif

            <div>
                <h1 class="text-base font-bold tracking-tight text-white leading-tight flex items-center gap-2">
                    {{ request()->routeIs('dashboard') ? 'Dashboard' : $title }}
                </h1>
                @if($subtitle && !request()->routeIs('dashboard'))
                    <p class="text-[11px] text-emerald-100 font-medium leading-none mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{ $slot ?? '' }}

            @unless(request()->routeIs('dashboard'))
                <button type="button"
                        onclick="toggleAppDrawer()"
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 transition text-white"
                        aria-label="Menu Aplikasi">
                    <i class="ti ti-menu-2 text-xl"></i>
                </button>
            @endunless
        </div>
    </div>
</header>
