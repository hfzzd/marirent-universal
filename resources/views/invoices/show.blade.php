@extends(auth()->user()->role === 'user' ? 'layouts.user' : 'layouts.dashboard')
@section('title', 'Detail Invoice - MariRent')
@section('page-title', 'Detail Invoice - ' . $invoice->invoice_number)
@section('content')
<div class="max-w-4xl">
    <a href="{{ route('invoices.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-8">
        <div class="flex items-start justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-navy-900">{{ $invoice->invoice_number }}</h2>
                <p class="text-sm text-navy-500 mt-1">Dibuat: {{ $invoice->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="text-right flex flex-col items-end gap-2">
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded-lg text-xs font-semibold transition shadow-sm">
                    <i class="fas fa-print mr-1"></i> Cetak Invoice
                </a>
                @php
                    $statusColors = ['sent' => 'bg-amber-100 text-amber-700', 'partial' => 'bg-yellow-100 text-yellow-700', 'paid' => 'bg-green-100 text-green-700', 'overdue' => 'bg-red-100 text-red-700'];
                @endphp
                <span class="px-3 py-1.5 rounded-full text-sm font-medium {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-700' }} capitalize">{{ $invoice->status }}</span>
                @if($invoice->isOverdue())
                <p class="text-xs text-red-600 mt-1 font-medium"><i class="fas fa-exclamation-circle mr-1"></i> Jatuh Tempo</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <p class="text-xs text-navy-500 mb-1">Ditagihkan Kepada</p>
                <p class="font-bold text-navy-800">{{ $invoice->user?->name }}</p>
                <p class="text-sm text-navy-500">{{ $invoice->user?->email }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-navy-500 mb-1">Jatuh Tempo</p>
                <p class="font-bold text-navy-800">{{ $invoice->due_date->format('d M Y') }}</p>
                @if($invoice->booking)
                <p class="text-sm text-navy-500 mt-1">Booking: <a href="{{ route('bookings.show', $invoice->booking) }}" class="text-sky-600 hover:underline">{{ $invoice->booking->booking_code }}</a></p>
                @endif
            </div>
        </div>

        {{-- Invoice Items / Line Items --}}
        <div class="border rounded-xl overflow-hidden mb-6">
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
                        <td colspan="4" class="py-6 text-center text-navy-400 text-sm">Tidak ada item invoice</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Summary --}}
        <div class="flex justify-end">
            <div class="w-72 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-navy-500">Subtotal</span><span>Rp {{ number_format($invoice->subtotal,0,',','.') }}</span></div>
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between"><span class="text-navy-500">Pajak (11%)</span><span>Rp {{ number_format($invoice->tax_amount,0,',','.') }}</span></div>
                @endif
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between"><span class="text-navy-500">Diskon</span><span class="text-red-600">- Rp {{ number_format($invoice->discount_amount,0,',','.') }}</span></div>
                @endif
                <div class="border-t pt-2 flex justify-between"><span class="font-bold text-navy-800">Total</span><span class="font-bold text-lg text-sky-600">Rp {{ number_format($invoice->total_amount,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Dibayar</span><span class="text-green-600 font-medium">Rp {{ number_format($invoice->paid_amount,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="font-bold text-navy-800">Sisa Tagihan</span><span class="font-bold {{ $invoice->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">Rp {{ number_format($invoice->due_amount,0,',','.') }}</span></div>
            </div>
        </div>

        {{-- Payment Form --}}
        @if($invoice->status !== 'paid' && $invoice->due_amount > 0)
        <div class="border-t mt-8 pt-6">
            <h4 class="font-bold text-navy-800 mb-4"><i class="fas fa-money-check-alt mr-2 text-sky-500"></i>Kirim Pembayaran</h4>
            <form method="POST" action="{{ route('invoices.pay', $invoice) }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">Jumlah (Rp) *</label>
                        <input type="number" name="amount" value="{{ $invoice->due_amount }}" min="1" max="{{ $invoice->due_amount }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">Metode *</label>
                        <select name="method" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                            <option value="transfer">Transfer Bank</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Kartu Kredit</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">No. Referensi</label>
                        <input type="text" name="reference_number" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" placeholder="No. Ref / Bukti Transfer">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">Foto Bukti Pembayaran</label>
                        <input type="file" name="proof_photo" accept="image/*" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-sky-100 file:text-sky-700 hover:file:bg-sky-200">
                        <p class="text-[11px] text-navy-400 mt-1">Upload bukti transfer/screenshot pembayaran</p>
                    </div>
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">Catatan</label>
                        <input type="text" name="notes" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" placeholder="Catatan pembayaran (opsional)">
                    </div>
                </div>
                <button type="submit" class="bg-gradient-to-r from-sky-500 to-sky-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:from-sky-600 hover:to-sky-800 transition"><i class="fas fa-paper-plane mr-2"></i>Kirim Bukti Pembayaran</button>
            </form>
        </div>
        @endif

        {{-- Payment History --}}
        @if($invoice->payments->count())
        <div class="border-t mt-6 pt-6" x-data="initRejectModals()">
            <h4 class="font-bold text-navy-800 mb-4"><i class="fas fa-history mr-2 text-sky-500"></i>Riwayat Pembayaran</h4>
            <div class="space-y-3">
                @foreach($invoice->payments as $p)
                <div class="border border-gray-200 rounded-xl p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-sm text-navy-800">{{ $p->payment_code }}</span>
                                @php
                                    $pColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'verified' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $pColors[$p->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    @if($p->status === 'pending') Menunggu Verifikasi
                                    @elseif($p->status === 'verified') Terverifikasi
                                    @else Ditolak @endif
                                </span>
                            </div>
                            <div class="text-xs text-navy-500 mt-1">
                                {{ ucfirst($p->method) }} &bull; {{ $p->paid_at->format('d M Y H:i') }}
                                @if($p->reference_number) &bull; Ref: {{ $p->reference_number }} @endif
                            </div>
                            @if($p->notes)
                            <div class="text-xs text-navy-400 mt-1 italic">{{ $p->notes }}</div>
                            @endif
                            @if($p->rejection_reason)
                            <div class="text-xs text-red-500 mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>Alasan penolakan: {{ $p->rejection_reason }}</div>
                            @endif
                        </div>
                        <div class="text-right ml-4">
                            <div class="font-bold text-sm text-navy-800">Rp {{ number_format($p->amount,0,',','.') }}</div>
                        </div>
                    </div>

                    {{-- Proof Photo --}}
                    @if($p->proof_photo)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-navy-500 mb-1"><i class="fas fa-image mr-1"></i>Bukti Pembayaran:</p>
                        <a href="{{ asset('storage/' . $p->proof_photo) }}" target="_blank" class="inline-block">
                            <img src="{{ asset('storage/' . $p->proof_photo) }}" alt="Bukti" class="h-20 rounded-lg border border-gray-200 hover:border-sky-400 transition">
                        </a>
                    </div>
                    @endif

                    {{-- Admin Verify/Reject Buttons --}}
                    @if($p->status === 'pending' && in_array(auth()->user()->role, ['superadmin', 'owner']))
                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
                        <form method="POST" action="{{ route('invoices.verify-payment', $p) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition"><i class="fas fa-check mr-1"></i>Verifikasi</button>
                        </form>
                        <button type="button" @click="showRejectModal{{ $p->id }} = true" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition"><i class="fas fa-times mr-1"></i>Tolak</button>

                        {{-- Reject Modal --}}
                        <div x-show="showRejectModal{{ $p->id }}" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                            <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4" @click.away="showRejectModal{{ $p->id }} = false">
                                <h3 class="font-bold text-navy-800 mb-3">Tolak Pembayaran</h3>
                                <form method="POST" action="{{ route('invoices.reject-payment', $p) }}">
                                    @csrf
                                    <textarea name="rejection_reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 mb-3" placeholder="Alasan penolakan..."></textarea>
                                    <div class="flex gap-2">
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Tolak</button>
                                        <button type="button" @click="showRejectModal{{ $p->id }} = false" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm font-medium text-navy-700">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($invoice->notes)
        <div class="bg-gray-50 p-4 rounded-xl mt-6">
            <p class="text-xs text-navy-500 mb-1">Catatan</p>
            <p class="text-sm text-navy-700">{{ $invoice->notes }}</p>
        </div>
        @endif
    </div>
</div>

<script>
function initRejectModals() {
    return {
        @foreach($invoice->payments as $p)
        showRejectModal{{ $p->id }}: false,
        @endforeach
    }
}
</script>
@endsection