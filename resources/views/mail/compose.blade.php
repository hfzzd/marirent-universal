@extends('layouts.dashboard')
@section('page-title', 'Tulis Pesan Baru')

@section('content')
<div class="max-w-3xl">
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('mail.index') }}" class="text-sky-600 hover:text-sky-700 text-xs font-semibold flex items-center gap-1.5 transition">
            <i class="fas fa-arrow-left"></i> Kembali ke Kotak Masuk
        </a>
    </div>

    <div class="glass-card rounded-2xl shadow-sm border border-sky-100/50 p-6 md:p-8">
        <div class="flex items-center gap-3 pb-5 mb-6 border-b border-gray-100">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shadow-sm">
                <i class="fas fa-pen-to-square"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-navy-800">Tulis Pesan / Surat Internal</h3>
                <p class="text-xs text-gray-400">Kirim komunikasi resmi ke Superadmin, Owner, Driver, atau Pelanggan.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('mail.store') }}">
            @csrf
            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-navy-700 mb-1.5">Penerima Pesan *</label>
                    <select name="receiver_id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        <option value="">-- Pilih Pengguna Tujuan --</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (old('receiver_id') == $u->id || (isset($selectedUser) && $selectedUser->id == $u->id)) ? 'selected' : '' }}>
                            {{ $u->name }} ({{ ucfirst($u->role) }}) - {{ $u->email }}
                        </option>
                        @endforeach
                    </select>
                    @error('receiver_id') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-semibold text-navy-700 mb-1.5">Subjek / Perihal *</label>
                    <input type="text" name="subject" value="{{ old('subject', isset($replyTo) ? 'Re: ' . $replyTo->subject : '') }}" required placeholder="Contoh: Koordinasi Jadwal Penjemputan / Laporan Armada" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    @error('subject') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-semibold text-navy-700 mb-1.5">Isi Pesan *</label>
                    <textarea name="body" rows="8" required placeholder="Tulis isi pesan Anda di sini..." class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none leading-relaxed">{{ old('body', isset($replyTo) ? "\n\n--- Pada " . $replyTo->created_at->format('d M Y H:i') . ", " . $replyTo->sender->name . " menulis: ---\n" . $replyTo->body : '') }}</textarea>
                    @error('body') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-gray-100 flex items-center gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-sky-500/20 flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Kirim Pesan
                </button>
                <a href="{{ route('mail.index') }}" class="bg-gray-100 hover:bg-gray-200 px-5 py-2.5 rounded-xl text-xs font-semibold text-navy-700 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
