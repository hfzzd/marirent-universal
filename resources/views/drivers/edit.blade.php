@extends('layouts.dashboard')
@section('page-title', 'Edit ' . $driver->user?->name)
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('drivers.show', $driver) }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('drivers.update', $driver) }}">
            @csrf @method('PUT')
            <div class="space-y-4">
                @if($isSuperadmin)
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Asal Company *</label>
                    <select name="company_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">-- Pilih company --</option>
                        @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ old('company_id', $driver->company_id) == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->city ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Asal Company</label>
                    <input type="text" value="{{ $driver->company?->name ?? 'Tanpa company' }}" disabled class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-sm">
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" value="{{ old('name', $driver->user?->name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Posisi / Jabatan</label>
                        <input type="text" name="position" value="{{ old('position', $driver->position) }}" placeholder="Driver / Karyawan / Mekanik / Admin" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Email</label>
                        <input type="text" value="{{ $driver->user?->email }}" disabled class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">No. HP</label>
                        <input type="text" name="phone" value="{{ old('phone', $driver->user?->phone) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Password Baru (kosongkan jika tetap)</label>
                        <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Alamat</label>
                    <input type="text" name="address" value="{{ old('address', $driver->user?->address) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <p class="text-[12px] font-semibold text-navy-700 mb-3 uppercase tracking-wide">Data SIM (kosongkan jika bukan driver)</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-navy-700 mb-1">Nomor SIM</label>
                            <input type="text" name="license_number" value="{{ old('license_number', $driver->license_number) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                            @error('license_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-navy-700 mb-1">Tipe SIM</label>
                            <select name="license_type" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                                <option value="">-- Pilih --</option>
                                @foreach(['A','B1','B2','C'] as $t)
                                <option value="{{ $t }}" {{ old('license_type', $driver->license_type) == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-navy-700 mb-1">Masa Berlaku SIM</label>
                            <input type="date" name="license_expiry" value="{{ old('license_expiry', $driver->license_expiry?->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Gaji per Hari (Rp) *</label>
                        <input type="number" name="daily_salary" required value="{{ old('daily_salary', $driver->daily_salary) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        @error('daily_salary') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Bonus per Trip (Rp)</label>
                        <input type="number" name="trip_salary" value="{{ old('trip_salary', $driver->trip_salary) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                            @foreach(['off_duty','on_duty','on_trip','active','inactive'] as $s)
                            <option value="{{ $s }}" {{ old('status', $driver->status) == $s ? 'selected' : '' }} class="capitalize">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 pb-2.5">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $driver->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 focus:ring-sky-500">
                            <span class="text-sm font-medium text-navy-700">Akun Aktif</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">{{ old('notes', $driver->notes) }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Update</button>
                <a href="{{ route('drivers.show', $driver) }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection