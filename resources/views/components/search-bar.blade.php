@props([
    'name' => 'search',
    'value' => null,
    'placeholder' => 'Cari kambing...',
    'action' => null,
])

<form action="{{ $action ?? url()->current() }}" method="GET" class="relative w-full">
    @foreach(request()->except([$name, 'page']) as $key => $val)
        @if(is_array($val))
            @foreach($val as $item)
                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
            @endforeach
        @elseif($val !== null)
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endif
    @endforeach

    <div class="relative flex items-center">
        <span class="absolute left-3.5 text-slate-400 pointer-events-none text-base">
            <i class="ti ti-search"></i>
        </span>

        <input type="search"
               name="{{ $name }}"
               value="{{ $value ?? request($name) }}"
               placeholder="{{ $placeholder }}"
               {{ $attributes->merge(['class' => 'w-full pl-10 pr-10 py-3 bg-white border border-slate-200 rounded-2xl text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-2xs transition']) }}>

        @if(request()->filled($name))
            <a href="{{ $action ?? url()->current() }}"
               class="absolute right-3 p-1 rounded-lg text-slate-400 hover:text-slate-600 active:scale-95"
               aria-label="Bersihkan pencarian">
                <i class="ti ti-x text-sm"></i>
            </a>
        @endif
    </div>
</form>
