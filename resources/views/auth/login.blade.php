@extends('layouts.app')

@section('title', 'Masuk Akun')

@section('content')
<div class="space-y-5 max-w-sm mx-auto py-2">
    <!-- 1. Hero Brand Card -->
    <div class="bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-800 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden text-center space-y-3">
        <div class="absolute -right-6 -bottom-8 text-8xl opacity-15 select-none pointer-events-none">🐐</div>
        
        <div class="w-16 h-16 rounded-3xl bg-white/15 backdrop-blur-md border border-white/20 mx-auto flex items-center justify-center text-3xl shadow-inner animate-bounce-subtle">
            🐐
        </div>

        <div class="space-y-1">
            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-emerald-100 backdrop-blur-xs">
                <span>🟢</span> PWA Mobile Peternakan
            </span>
            <h1 class="text-xl font-black tracking-tight text-white mt-1">Kandang Reyot</h1>
            <p class="text-xs text-emerald-100/90 leading-relaxed max-w-xs mx-auto">
                Sistem Manajemen Peternakan Kambing Modern, Silsilah Digital & Siklus Breeding.
            </p>
        </div>
    </div>

    <!-- 2. Login Action Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-5">
        <div class="text-center space-y-1">
            <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Masuk ke Akun Anda</h2>
            <p class="text-xs text-slate-500">
                Akses cepat & aman menggunakan akun Google Anda tanpa perlu mengingat password.
            </p>
        </div>

        <!-- Google Login Button -->
        <button type="button"
                id="btnGoogleLogin"
                class="w-full flex items-center justify-center gap-3.5 px-4 py-3.5 rounded-2xl bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200 shadow-sm active:scale-98 transition group cursor-pointer">
            <!-- Google "G" Logo SVG -->
            <svg class="w-5 h-5 flex-shrink-0 group-hover:scale-105 transition" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span class="tracking-tight text-slate-800 group-hover:text-slate-900">Masuk dengan Google</span>
        </button>

        <!-- Divider -->
        <div class="relative flex items-center justify-center">
            <div class="border-t border-slate-100 w-full"></div>
            <span class="bg-white px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-widest absolute">
                Fitur Utama
            </span>
        </div>

        <!-- Quick Highlight Chips -->
        <div class="grid grid-cols-2 gap-2 text-[11px] font-semibold text-slate-700">
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-2">
                <span class="text-base">🐐</span>
                <span>Populasi & Induk</span>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-2">
                <span class="text-base">🌳</span>
                <span>Pohon Silsilah</span>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-2">
                <span class="text-base">❤️</span>
                <span>Siklus Breeding</span>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-2">
                <span class="text-base">💊</span>
                <span>Pengingat Obat</span>
            </div>
        </div>
    </div>

    <!-- 3. Security & Info Footer -->
    <div class="text-center space-y-1 text-slate-400">
        <p class="text-[11px] flex items-center justify-center gap-1.5 font-medium">
            <i class="ti ti-shield-check text-emerald-600 text-sm"></i>
            <span>Google Firebase Authentication Protected</span>
        </p>
        <p class="text-[10px]">
            Project: <code class="bg-slate-200/60 px-1 py-0.5 rounded text-slate-600">project-103450417721</code>
        </p>
    </div>
</div>

<!-- Firebase Google Authentication Script -->
<script type="module">
    // Import the functions you need from the SDKs you need
    import { initializeApp } from "https://www.gstatic.com/firebasejs/13.0.0/firebase-app.js";
    import { getAnalytics } from "https://www.gstatic.com/firebasejs/13.0.0/firebase-analytics.js";
    import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/13.0.0/firebase-auth.js";

    // Your web app's Firebase configuration
    const firebaseConfig = {
        apiKey: "AIzaSyC1M_2sVW4tl8XPyQrvorZN9GedIZZB2gY",
        authDomain: "kandangreyot.firebaseapp.com",
        projectId: "kandangreyot",
        storageBucket: "kandangreyot.firebasestorage.app",
        messagingSenderId: "103450417721",
        appId: "1:103450417721:web:0bd4c6cc6b60decf9d4be8",
        measurementId: "G-Q3MKVL6KGR"
    };

    // Initialize Firebase
    const app = initializeApp(firebaseConfig);
    try {
        getAnalytics(app);
    } catch (e) {
        // Analytics may be blocked or optional
    }

    const auth = getAuth(app);
    const provider = new GoogleAuthProvider();
    provider.setCustomParameters({
        prompt: 'select_account'
    });

    const btnGoogleLogin = document.getElementById('btnGoogleLogin');

    if (btnGoogleLogin) {
        btnGoogleLogin.addEventListener('click', async () => {
            try {
                if (typeof showAppLoader === 'function') {
                    showAppLoader('Menghubungkan ke Google...', 'Silakan pilih akun Google Anda di jendela pop-up...');
                }

                const result = await signInWithPopup(auth, provider);
                const user = result.user;
                const idToken = await user.getIdToken();

                if (typeof showAppLoader === 'function') {
                    showAppLoader('Memverifikasi Akun...', 'Menyiapkan sesi aplikasi Anda...');
                }

                const response = await fetch("{{ route('login.firebase') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: user.displayName,
                        email: user.email,
                        avatar: user.photoURL,
                        uid: user.uid,
                        token: idToken
                    })
                });

                const data = await response.json();

                if (data.success) {
                    hideAppLoader();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Masuk!',
                        text: 'Selamat datang, ' + (user.displayName || 'Peternak') + '!',
                        timer: 1500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        customClass: { popup: 'swal-compact rounded-3xl border border-slate-100 shadow-2xl' }
                    }).then(() => {
                        window.location.href = data.redirect || "{{ route('dashboard') }}";
                    });
                } else {
                    hideAppLoader();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Masuk',
                        text: data.message || 'Terjadi kesalahan saat memverifikasi sesi login.',
                        confirmButtonColor: '#059669',
                        customClass: { popup: 'swal-compact rounded-3xl border border-slate-100 shadow-2xl' }
                    });
                }
            } catch (error) {
                hideAppLoader();

                if (error.code === 'auth/popup-closed-by-user' || error.code === 'auth/cancelled-popup-request') {
                    return;
                }

                console.error('Firebase Auth Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Autentikasi Gagal',
                    text: error.message || 'Tidak dapat menyelesaikan proses masuk dengan Google.',
                    confirmButtonColor: '#059669',
                    customClass: { popup: 'swal-compact rounded-3xl border border-slate-100 shadow-2xl' }
                });
            }
        });
    }
</script>
@endsection
