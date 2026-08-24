@extends(auth()->user()->role === 'user' ? 'layouts.user' : 'layouts.dashboard')
@section('title', 'Detail Invoice - MariRent')
@section('page-title', 'Detail Invoice - ' . $invoice->invoice_number)
@section('content')
<div class="max-w-4xl">
    <a href="{{ route('invoices.index') }}" class="text-primary-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-8">
        <div class="flex items-start justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-navy-900">{{ $invoice->invoice_number }}</h2>
                <p class="text-sm text-navy-500 mt-1">Dibuat: {{ $invoice->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="text-right">
                <span class="status-{{ $invoice->status }} px-3 py-1.5 rounded-full text-sm font-medium capitalize">{{ $invoice->status }}</span>
                @if($invoice->isOverdue())
                <p class="text-xs text-red-600 mt-1 font-medium"><i class="fas fa-exclamation-circle mr-1"></i> Jatuh Tempo</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <p class="text-xs text-navy-500 mb-1">Ditagihkan Kepada</p>
                <p class="font-bold text-navy-800">{{ $invoice->user->name }}</p>
                <p class="text-sm text-navy-500">{{ $invoice->user->email }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-navy-500 mb-1">Jatuh Tempo</p>
                <p class="font-bold text-navy-800">{{ $invoice->due_date->format('d M Y') }}</p>
                @php $invBookings = $invoice->bookings->isNotEmpty() ? $invoice->bookings : collect([$invoice->booking])->filter(); @endphp
                @if($invBookings->isNotEmpty())
                <div class="flex flex-wrap justify-end gap-1.5 mt-2">
                    @foreach($invBookings as $b)
                    <span class="inline-flex items-center bg-sky-50 text-sky-600 border border-sky-200 rounded-lg px-2 py-0.5 text-[11px] font-semibold font-mono">{{ $b->booking_code }}</span>
                    @endforeach
                </div>
                @if($invBookings->count() > 1)
                <p class="text-[11px] font-bold text-violet-500 mt-1.5"><i class="fas fa-layer-group mr-1"></i>Invoice Gabungan {{ $invBookings->count() }} Sewa</p>
                @endif
                @endif
            </div>
        </div>

        <div class="border rounded-xl overflow-hidden mb-6">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 border-b">
                    <th class="text-left py-3 px-4 text-navy-500 font-medium">Deskripsi</th>
                    <th class="text-center py-3 px-4 text-navy-500 font-medium">Qty</th>
                    <th class="text-right py-3 px-4 text-navy-500 font-medium">Harga</th>
                    <th class="text-right py-3 px-4 text-navy-500 font-medium">Total</th>
                </tr></thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr class="border-b">
                        <td class="py-3 px-4">{{ $item->description }}</td>
                        <td class="py-3 px-4 text-center">{{ $item->quantity }}</td>
                        <td class="py-3 px-4 text-right">Rp {{ number_format($item->unit_price,0,',','.') }}</td>
                        <td class="py-3 px-4 text-right font-medium">Rp {{ number_format($item->total_price,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <div class="w-72 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-navy-500">Subtotal</span><span>Rp {{ number_format($invoice->subtotal,0,',','.') }}</span></div>
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between"><span class="text-navy-500">Pajak (11%)</span><span>Rp {{ number_format($invoice->tax_amount,0,',','.') }}</span></div>
                @endif
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between"><span class="text-navy-500">Diskon</span><span class="text-red-600">- Rp {{ number_format($invoice->discount_amount,0,',','.') }}</span></div>
                @endif
                <div class="border-t pt-2 flex justify-between"><span class="font-bold text-navy-800">Total</span><span class="font-bold text-lg text-primary-600">Rp {{ number_format($invoice->total_amount,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Dibayar</span><span class="text-green-600 font-medium">Rp {{ number_format($invoice->paid_amount,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="font-bold text-navy-800">Sisa Tagihan</span><span class="font-bold {{ $invoice->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">Rp {{ number_format($invoice->due_amount,0,',','.') }}</span></div>
            </div>
        </div>

        @if($invoice->status !== 'paid' && $invoice->due_amount > 0)
        <div class="border-t mt-8 pt-6">
            <h4 class="font-bold text-navy-800 mb-1">Bayar Tagihan</h4>
            <p class="text-xs text-navy-500 mb-4">Pilih cepat: lunasi sekaligus atau bayar DP 50% (sisa dibayar saat serah terima). Non-tunai diverifikasi admin.</p>
            <div class="flex flex-wrap gap-2 mb-4">
                <button type="button" onclick="setAmount({{ (float) $invoice->due_amount }}, this)"
                    class="px-4 py-2 rounded-xl text-[12px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                    <i class="fas fa-money-bill-wave mr-1"></i> Bayar Penuh &bull; Rp {{ number_format($invoice->due_amount, 0, ',', '.') }}
                </button>
                @php $dpAmount = round((float) $invoice->total_amount * 0.5); @endphp
                @if($dpAmount > 0 && $dpAmount < (float) $invoice->due_amount)
                <button type="button" onclick="setAmount({{ min($dpAmount, (float) $invoice->due_amount) }}, this)"
                    class="px-4 py-2 rounded-xl text-[12px] font-bold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition">
                    <i class="fas fa-hand-holding-dollar mr-1"></i> DP 50% &bull; Rp {{ number_format(min($dpAmount, (float) $invoice->due_amount), 0, ',', '.') }}
                </button>
                @endif
            </div>
            <form method="POST" action="{{ route('invoices.pay', $invoice) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">Jumlah (Rp)</label>
                        <input type="number" id="pay-amount" name="amount" value="{{ $invoice->due_amount }}" min="1" max="{{ $invoice->due_amount }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">Metode</label>
                        <select name="method" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="transfer">Transfer Bank</option><option value="ewallet">E-Wallet</option><option value="cash">Cash di Kantor</option><option value="credit_card">Kartu Kredit</option><option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">Referensi</label>
                        <input type="text" name="reference_number" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="No. rekening/pengirim">
                    </div>
                </div>
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold"><i class="fas fa-money-check-alt mr-1"></i> Kirim Pembayaran</button>
            </form>
        </div>
        @endif

        @if($invoice->payments->count())
        <div class="border-t mt-6 pt-6">
            <h4 class="font-bold text-navy-800 mb-3">Riwayat Pembayaran</h4>
            @foreach($invoice->payments as $p)
            <div class="flex items-center justify-between gap-3 py-2.5 border-b last:border-0 text-sm flex-wrap">
                <div>
                    <span class="font-medium text-navy-800">{{ $p->payment_code }}</span>
                    <span class="text-navy-500 ml-2">{{ ucfirst(str_replace('_', ' ', $p->method)) }}</span>
                    <span class="text-navy-400 ml-2">{{ $p->paid_at?->format('d M Y H:i') ?? '-' }}</span>
                    @if($p->reference_number) <span class="text-navy-400 ml-2">&bull; Ref: {{ $p->reference_number }}</span> @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="status-{{ $p->status }} px-2 py-0.5 rounded-full text-xs font-medium">{{ match($p->status) { 'pending' => 'Menunggu Verifikasi', 'verified' => 'Terverifikasi', default => ucfirst($p->status) } }}</span>
                    @if(in_array(auth()->user()->role, ['superadmin', 'owner']) && $p->status !== 'rejected')
                    <form method="POST" action="{{ route('invoices.payments.verify', [$invoice, $p]) }}" class="inline">
                        @csrf
                        <button type="submit" {{ $p->status === 'verified' ? 'disabled' : '' }} class="px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $p->status === 'verified' ? 'bg-gray-100 text-gray-300 cursor-not-allowed' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                            <i class="fas fa-check mr-0.5"></i> Verifikasi
                        </button>
                    </form>
                    @if($p->status !== 'verified')
                    <form method="POST" action="{{ route('invoices.payments.reject', [$invoice, $p]) }}" class="inline"
                        onsubmit="return confirm('Tolak pembayaran ini?')">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-red-50 text-red-600 hover:bg-red-100">
                            <i class="fas fa-times mr-0.5"></i> Tolak
                        </button>
                    </form>
                    @endif
                    @endif
                    <span class="font-medium ml-1">Rp {{ number_format($p->amount,0,',','.') }}</span>
                </div>
            </div>
            @endforeach
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
function setAmount(val, btn) {
    const input = document.getElementById('pay-amount');
    if (input) input.value = val;
    document.querySelectorAll('button[onclick^="setAmount"]').forEach(b => b.classList.remove('ring-2', 'ring-offset-1'));
    if (btn) { btn.classList.add('ring-2', 'ring-offset-1'); }
}
</script>
@endsection
