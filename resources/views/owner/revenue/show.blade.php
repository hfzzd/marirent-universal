@extends('layouts.dashboard')
@section('title', 'Detail Pendapatan - MariRent')
@section('page-title', 'Detail Pendapatan - ' . $invoice->invoice_number)

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('owner.revenue.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>

    <div class="bg-white rounded-2xl shadow-sm p-8">
        <div class="flex items-start justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-navy-900">{{ $invoice->invoice_number }}</h2>
                <p class="text-sm text-navy-500 mt-1">Dibuat: {{ $invoice->created_at->format('d M Y H:i') }}</p>
                @if($invoice->category)
                <span class="inline-block mt-2 bg-sky-50 text-sky-600 px-3 py-1 rounded-lg text-[11px] font-semibold">{{ $invoice->category->name }}</span>
                @endif
            </div>
            <div class="text-right flex flex-col items-end gap-2">
                @php
                    $statusColors = ['unpaid' => 'bg-red-100 text-red-700', 'partial' => 'bg-yellow-100 text-yellow-700', 'paid' => 'bg-green-100 text-green-700', 'sent' => 'bg-blue-100 text-blue-700'];
                @endphp
                <span class="px-3 py-1.5 rounded-full text-sm font-medium {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-700' }} capitalize">{{ $invoice->status }}</span>
            </div>
        </div>

        {{-- Invoice Items --}}
        <div class="border rounded-xl overflow-hidden mb-6">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 border-b">
                    <th class="text-left py-3 px-4 text-navy-500 font-medium">Deskripsi</th>
                    <th class="text-center py-3 px-4 text-navy-500 font-medium">Qty</th>
                    <th class="text-right py-3 px-4 text-navy-500 font-medium">Harga</th>
                    <th class="text-right py-3 px-4 text-navy-500 font-medium">Total</th>
                </tr></thead>
                <tbody>
                    @forelse($invoice->items as $item)
                    <tr class="border-b last:border-0">
                        <td class="py-3 px-4">{{ $item->description }}</td>
                        <td class="py-3 px-4 text-center">{{ $item->quantity }}</td>
                        <td class="py-3 px-4 text-right">Rp {{ number_format($item->unit_price,0,',','.') }}</td>
                        <td class="py-3 px-4 text-right font-medium">Rp {{ number_format($item->total_price,0,',','.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-navy-400 text-sm">Tidak ada item</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- Summary --}}
        <div class="flex justify-end">
            <div class="w-full sm:w-72 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-navy-500">Subtotal</span><span>Rp {{ number_format($invoice->subtotal,0,',','.') }}</span></div>
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between"><span class="text-navy-500">Pajak</span><span>Rp {{ number_format($invoice->tax_amount,0,',','.') }}</span></div>
                @endif
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between"><span class="text-navy-500">Diskon</span><span class="text-red-600">- Rp {{ number_format($invoice->discount_amount,0,',','.') }}</span></div>
                @endif
                <div class="border-t pt-2 flex justify-between"><span class="font-bold text-navy-800">Total</span><span class="font-bold text-lg text-sky-600">Rp {{ number_format($invoice->total_amount,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Dibayar</span><span class="text-green-600 font-medium">Rp {{ number_format($invoice->paid_amount,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="font-bold text-navy-800">Sisa Tagihan</span><span class="font-bold {{ $invoice->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">Rp {{ number_format($invoice->due_amount,0,',','.') }}</span></div>
            </div>
        </div>

        {{-- Notes --}}
        @if($invoice->notes)
        <div class="bg-gray-50 p-4 rounded-xl mt-6">
            <p class="text-xs text-navy-500 mb-1">Catatan</p>
            <p class="text-sm text-navy-700">{{ $invoice->notes }}</p>
        </div>
        @endif

        {{-- Actions --}}
        @if($invoice->status === 'draft')
        <div class="border-t mt-6 pt-6 flex gap-2">
            <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="inline">
                @csrf
                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-xs font-semibold transition"><i class="fas fa-paper-plane mr-1"></i> Kirim</button>
            </form>
            <form method="POST" action="{{ route('owner.revenue.destroy', $invoice) }}" class="inline"
                  onsubmit="return confirm('Hapus pendapatan ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-500 px-4 py-2 rounded-lg text-xs font-semibold transition border border-red-100"><i class="fas fa-trash mr-1"></i> Hapus</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
