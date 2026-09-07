@extends('layouts.dashboard')
@section('page-title', $driver->user?->name)
@section('content')
<div class="max-w-3xl">
    <a href="{{ route('drivers.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-sky-500 to-sky-600 px-6 py-5 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="font-bold text-lg">{{ strtoupper(substr($driver->user?->name ?? '', 0, 1)) }}</span>
                </div>
                <div>
                    <h2 class="font-bold text-lg leading-tight">{{ $driver->user?->name }}</h2>
                    <p class="text-[12px] text-white/80">{{ $driver->position ?? ($driver->license_number ? 'Driver' : 'Karyawan') }} &middot; {{ $driver->user?->email }}</p>
                </div>
                <div class="ml-auto">
                    @if($driver->isAvailable())
                    <span class="badge badge-green bg-white/20 text-white">Tersedia</span>
                    @elseif($driver->status == 'on_trip')
                    <span class="badge badge-yellow">Bertugas</span>
                    @elseif($driver->status == 'on_duty')
                    <span class="badge badge-blue">Di Tugaskan</span>
                    @else
                    <span class="badge badge-gray">Nonaktif</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Asal Company</p>
                <p class="font-semibold text-navy-800">{{ $driver->company?->name ?? 'Tanpa company' }}</p>
                <p class="text-[12px] text-gray-400">{{ $driver->company?->city ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Owner</p>
                <p class="font-semibold text-navy-800">{{ $driver->owner?->name ?? '-' }}</p>
                <p class="text-[12px] text-gray-400">{{ $driver->owner?->email ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">No. HP</p>
                <p class="font-semibold text-navy-800">{{ $driver->user?->phone ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Alamat</p>
                <p class="text-[13px] text-navy-800">{{ $driver->user?->address ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Nomor SIM</p>
                <p class="font-semibold text-navy-800">{{ $driver->license_number ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Tipe / Berlaku</p>
                <p class="font-semibold text-navy-800">{{ $driver->license_type ?? '-' }}{{ $driver->license_expiry ? ' / ' . $driver->license_expiry->format('d M Y') : '' }}</p>
            </div>
            <div>
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Gaji per Hari</p>
                <p class="font-semibold text-navy-800">Rp {{ number_format($driver->daily_salary,0,',','.') }}</p>
            </div>
            <div>
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Bonus per Trip</p>
                <p class="font-semibold text-navy-800">Rp {{ number_format($driver->trip_salary,0,',','.') }}</p>
            </div>
            @if($driver->notes)
            <div class="sm:col-span-2">
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Catatan</p>
                <p class="text-[13px] text-navy-700 bg-gray-50 rounded-lg px-3 py-2">{{ $driver->notes }}</p>
            </div>
            @endif
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
            <a href="{{ route('drivers.edit', $driver) }}" class="btn-primary text-white px-5 py-2.5 rounded-lg text-sm font-semibold"><i class="fas fa-edit mr-1.5"></i> Edit</a>
            <form method="POST" action="{{ route('drivers.destroy', $driver) }}" onsubmit="return confirm('Hapus akun {{ $driver->user?->name }}? Tindakan ini tidak bisa dibatalkan.')">
                @csrf @method('DELETE')
                <button class="bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold"><i class="fas fa-trash mr-1.5"></i> Hapus</button>
            </form>
        </div>
    </div>
</div>
@endsection