@extends(auth()->user()->role === 'user' ? 'layouts.user' : 'layouts.dashboard')
@section('title', 'Profil Saya - MariRent')
@if(auth()->user()->role === 'user')
@section('page-title', 'Profil Saya')
@endif

@section('content')
@php $user = auth()->user(); $isUser = $user->role === 'user'; @endphp

@if($isUser)
{{-- USER PROFILE - Clean modern style --}}
<div class="max-w-lg mx-auto">
    {{-- Header Card --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-5" x-data="{ preview: '{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}' }">
        <div class="h-28 bg-gradient-to-r from-sky-500 via-sky-400 to-sky-600 relative overflow-hidden">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white rounded-full -translate-y-8 translate-x-8"></div>
                <div class="absolute bottom-0 left-0 w-20 h-20 bg-white rounded-full translate-y-6 -translate-x-4"></div>
            </div>
        </div>
        <div class="px-6 pb-6 -mt-12 relative z-10">
            <div class="flex items-end gap-4 mb-5">
                <div class="relative">
                    <div class="w-24 h-24 rounded-2xl bg-white shadow-xl shadow-sky-500/10 flex items-center justify-center overflow-hidden border-4 border-white">
                        <template x-if="preview">
                            <img :src="preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <div class="w-full h-full bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center">
                                <i class="fas fa-user text-white text-3xl"></i>
                            </div>
                        </template>
                    </div>
                    <label for="avatar" class="absolute -bottom-1 -right-1 w-8 h-8 bg-sky-500 rounded-xl flex items-center justify-center cursor-pointer hover:bg-sky-600 transition shadow-lg shadow-sky-500/25">
                        <i class="fas fa-camera text-white text-[11px]"></i>
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden"
                               x-on:change="if(event.target.files[0]) { const reader = new FileReader(); reader.onload = (e) => preview = e.target.result; reader.readAsDataURL(event.target.files[0]); }">
                    </label>
                </div>
                <div class="pb-1">
                    <h2 class="text-lg font-bold text-navy-900">{{ $user->name }}</h2>
                    <p class="text-[12px] text-gray-400">{{ $user->email }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('dashboard.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-[14px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <div class="w-8 h-8 bg-sky-50 rounded-xl flex items-center justify-center"><i class="fas fa-user-edit text-sky-500 text-[11px]"></i></div>
                Informasi Pribadi
            </h3>
            <div class="space-y-3.5">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 hover:border-gray-300">
                    @error('name') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 hover:border-gray-300">
                    @error('email') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 hover:border-gray-300">
                    @error('phone') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Alamat</label>
                    <textarea name="address" rows="3" placeholder="Alamat lengkap Anda"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 hover:border-gray-300 resize-none">{{ old('address', $user->address) }}</textarea>
                    @error('address') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary text-white w-full py-3.5 rounded-2xl text-[14px] font-bold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:shadow-xl hover:shadow-sky-500/30 flex items-center justify-center gap-2">
            <i class="fas fa-save text-[12px]"></i> Simpan Perubahan
        </button>
    </form>

    {{-- Password --}}
    <form action="{{ route('dashboard.password.update') }}" method="POST" class="mt-5">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-[14px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-50 rounded-xl flex items-center justify-center"><i class="fas fa-lock text-amber-500 text-[11px]"></i></div>
                Ganti Password
            </h3>
            <div class="space-y-3.5">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Password Lama</label>
                    <input type="password" name="current_password" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 hover:border-gray-300">
                    @error('current_password') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Password Baru</label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 hover:border-gray-300">
                        @error('password') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Konfirmasi</label>
                        <input type="password" name="password_confirmation" required minlength="8"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition-all duration-200 hover:border-gray-300">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="mt-4 w-full py-3 rounded-2xl text-[13px] font-bold text-sky-600 bg-sky-50 hover:bg-sky-100 border border-sky-200 transition-all duration-200 flex items-center justify-center gap-2">
            <i class="fas fa-key text-[11px]"></i> Perbarui Password
        </button>
    </form>
</div>

@else
{{-- ADMIN/OTHER PROFILE - Keep original dashboard style --}}
<div class="max-w-2xl mx-auto">
    <div class="glass-card rounded-2xl overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-sky-100/50">
            <h3 class="text-[14px] font-semibold text-navy-800">
                <i class="fas fa-user-edit text-sky-500 mr-2"></i>Informasi Profil
            </h3>
            <p class="text-[11px] text-gray-400 mt-0.5">Perbarui informasi akun Anda</p>
        </div>

        <form action="{{ route('dashboard.profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-5" x-data="{ preview: '{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}' }">
                <div class="relative">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center overflow-hidden shadow-lg shadow-sky-500/20 border-4 border-white">
                        <template x-if="preview">
                            <img :src="preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <i class="fas fa-user text-white text-2xl"></i>
                        </template>
                    </div>
                    <label for="avatar" class="absolute -bottom-1 -right-1 w-7 h-7 bg-sky-500 rounded-full flex items-center justify-center cursor-pointer hover:bg-sky-600 transition shadow-md">
                        <i class="fas fa-camera text-white text-[10px]"></i>
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden"
                               x-on:change="if(event.target.files[0]) { const reader = new FileReader(); reader.onload = (e) => preview = e.target.result; reader.readAsDataURL(event.target.files[0]); }">
                    </label>
                </div>
                <div>
                    <p class="text-[13px] font-semibold text-navy-700">{{ $user->name }}</p>
                    <p class="text-[11px] text-gray-400">Klik ikon kamera untuk mengubah foto</p>
                    @error('avatar') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <hr class="border-gray-100">

            <div>
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition">
                @error('name') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition">
                @error('email') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Nomor Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition">
                @error('phone') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Alamat</label>
                <textarea name="address" rows="3" placeholder="Alamat lengkap Anda"
                          class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition resize-none">{{ old('address', $user->address) }}</textarea>
                @error('address') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-[13px] font-semibold shadow-lg shadow-sky-500/25 hover:shadow-sky-500/40 transition-all duration-300 inline-flex items-center gap-2">
                    <i class="fas fa-save text-xs"></i> Simpan Perubahan
                </button>
                <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-xl text-[13px] font-semibold text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600 transition-all duration-300">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-sky-100/50">
            <h3 class="text-[14px] font-semibold text-navy-800">
                <i class="fas fa-lock text-sky-500 mr-2"></i>Ganti Password
            </h3>
            <p class="text-[11px] text-gray-400 mt-0.5">Pastikan Anda menggunakan password yang kuat</p>
        </div>

        <form action="{{ route('dashboard.password.update') }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Password Lama</label>
                <input type="password" name="current_password" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition">
                @error('current_password') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Password Baru</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition">
                    @error('password') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] text-navy-700 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 outline-none transition">
                </div>
            </div>

            <div>
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-[13px] font-semibold shadow-lg shadow-sky-500/25 hover:shadow-sky-500/40 transition-all duration-300 inline-flex items-center gap-2">
                    <i class="fas fa-key text-xs"></i> Perbarui Password
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
