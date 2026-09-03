@extends('layouts.dashboard')
@section('page-title', 'Input Gaji Driver')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('salaries.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('salaries.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Driver *</label>
                    <select name="driver_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Driver</option>
                        @foreach($drivers as $d)
                        <option value="{{ $d->id }}">{{ $d->user?->name }} (Gaji/Hari: Rp {{ number_format($d->daily_salary,0,',','.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Periode (Bulan) *</label>
                    <input type="month" name="period_month" value="{{ date('Y-m') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Gaji Pokok (Rp)</label>
                        <input type="number" name="base_salary" value="0" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Bonus Perjalanan (Rp)</label>
                        <input type="number" name="trip_bonus" value="0" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Lembur (Rp)</label>
                        <input type="number" name="overtime_pay" value="0" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Potongan (Rp)</label>
                        <input type="number" name="deductions" value="0" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500"></textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Simpan</button>
                <a href="{{ route('salaries.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
