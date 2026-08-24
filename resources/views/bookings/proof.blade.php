<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Booking {{ $booking->booking_code }} - MariRent</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; color: #0f172a; padding: 24px; }
        .toolbar { max-width: 800px; margin: 0 auto 16px; display: flex; gap: 10px; }
        .btn { padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; }
        .btn-print { background: #0ea5e9; color: #fff; }
        .btn-back { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
        .sheet { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(15,23,42,.08); overflow: hidden; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; padding: 22px 28px; border-bottom: 3px solid #0ea5e9; }
        .brand h1 { font-size: 20px; letter-spacing: .5px; color: #0369a1; }
        .brand p { font-size: 11px; color: #64748b; margin-top: 2px; }
        .badge { background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px; white-space: nowrap; }
        .title { text-align: center; padding: 18px 28px 6px; }
        .title h2 { font-size: 15px; letter-spacing: 1.5px; text-transform: uppercase; }
        .title p { font-size: 11.5px; color: #64748b; margin-top: 3px; }
        .code { text-align: center; padding: 4px 28px 14px; }
        .code span { display: inline-block; border: 1.5px dashed #94a3b8; border-radius: 8px; padding: 7px 20px; font-weight: 700; font-size: 15px; letter-spacing: 1px; color: #0f172a; }
        table.details { width: calc(100% - 56px); margin: 0 28px; border-collapse: collapse; font-size: 12.5px; }
        table.details th, table.details td { border: 1px solid #e2e8f0; padding: 8px 12px; text-align: left; vertical-align: top; }
        table.details th { background: #f8fafc; width: 190px; color: #475569; font-weight: 600; }
        section.block { padding: 16px 28px 0; }
        section.block h3 { font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: #0369a1; margin-bottom: 8px; border-left: 3px solid #0ea5e9; padding-left: 8px; }
        .costs { width: calc(100% - 56px); margin: 6px 28px 0; border-collapse: collapse; font-size: 12.5px; }
        .costs td { padding: 6px 12px; border-bottom: 1px dashed #e2e8f0; }
        .costs td:last-child { text-align: right; font-weight: 600; white-space: nowrap; }
        .costs tr.total td { border-bottom: none; border-top: 2px solid #0ea5e9; font-size: 14px; font-weight: 700; color: #0369a1; padding-top: 9px; }
        .notes { width: calc(100% - 56px); margin: 12px 28px 0; font-size: 11.5px; color: #475569; line-height: 1.55; }
        .signs { display: flex; justify-content: space-between; gap: 12px; padding: 34px 40px 30px; page-break-inside: avoid; }
        .sign { flex: 1; text-align: center; font-size: 11.5px; color: #334155; }
        .sign .line { margin-top: 58px; border-top: 1.5px solid #334155; padding-top: 5px; font-weight: 600; }
        .foot { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 28px; font-size: 10.5px; color: #94a3b8; text-align: center; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; border-radius: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()" class="btn btn-print">&#128424; Cetak Bukti</button>
        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-back">&larr; Kembali ke Detail Booking</a>
    </div>

    <div class="sheet">
        <div class="head">
            <div class="brand">
                <h1>MariRent</h1>
                <p>Rental Universal &mdash; Mobil, Motor, HP, Kamera &amp; Alat Camping</p>
                <p>Jl. Contoh No. 123 &bull; 0812-3456-7890 &bull; halo@marirent.test</p>
            </div>
            <div style="text-align:right">
                <span class="badge">{{ strtoupper($booking->status === 'pending' ? 'Menunggu Konfirmasi' : ($booking->status)) }}</span>
                <p style="font-size:10.5px;color:#94a3b8;margin-top:6px">Dicetak {{ now()->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="title">
            <h2>Bukti Booking &amp; Serah Terima Unit Sewa</h2>
            <p>Dokumen ini merupakan bukti pemesanan resmi MariRent. Mohon dibawa saat pengambilan unit.</p>
        </div>
        <div class="code"><span>{{ $booking->booking_code }}</span></div>

        @php
            $jenis = $booking->with_driver ? 'Sewa dengan Driver' : 'Lepas Kunci';
            if (!$booking->isVehicleBooking()) {
                $jenis = 'Sewa Barang (' . ($booking->category->name ?? '-') . ')';
            }
        @endphp

        <section class="block">
            <h3>A. Data Pemesan</h3>
            <table class="details">
                <tr><th>Nama Penyewa</th><td>{{ $booking->guest_name ?? ($booking->user->name ?? '-') }}</td></tr>
                <tr><th>No. HP</th><td>{{ $booking->guest_phone ?? '-' }}</td></tr>
                <tr><th>Identitas (KTP)</th><td>@if($booking->ktp_photo) Sudah dilampirkan / diverifikasi @else Belum dilampirkan @endif</td></tr>
            </table>
        </section>

        <section class="block">
            <h3>B. Detail Unit &amp; Periode Sewa</h3>
            <table class="details">
                <tr><th>Unit Disewa</th><td><strong>{{ $booking->unitName() }}</strong></td></tr>
                <tr><th>Kategori</th><td>{{ $booking->category->name ?? '-' }} &mdash; {{ $booking->rental_type === 'hourly' ? 'Per Jam' : ($booking->rental_type === 'weekly' ? 'Per Minggu' : ($booking->rental_type === 'monthly' ? 'Per Bulan' : 'Per Hari')) }}</td></tr>
                <tr><th>Jenis Sewa</th><td>{{ $jenis }}
                    @if($booking->with_driver && $booking->driver)
                        &mdash; Driver: <strong>{{ $booking->driver->user->name ?? '-' }}</strong>@if(!empty($booking->driver->license_number)) (SIM {{ $booking->driver->license_number }})@endif
                    @elseif(!$booking->with_driver && $booking->isVehicleBooking())
                        &mdash; Pengemudi: penyewa sendiri (wajib membawa SIM asli)
                    @endif
                </td></tr>
                <tr><th>Periode Sewa</th><td>{{ $booking->start_date->format('d M Y, H:i') }} s/d {{ $booking->end_date->format('d M Y, H:i') }}</td></tr>
                @if($booking->pickup_location || $booking->dropoff_location)
                <tr><th>Lokasi</th><td>Jemput: {{ $booking->pickup_location ?: '(di kantor)' }} &bull; Kembali: {{ $booking->dropoff_location ?: '(di kantor)' }}</td></tr>
                @endif
            </table>
        </section>

        <section class="block">
            <h3>C. Rincian Biaya</h3>
            <table class="costs">
                <tr><td>Biaya sewa unit ({{ $booking->rental_type === 'hourly' ? 'per jam' : ($booking->rental_type === 'weekly' ? 'per minggu' : ($booking->rental_type === 'monthly' ? 'per bulan' : 'per hari')) }})</td><td>Rp {{ number_format($booking->base_price, 0, ',', '.') }}</td></tr>
                @if((float) $booking->driver_price > 0)
                <tr><td>Biaya driver</td><td>Rp {{ number_format($booking->driver_price, 0, ',', '.') }}</td></tr>
                @endif
                @if((float) $booking->discount > 0)
                <tr><td>Diskon</td><td>-Rp {{ number_format($booking->discount, 0, ',', '.') }}</td></tr>
                @endif
                <tr class="total"><td>TOTAL</td><td>Rp {{ number_format($booking->final_price, 0, ',', '.') }}</td></tr>
            </table>
            <p style="margin:6px 28px 0;font-size:11px;color:#64748b">Status pembayaran: <strong>{{ $booking->payment_status === 'paid' ? 'LUNAS' : ($booking->payment_status === 'down_payment' ? 'DP Terbayar' : 'BELUM DIBAYAR') }}</strong></p>
        </section>

        <section class="block" style="padding-bottom:4px">
            <h3>D. Catatan Serah Terima</h3>
            <ul class="notes" style="list-style:disc;padding-left:18px;margin:0;width:auto">
                <li>Kondisi unit diperiksa bersama saat penyerahan &amp; pengembalian (foto kelengkapan/kerusakan dicatat dalam berita acara inspeksi).</li>
                <li>Sewa lepas kunci: penyewa wajib menunjukkan SIM asli yang masih berlaku dan mengemudi sesuai peraturan.</li>
                <li>Pengembalian melewati waktu yang disepakati dapat dikenakan biaya tambahan.</li>
                @if($booking->notes)<li>Catatan khusus: {{ $booking->notes }}</li>@endif
            </ul>
        </section>

        <div class="signs">
            <div class="sign">
                <p>Penyewa,</p>
                <p class="line">{{ $booking->guest_name ?? ($booking->user->name ?? '(..........................)') }}</p>
            </div>
            <div class="sign">
                <p>Petugas MariRent,</p>
                <p class="line">(..........................)</p>
            </div>
        </div>

        <div class="foot">Dokumen ini sah tanpa tanda tangan basah jika ditampilkan dari sistem MariRent &bull; Kode verifikasi: {{ $booking->booking_code }}</div>
    </div>
</body>
</html>
