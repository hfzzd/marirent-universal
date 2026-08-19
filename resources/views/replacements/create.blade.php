@extends('layouts.dashboard')
@section('page-title', 'Ajukan Penggantian Kendaraan')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('replacements.index') }}" class="text-primary-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('replacements.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Booking *</label>
                    <select name="booking_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">Pilih Booking</option>
                        @foreach($bookings as $b)
                        <option value="{{ $b->id }}">{{ $b->booking_code }} - {{ $b->vehicle->name ?? ($b->category->name ?? '-') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Kendaraan Pengganti *</label>
                    <select name="replacement_vehicle_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">Pilih Kendaraan</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->category->name }}) - Rp {{ number_format($v->daily_price,0,',','.') }}/hari</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Alasan Penggantian *</label>
                    <textarea name="reason" rows="4" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="Jelaskan alasan penggantian kendaraan...">{{ old('reason') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Ajukan</button>
                <a href="{{ route('replacements.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
