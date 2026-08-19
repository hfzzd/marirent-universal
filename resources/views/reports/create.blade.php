@extends('layouts.dashboard')
@section('page-title', 'Buat Laporan Perjalanan')
@section('content')
<div class="max-w-3xl">
    <a href="{{ route('reports.index') }}" class="text-primary-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('reports.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Booking (Perjalanan Aktif) *</label>
                    <select name="booking_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">Pilih Booking</option>
                        @foreach($bookings as $b)
                        <option value="{{ $b->id }}">{{ $b->booking_code }} - {{ $b->vehicle->name ?? ($b->category->name ?? '-') }} ({{ $b->user->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Odometer Awal (km)</label>
                        <input type="number" name="start_odometer" value="{{ old('start_odometer') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Odometer Akhir (km)</label>
                        <input type="number" name="end_odometer" value="{{ old('end_odometer') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <p class="text-sm font-medium text-navy-700">Biaya Operasional</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">BBM (Rp)</label>
                        <input type="number" name="fuel_cost" value="{{ old('fuel_cost', 0) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">Tol (Rp)</label>
                        <input type="number" name="toll_cost" value="{{ old('toll_cost', 0) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">Parkir (Rp)</label>
                        <input type="number" name="parking_cost" value="{{ old('parking_cost', 0) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">Lainnya (Rp)</label>
                        <input type="number" name="other_cost" value="{{ old('other_cost', 0) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Catatan Perjalanan</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">{{ old('notes') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Masalah yang Ditemukan (opsional)</label>
                    <textarea name="issues_reported" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">{{ old('issues_reported') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Simpan Laporan</button>
                <a href="{{ route('reports.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
