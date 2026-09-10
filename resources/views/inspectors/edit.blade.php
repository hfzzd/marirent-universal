@extends('layouts.dashboard')
@section('page-title', 'Edit Akun Inspektur')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('inspectors.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('inspectors.update', $inspector) }}">
            @csrf @method('PUT')
            <div class="space-y-4">
                @if($isSuperadmin)
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Asal Company *</label>
                    <select name="company_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">-- Pilih company --</option>
                        @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ $inspector->merchant?->company?->id == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->city ?? '-' }})</option>
                        @endforeach
                    </select>
                    @error('company_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name', $inspector->name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Email (tidak bisa diubah)</label>
                        <input type="email" value="{{ $inspector->email }}" disabled class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-gray-50 text-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">No. HP</label>
                        <input type="text" name="phone" value="{{ old('phone', $inspector->phone) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Password Baru</label>
                        <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" placeholder="Kosongkan jika tidak diubah">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Alamat</label>
                    <input type="text" name="address" value="{{ old('address', $inspector->address) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ $inspector->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-sky-500 focus:ring-sky-500">
                    <label for="is_active" class="text-sm text-navy-700">Akun aktif</label>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Simpan Perubahan</button>
                <a href="{{ route('inspectors.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection