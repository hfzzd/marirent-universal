@extends('layouts.dashboard')
@section('page-title', 'Tambah Driver')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('drivers.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('drivers.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Akun Pengguna *</label>
                    <select name="user_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih User</option>
                        @foreach(\App\Models\User::where('role', 'user')->get() as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Nomor SIM</label>
                        <input type="text" name="license_number" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Tipe SIM</label>
                        <select name="license_type" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                            <option value="A">A</option><option value="B1">B1</option><option value="B2">B2</option><option value="C">C</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Masa Berlaku SIM</label>
                    <input type="date" name="license_expiry" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Gaji per Hari (Rp) *</label>
                        <input type="number" name="daily_salary" required value="150000" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Bonus per Trip (Rp)</label>
                        <input type="number" name="trip_salary" value="50000" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500"></textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Simpan</button>
                <a href="{{ route('drivers.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
