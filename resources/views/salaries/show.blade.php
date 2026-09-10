@extends('layouts.dashboard')
@section('page-title', 'Detail Gaji - ' . $salary->driver?->user?->name)
@section('content')
<div class="max-w-3xl">
    <a href="{{ route('salaries.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-navy-900">{{ $salary->driver?->user?->name }}</h2>
                <p class="text-sm text-navy-500">Periode: {{ $salary->period_month }}</p>
            </div>
            <span class="status-{{ $salary->status }} px-3 py-1.5 rounded-full text-sm font-medium capitalize">{{ $salary->status }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-navy-500">Gaji Pokok</span><span class="font-medium">Rp {{ number_format($salary->base_salary,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Bonus Perjalanan</span><span class="font-medium">Rp {{ number_format($salary->trip_bonus,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Lembur</span><span class="font-medium">Rp {{ number_format($salary->overtime_pay,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Potongan</span><span class="font-medium text-red-600">- Rp {{ number_format($salary->deductions,0,',','.') }}</span></div>
                <div class="border-t pt-3 flex justify-between"><span class="font-bold text-navy-800">Total Gaji</span><span class="font-bold text-xl text-sky-600">Rp {{ number_format($salary->total_salary,0,',','.') }}</span></div>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-navy-500">Owner</span><span class="font-medium">{{ $salary->owner?->name }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Asal Company</span><span class="font-medium">{{ $salary->driver?->company?->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Status</span><span class="font-medium capitalize">{{ $salary->status }}</span></div>
                @if($salary->invoice)
                <div class="flex justify-between"><span class="text-navy-500">Invoice</span><a href="{{ route('invoices.show', $salary->invoice) }}" class="font-medium text-sky-600">{{ $salary->invoice->invoice_number }}</a></div>
                @endif
                @if($salary->notes)
                <div class="bg-gray-50 p-3 rounded-xl mt-3"><p class="text-xs text-navy-500 mb-1">Catatan</p><p class="text-navy-700">{{ $salary->notes }}</p></div>
                @endif
            </div>
        </div>
        @if(in_array(auth()->user()->role, ['superadmin', 'admin', 'owner']))
        <div class="flex gap-3 border-t pt-6">
            @if($salary->status == 'draft')
            <form method="POST" action="{{ route('salaries.approve', $salary) }}">@csrf
                <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold"><i class="fas fa-check mr-1"></i> Setujui & Buat Invoice</button>
            </form>
            @endif
            @if($salary->status == 'approved')
            <form method="POST" action="{{ route('salaries.pay', $salary) }}">@csrf
                <button class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold"><i class="fas fa-money-check-alt mr-1"></i> Tandai Sudah Dibayar</button>
            </form>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
