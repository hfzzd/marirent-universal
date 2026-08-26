@extends('layouts.dashboard')
@section('page-title', 'Edit Driver - ' . $driver->user?->name)
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('drivers.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('drivers.update', $driver) }}">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Nomor SIM</label>
                        <input type="text" name="license_number" value="{{ old('license_number', $driver->license_number) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Tipe SIM</label>
                        <select name="license_type" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                            @foreach(['A','B1','B2','C'] as $t)
                            <option value="{{ $t }}" {{ old('license_type', $driver->license_type) == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Masa Berlaku SIM</label>
                    <input type="date" name="license_expiry" value="{{ old('license_expiry', $driver->license_expiry?->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Gaji per Hari (Rp)</label>
                        <input type="number" name="daily_salary" value="{{ old('daily_salary', $driver->daily_salary) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Bonus per Trip (Rp)</label>
                        <input type="number" name="trip_salary" value="{{ old('trip_salary', $driver->trip_salary) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        @foreach(['active','inactive','on_trip','off_duty'] as $s)
                        <option value="{{ $s }}" {{ old('status', $driver->status) == $s ? 'selected' : '' }} class="capitalize">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">{{ old('notes', $driver->notes) }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Update</button>
                <a href="{{ route('drivers.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
