@extends('layouts.dashboard')
@section('page-title', 'Pesan Kontak - ' . $contactMessage->subject)

@section('content')
<div class="max-w-4xl">
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('mail.contacts') }}" class="text-sky-600 hover:text-sky-700 text-xs font-semibold flex items-center gap-1.5 transition">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesan Masuk
        </a>
    </div>

    <div class="glass-card rounded-2xl shadow-sm border border-amber-100/50 p-6 md:p-8">
        {{-- Header --}}
        <div class="pb-5 mb-5 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <h2 class="text-lg md:text-xl font-extrabold text-navy-800">{{ $contactMessage->subject }}</h2>
                <span class="text-xs text-gray-400 font-medium">{{ $contactMessage->created_at->translatedFormat('l, d F Y H:i') }}</span>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold shadow-sm">
                    {{ strtoupper(substr($contactMessage->name, 0, 2)) }}
                </div>
                <div>
                    <span class="font-bold text-navy-800 text-sm">{{ $contactMessage->name }}</span>
                    <p class="text-xs text-gray-500">
                        Email: <a href="mailto:{{ $contactMessage->email }}" class="text-sky-600 font-semibold">{{ $contactMessage->email }}</a>
                        @if($contactMessage->phone)
                        &bull; Telp: <span class="font-mono text-navy-700">{{ $contactMessage->phone }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Message Body --}}
        <div class="text-xs leading-relaxed text-navy-800 whitespace-pre-line py-4 bg-amber-50/20 p-4 rounded-xl border border-amber-100/40">
{{ $contactMessage->message }}
        </div>

        {{-- Action Buttons --}}
        <div class="mt-6 flex flex-wrap items-center gap-3">
            @if($contactMessage->phone)
            @php $cleanPhone = preg_replace('/[^0-9]/', '', $contactMessage->phone); if(str_starts_with($cleanPhone, '0')) $cleanPhone = '62'.substr($cleanPhone, 1); @endphp
            <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($contactMessage->name) }},%20kami%20dari%20MariRent%20Universal%20merespon%20pesan%20Anda%20terkait%20{{ urlencode($contactMessage->subject) }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-emerald-500/20 flex items-center gap-2 transition">
                <i class="fab fa-whatsapp text-sm"></i> Balas Cepat via WhatsApp
            </a>
            @endif
            <a href="mailto:{{ $contactMessage->email }}?subject={{ urlencode('Re: ' . $contactMessage->subject) }}" class="btn-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-sky-500/20 flex items-center gap-2 transition">
                <i class="fas fa-envelope"></i> Balas via Email
            </a>
        </div>

        {{-- Reply Log Form --}}
        <div class="mt-8 pt-6 border-t border-gray-100">
            <h4 class="text-xs font-bold text-navy-800 uppercase tracking-wider mb-3">Catatan / Riwayat Balasan</h4>
            
            @if($contactMessage->replied_at)
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs">
                <div class="flex items-center justify-between mb-1">
                    <strong class="text-emerald-800"><i class="fas fa-check-circle"></i> Sudah Ditanggapi</strong>
                    <span class="text-[11px] text-emerald-600">{{ $contactMessage->replied_at->translatedFormat('d M Y H:i') }}</span>
                </div>
                <p class="text-emerald-700 whitespace-pre-line">{{ $contactMessage->reply_message }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('mail.contacts.reply', $contactMessage) }}">
                @csrf
                <textarea name="reply_message" rows="3" required placeholder="Tuliskan catatan tindak lanjut atau balasan yang sudah diberikan kepada pelanggan..." class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none leading-relaxed">{{ $contactMessage->reply_message }}</textarea>
                <button type="submit" class="mt-2 bg-gray-800 hover:bg-black text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fas fa-save"></i> Simpan Catatan Balasan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
