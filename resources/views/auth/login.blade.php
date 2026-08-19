@extends('layouts.public')
@section('title', 'Login - MariRent')
@section('content')

<style>
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
    @keyframes fadeInUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
    @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
    @keyframes pulse-ring { 0%{transform:scale(.8);opacity:1} 100%{transform:scale(2.4);opacity:0} }
    @keyframes slideIn { from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:translateX(0)} }
    @keyframes gradientShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    .animate-float { animation: float 3s ease-in-out infinite; }
    .animate-float-delay { animation: float 3s ease-in-out 0.5s infinite; }
    .animate-fadeInUp { animation: fadeInUp 0.7s ease-out both; }
    .animate-fadeInUp-delay { animation: fadeInUp 0.7s ease-out 0.15s both; }
    .animate-fadeInUp-delay2 { animation: fadeInUp 0.7s ease-out 0.3s both; }
    .animate-shimmer { background: linear-gradient(90deg,transparent 30%,rgba(255,255,255,0.4) 50%,transparent 70%); background-size: 200% 100%; animation: shimmer 2s infinite; }
    .animate-slideIn { animation: slideIn 0.5s ease-out both; }
    .gradient-bg { background: linear-gradient(-45deg,#f0f9ff,#e0f2fe,#bae6fd,#7dd3fc); background-size:400% 400%; animation: gradientShift 8s ease infinite; }
    .glass-card { background: rgba(255,255,255,0.75); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.6); }
    .input-focus { transition: all 0.3s ease; }
    .input-focus:focus { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(14,165,233,0.15); }
    .btn-hover { transition: all 0.3s ease; position:relative; overflow:hidden; }
    .btn-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(14,165,233,0.35); }
    .btn-hover:active { transform: translateY(0); }
    .btn-hover .btn-loading { display:none; }
    .btn-hover.is-loading .btn-text { display:none; }
    .btn-hover.is-loading .btn-loading { display:flex; align-items:center; justify-content:center; }
    .btn-hover.is-loading { pointer-events:none; opacity:0.8; }
    .floating-shape { position:absolute; border-radius:50%; opacity:0.08; }
</style>

<div class="gradient-bg min-h-[85vh] flex items-center justify-center py-12 px-4 relative overflow-hidden">
    {{-- Floating shapes --}}
    <div class="floating-shape w-64 h-64 bg-sky-400 -top-20 -right-20 animate-float" style="position:absolute;"></div>
    <div class="floating-shape w-40 h-40 bg-sky-600 bottom-10 -left-10 animate-float-delay" style="position:absolute;"></div>
    <div class="floating-shape w-20 h-20 bg-sky-300 top-1/3 left-10 animate-float" style="position:absolute;"></div>
    <div class="floating-shape w-16 h-16 bg-sky-500 bottom-1/3 right-20 animate-float-delay" style="position:absolute;"></div>

    <div class="max-w-5xl w-full flex items-center gap-12 relative z-10">
        {{-- Left - Branding --}}
        <div class="hidden lg:flex flex-col flex-1 animate-fadeInUp">
            <div class="mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 mb-8">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center shadow-lg shadow-sky-500/30">
                        <i class="fas fa-car-side text-white text-2xl"></i>
                    </div>
                    <span class="text-3xl font-extrabold text-navy-900">Mari<span class="text-sky-600">Rent</span></span>
                </a>
                <h1 class="text-4xl font-extrabold text-navy-900 leading-tight">Selamat Datang<br>Kembali!</h1>
                <p class="text-navy-500 mt-4 text-lg max-w-sm">Masuk ke akun Anda untuk mulai menyewa kendaraan dan peralatan favorit.</p>
            </div>

            <div class="space-y-4 mt-4">
                <div class="flex items-center gap-3 animate-slideIn" style="animation-delay:0.3s">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center"><i class="fas fa-shield-halved text-emerald-500 text-sm"></i></div>
                    <div><p class="text-[13px] font-semibold text-navy-800">Transaksi Aman</p><p class="text-[11px] text-gray-400">Sistem pembayaran terenkripsi</p></div>
                </div>
                <div class="flex items-center gap-3 animate-slideIn" style="animation-delay:0.45s">
                    <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center"><i class="fas fa-bolt text-sky-500 text-sm"></i></div>
                    <div><p class="text-[13px] font-semibold text-navy-800">Booking Instan</p><p class="text-[11px] text-gray-400">Langsung tanpa ribet</p></div>
                </div>
                <div class="flex items-center gap-3 animate-slideIn" style="animation-delay:0.6s">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center"><i class="fas fa-headset text-amber-500 text-sm"></i></div>
                    <div><p class="text-[13px] font-semibold text-navy-800">Support 24/7</p><p class="text-[11px] text-gray-400">Siap bantu kapan saja</p></div>
                </div>
            </div>
        </div>

        {{-- Right - Form --}}
        <div class="w-full max-w-md animate-fadeInUp-delay2">
            <div class="glass-card rounded-3xl p-8 shadow-xl shadow-sky-500/5">
                {{-- Mobile Logo --}}
                <div class="lg:hidden text-center mb-6">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                        <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/30"><i class="fas fa-car-side text-white"></i></div>
                        <span class="text-xl font-extrabold text-navy-900">Mari<span class="text-sky-600">Rent</span></span>
                    </a>
                </div>

                <div class="text-center mb-7">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-sky-500/20">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-navy-900">Masuk ke Akun</h2>
                    <p class="text-[13px] text-gray-400 mt-1">Silakan masukkan kredensial Anda</p>
                </div>

                {{-- Success --}}
                @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-[13px] flex items-center gap-2 animate-fadeInUp">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
                @endif

                {{-- Errors --}}
                @if($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-[13px] animate-fadeInUp">
                    @foreach($errors->all() as $e)
                    <p class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> {{ $e }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Email</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-envelope text-sm"></i></span>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] text-navy-800 focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white outline-none"
                                   placeholder="email@contoh.com">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Password</label>
                        <div class="relative" x-data="{ show: false }">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-lock text-sm"></i></span>
                            <input :type="show ? 'text' : 'password'" name="password" required
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-10 py-3 text-[13px] text-navy-800 focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white outline-none"
                                   placeholder="Masukkan password">
                            <button type="button" @click="show = !show" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-sky-500 transition">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" class="text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center text-[13px] text-navy-600 cursor-pointer group">
                            <input type="checkbox" name="remember" class="mr-2.5 rounded-lg border-gray-300 text-sky-500 focus:ring-sky-500/30">
                            <span class="group-hover:text-sky-600 transition">Ingat saya</span>
                        </label>
                        <a href="#" class="text-[12px] text-sky-600 font-medium hover:text-sky-700 hover:underline transition">Lupa password?</a>
                    </div>

                    <button type="submit" id="loginBtn" class="btn-hover w-full bg-gradient-to-r from-sky-500 to-sky-600 text-white py-3.5 rounded-xl font-bold text-[14px] shadow-lg shadow-sky-500/25">
                        <span class="btn-text flex items-center justify-center gap-2"><i class="fas fa-sign-in-alt"></i> Masuk</span>
                        <span class="btn-loading"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...</span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-[13px] text-gray-400">Belum punya akun?
                        <a href="{{ route('register') }}" class="text-sky-600 font-semibold hover:text-sky-700 hover:underline transition">Daftar sekarang</a>
                    </p>
                </div>

                {{-- Demo --}}
                <div class="mt-5 pt-5 border-t border-gray-100">
                    <p class="text-[10px] text-gray-300 text-center uppercase tracking-wider font-semibold mb-3">Demo Accounts</p>
                    <div class="grid grid-cols-2 gap-2" x-data>
                        @php $demos = [['Admin','admin@marirent.com'],['Owner','owner@marirent.com'],['User','user@marirent.com'],['Driver','driver@marirent.com']]; @endphp
                        @foreach($demos as $d)
                        <button type="button" @click="$dispatch('fill-login',{email:'{{ $d[1] }}',pass:'password'})"
                                class="bg-sky-50/80 hover:bg-sky-100 text-[11px] text-navy-600 py-2 rounded-lg font-medium transition border border-sky-100 hover:border-sky-200">
                            <i class="fas fa-user-circle mr-1 text-sky-400"></i> {{ $d[0] }}
                        </button>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-gray-300 text-center mt-2">Password: <span class="font-semibold text-gray-400">password</span></p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Alpine event to fill login form from demo buttons
    document.addEventListener('fill-login', (e) => {
        document.querySelector('input[name="email"]').value = e.detail.email;
        document.querySelector('input[name="password"]').value = e.detail.pass;
    });

    // Loading state on submit
    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('loginBtn');
        btn.classList.add('is-loading');
        btn.innerHTML = '<span class="btn-loading flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...</span>';
    });
</script>
@endpush
@endsection
