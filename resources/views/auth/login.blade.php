@extends('layouts.app')

@section('title', 'Masuk Akun')

@section('content')
<div class="w-full max-w-sm mx-auto flex flex-col items-center justify-center text-center space-y-8 my-auto py-6">
    <!-- Brand Section: Prominent Logo & Title -->
    <div class="flex flex-col items-center space-y-3">
        <div class="w-28 h-28 sm:w-32 sm:h-32 flex items-center justify-center select-none">
            <img src="{{ asset('images/goat_logo.png') }}" alt="Logo Kandang Reyot" class="w-full h-full object-contain drop-shadow-sm">
        </div>
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Kandang Reyot
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                Sistem Pencatatan Ternak & Silsilah Kambing
            </p>
        </div>
    </div>

    <!-- Login Action (Clean, Standard Front-End) -->
    <div class="w-full space-y-3 pt-2">
        <!-- Google Login Button -->
        <button type="button"
                id="btnGoogleLogin"
                class="w-full flex items-center justify-center gap-3 px-5 py-3.5 rounded-2xl bg-white hover:bg-slate-50 active:scale-98 text-slate-700 font-semibold text-sm border border-slate-300/80 shadow-xs hover:shadow transition cursor-pointer">
            <!-- Google "G" Logo SVG -->
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span class="tracking-tight font-bold text-slate-800">Masuk dengan Google</span>
        </button>

        <p class="text-[11px] text-slate-400 font-medium">
            Masuk dengan akun Google untuk mengelola data kandang Anda.
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
