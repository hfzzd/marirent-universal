@extends('layouts.public')
@section('title', 'Daftar - MariRent')
@section('content')

<style>
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
    @keyframes fadeInUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
    @keyframes slideIn { from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:translateX(0)} }
    @keyframes gradientShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    @keyframes checkBounce { 0%{transform:scale(0)} 50%{transform:scale(1.2)} 100%{transform:scale(1)} }
    .animate-float { animation: float 3s ease-in-out infinite; }
    .animate-float-delay { animation: float 3s ease-in-out 0.5s infinite; }
    .animate-fadeInUp { animation: fadeInUp 0.7s ease-out both; }
    .animate-fadeInUp-delay { animation: fadeInUp 0.7s ease-out 0.15s both; }
    .animate-fadeInUp-delay2 { animation: fadeInUp 0.7s ease-out 0.3s both; }
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
    .check-anim { animation: checkBounce 0.4s ease-out both; }
</style>

<div class="gradient-bg min-h-[85vh] flex items-center justify-center py-12 px-4 relative overflow-hidden">
    {{-- Floating shapes --}}
    <div class="floating-shape w-64 h-64 bg-sky-400 -top-20 -right-20 animate-float" style="position:absolute;"></div>
    <div class="floating-shape w-40 h-40 bg-sky-600 bottom-10 -left-10 animate-float-delay" style="position:absolute;"></div>
    <div class="floating-shape w-20 h-20 bg-sky-300 top-1/4 left-10 animate-float" style="position:absolute;"></div>
    <div class="floating-shape w-16 h-16 bg-sky-500 bottom-1/4 right-20 animate-float-delay" style="position:absolute;"></div>

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
                <h1 class="text-4xl font-extrabold text-navy-900 leading-tight">Buat Akun<br>Baru</h1>
                <p class="text-navy-500 mt-4 text-lg max-w-sm">Daftar sekarang dan mulai petualangan Anda bersama MariRent.</p>
            </div>

            <div class="space-y-4 mt-4">
                <div class="flex items-center gap-3 animate-slideIn" style="animation-delay:0.3s">
                    <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center"><i class="fas fa-car text-sky-500 text-sm"></i></div>
                    <div><p class="text-[13px] font-semibold text-navy-800">Ribuan Pilihan</p><p class="text-[11px] text-gray-400">Mobil, motor, kamera, alat camping & HP</p></div>
                </div>
                <div class="flex items-center gap-3 animate-slideIn" style="animation-delay:0.45s">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center"><i class="fas fa-tag text-emerald-500 text-sm"></i></div>
                    <div><p class="text-[13px] font-semibold text-navy-800">Harga Terjangkau</p><p class="text-[11px] text-gray-400">Sesuai budget Anda</p></div>
                </div>
                <div class="flex items-center gap-3 animate-slideIn" style="animation-delay:0.6s">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center"><i class="fas fa-star text-amber-500 text-sm"></i></div>
                    <div><p class="text-[13px] font-semibold text-navy-800">Rating Terbaik</p><p class="text-[11px] text-gray-400">Dipercaya ribuan pelanggan</p></div>
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
                        <i class="fas fa-user-plus text-white text-xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-navy-900">Buat Akun Baru</h2>
                    <p class="text-[13px] text-gray-400 mt-1">Isi data diri Anda di bawah ini</p>
                </div>

                {{-- Errors --}}
                @if($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-[13px] animate-fadeInUp">
                    @foreach($errors->all() as $e)
                    <p class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> {{ $e }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf
                    <div class="mb-3.5">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Nama Lengkap</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-user text-sm"></i></span>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] text-navy-800 focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white outline-none"
                                   placeholder="Masukkan nama lengkap">
                        </div>
                    </div>

                    <div class="mb-3.5">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Email</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-envelope text-sm"></i></span>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] text-navy-800 focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white outline-none"
                                   placeholder="email@contoh.com">
                        </div>
                    </div>

                    <div class="mb-3.5">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">No. Telepon <span class="text-gray-300 font-normal">(opsional)</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-phone text-sm"></i></span>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] text-navy-800 focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white outline-none"
                                   placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="mb-3.5" x-data="{ show: false }">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Password</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-lock text-sm"></i></span>
                            <input :type="show ? 'text' : 'password'" name="password" required
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-10 py-3 text-[13px] text-navy-800 focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white outline-none"
                                   placeholder="Minimal 6 karakter">
                            <button type="button" @click="show = !show" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-sky-500 transition">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" class="text-sm"></i>
                            </button>
                        </div>
                        {{-- Password strength --}}
                        <div class="mt-2 flex gap-1" x-data="{ strength: 0 }"
                             x-init="$watch('$root', () => {
                                 const p = document.querySelector('input[name=password]').value;
                                 let s = 0;
                                 if(p.length >= 6) s++;
                                 if(p.length >= 8) s++;
                                 if(/[A-Z]/.test(p)) s++;
                                 if(/[0-9]/.test(p)) s++;
                                 if(/[^A-Za-z0-9]/.test(p)) s++;
                                 strength = s;
                             })" @input.window="const p = $el.closest('form').querySelector('input[name=password]').value; let s=0; if(p.length>=6)s++; if(p.length>=8)s++; if(/[A-Z]/.test(p))s++; if(/[0-9]/.test(p))s++; if(/[^A-Za-z0-9]/.test(p))s++; strength=s">
                            <template x-for="i in 5">
                                <div class="h-1 flex-1 rounded-full transition-all duration-300" :class="i <= strength ? (strength <= 2 ? 'bg-red-400' : strength <= 3 ? 'bg-amber-400' : 'bg-emerald-400') : 'bg-gray-100'"></div>
                            </template>
                        </div>
                    </div>

                    <div class="mb-6" x-data="{ show: false }">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-lock text-sm"></i></span>
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" required
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-10 py-3 text-[13px] text-navy-800 focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white outline-none"
                                   placeholder="Ulangi password">
                            <button type="button" @click="show = !show" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-sky-500 transition">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" class="text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="registerBtn" class="btn-hover w-full bg-gradient-to-r from-sky-500 to-sky-600 text-white py-3.5 rounded-xl font-bold text-[14px] shadow-lg shadow-sky-500/25">
                        <span class="btn-text flex items-center justify-center gap-2"><i class="fas fa-user-plus"></i> Daftar Sekarang</span>
                        <span class="btn-loading"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...</span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-[13px] text-gray-400">Sudah punya akun?
                        <a href="{{ route('login') }}" class="text-sky-600 font-semibold hover:text-sky-700 hover:underline transition">Masuk</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('registerForm').addEventListener('submit', function() {
        const btn = document.getElementById('registerBtn');
        btn.classList.add('is-loading');
        btn.innerHTML = '<span class="btn-loading flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...</span>';
    });
</script>
@endpush
@endsection
