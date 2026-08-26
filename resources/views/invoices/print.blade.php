<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} - MariRent</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-area { padding: 0; margin: 0; }
        }
        @page { size: A4; margin: 15mm; }
    </style>
</head>
<body class="bg-white text-gray-800">
    {{-- Print Button --}}
    <div class="no-print fixed top-4 right-4 z-50 flex gap-2">
        <button onclick="window.print()" class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-lg">
            <i class="fas fa-print mr-2"></i>Cetak Invoice
        </button>
        <a href="{{ route('invoices.show', $invoice) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold shadow-lg">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <div class="print-area max-w-3xl mx-auto p-8">
        {{-- Header --}}
        <div class="flex items-start justify-between mb-8 border-b-2 border-sky-500 pb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-car-side text-white text-sm"></i>
                    </div>
                    <div>
                        <span class="text-xl font-extrabold text-navy-900">Mari<span class="text-sky-600">Rent</span></span>
                        <p class="text-[10px] text-gray-400 tracking-wider">UNIVERSAL RENTAL</p>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Jl. Raya Utama No. 1, Jakarta Selatan</p>
                <p class="text-xs text-gray-400">Telp: (021) 1234-5678 | info@mariarental.com</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-navy-900">INVOICE</h2>
                <p class="text-sm font-semibold text-sky-600 mt-1">{{ $invoice->invoice_number }}</p>
                <p class="text-xs text-gray-400 mt-1">Tanggal: {{ $invoice->created_at->format('d/m/Y') }}</p>
                <p class="text-xs text-gray-400">Jatuh Tempo: {{ $invoice->due_date->format('d/m/Y') }}</p>
                @php
                    $statusLabel = match($invoice->status) {
                        'paid' => 'LUNAS',
                        'partial' => 'DP TERBAYAR',
                        'unpaid' => 'BELUM BAYAR',
                        'overdue' => 'JATUH TEMPO',
                        default => strtoupper($invoice->status),
                    };
                    $statusColor = match($invoice->status) {
                        'paid' => 'text-green-600',
                        'partial' => 'text-yellow-600',
                        default => 'text-red-600',
                    };
                @endphp
                <p class="text-sm font-bold {{ $statusColor }} mt-2">{{ $statusLabel }}</p>
            </div>
        </div>

        {{-- Bill To & Booking Info --}}
        <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Ditagihkan Kepada</p>
                <p class="font-bold text-navy-800">{{ $invoice->user?->name }}</p>
                <p class="text-sm text-gray-500">{{ $invoice->user?->email }}</p>
                @if($invoice->user?->phone)
                <p class="text-sm text-gray-500">{{ $invoice->user->phone }}</p>
                @endif
                @if($invoice->user?->address)
                <p class="text-sm text-gray-500">{{ $invoice->user->address }}</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Detail Booking</p>
                @if($invoice->booking)
                <p class="text-sm"><span class="font-semibold text-navy-700">Kode Booking:</span> {{ $invoice->booking->booking_code }}</p>
                <p class="text-sm"><span class="font-semibold text-navy-700">Unit:</span> {{ $invoice->booking->vehicle?->name ?? $invoice->booking->category?->name ?? '-' }}</p>
                <p class="text-sm"><span class="font-semibold text-navy-700">Periode:</span> {{ $invoice->booking->start_date?->format('d/m/Y') }} - {{ $invoice->booking->end_date?->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>

        {{-- Line Items --}}
        <table class="w-full text-sm mb-6">
            <thead>
                <tr class="border-b-2 border-gray-200">
                    <th class="text-left py-3 text-navy-700 font-semibold">Deskripsi</th>
                    <th class="text-center py-3 text-navy-700 font-semibold w-16">Qty</th>
                    <th class="text-right py-3 text-navy-700 font-semibold w-32">Harga Satuan</th>
                    <th class="text-right py-3 text-navy-700 font-semibold w-32">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                <tr class="border-b border-gray-100">
                    <td class="py-3 text-gray-700">{{ $item->description }}</td>
                    <td class="py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                    <td class="py-3 text-right text-gray-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="py-3 text-right font-semibold text-navy-800">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400 text-sm">Tidak ada item invoice</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Summary --}}
        <div class="flex justify-end mb-8">
            <div class="w-72 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="text-gray-700">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between">
                    <span class="text-gray-500">Pajak (11%)</span>
                    <span class="text-gray-700">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between">
                    <span class="text-gray-500">Diskon</span>
                    <span class="text-red-600">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="border-t-2 border-gray-200 pt-2 flex justify-between">
                    <span class="font-bold text-navy-800">Total</span>
                    <span class="font-bold text-lg text-sky-600">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Sudah Dibayar</span>
                    <span class="text-green-600 font-medium">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-bold text-navy-800">Sisa Tagihan</span>
                    <span class="font-bold {{ $invoice->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">Rp {{ number_format($invoice->due_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Payment History --}}
        @if($invoice->payments->where('status', 'verified')->count())
        <div class="mb-8">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Riwayat Pembayaran</p>
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 text-gray-600">Tanggal</th>
                        <th class="text-left py-2 text-gray-600">Kode</th>
                        <th class="text-left py-2 text-gray-600">Metode</th>
                        <th class="text-right py-2 text-gray-600">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments->where('status', 'verified') as $p)
                    <tr class="border-b border-gray-50">
                        <td class="py-2 text-gray-600">{{ $p->paid_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2 text-gray-600">{{ $p->payment_code }}</td>
                        <td class="py-2 text-gray-600 capitalize">{{ str_replace('_', ' ', $p->method) }}</td>
                        <td class="py-2 text-right font-medium text-gray-700">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Terms & Notes --}}
        @if($invoice->notes)
        <div class="mb-8 bg-gray-50 p-4 rounded-lg">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Catatan</p>
            <p class="text-sm text-gray-600">{{ $invoice->notes }}</p>
        </div>
        @endif

        <div class="bg-gray-50 p-4 rounded-lg mb-8">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Ketentuan</p>
            <ul class="text-xs text-gray-500 space-y-1">
                <li>1. Invoice ini harus dibayar sesuai tanggal jatuh tempo.</li>
                <li>2. Pembayaran dapat dilakukan via transfer bank, e-wallet, atau tunai.</li>
                <li>3. Lampirkan bukti transfer pada form konfirmasi pembayaran.</li>
                <li>4. Hubungi admin jika terdapat ketidaksesuaian pada invoice ini.</li>
            </ul>
        </div>

        {{-- Signature Areas --}}
        <div class="grid grid-cols-2 gap-12 mt-12 pt-8 border-t border-gray-200">
            <div class="text-center">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-16">Penerima</p>
                <div class="border-t border-gray-400 w-48 mx-auto mb-2"></div>
                <p class="text-sm font-bold text-navy-800">{{ $invoice->user?->name }}</p>
                <p class="text-xs text-gray-400">{{ $invoice->created_at->format('d/m/Y') }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-16">Petugas MariRent</p>
                <div class="border-t border-gray-400 w-48 mx-auto mb-2"></div>
                <p class="text-sm font-bold text-navy-800">{{ $invoice->owner->name ?? 'Admin MariRent' }}</p>
                <p class="text-xs text-gray-400">{{ $invoice->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-12 pt-4 border-t border-gray-100 text-center">
            <p class="text-[10px] text-gray-300">Invoice ini dicetak secara otomatis oleh sistem MariRent Universal Rental</p>
            <p class="text-[10px] text-gray-300">Dokumen ini sah tanpa tanda tangan elektronik</p>
        </div>
    </div>
</body>
</html>
