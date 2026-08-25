@extends('layouts.dashboard')
@section('page-title', 'Monitoring Scheduler')

@push('styles')
<style>
    .fc { font-family: inherit; }
    .fc .fc-toolbar-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
    .fc .fc-button { background: linear-gradient(135deg, #0ea5e9, #0284c7); border: none; padding: 6px 14px; font-size: 12px; font-weight: 600; border-radius: 8px; text-transform: capitalize; box-shadow: 0 2px 6px rgba(14,165,233,0.3); }
    .fc .fc-button:hover { background: linear-gradient(135deg, #0284c7, #0369a1); }
    .fc .fc-button-active { background: linear-gradient(135deg, #0369a1, #075985) !important; }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active { background: linear-gradient(135deg, #0369a1, #075985) !important; border-color: transparent; }
    .fc .fc-daygrid-day { border: 1px solid #e2e8f0; transition: background 0.15s; }
    .fc .fc-daygrid-day:hover { background: #f0f9ff; }
    .fc .fc-day-today { background: rgba(14,165,233,0.06) !important; }
    .fc .fc-col-header-cell { background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 0; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; }
    .fc .fc-event { border: none; border-radius: 6px; padding: 2px 6px; font-size: 11px; font-weight: 600; cursor: pointer; margin-bottom: 1px; line-height: 1.4; }
    .fc .fc-daygrid-day-number { font-size: 12px; font-weight: 600; color: #334155; padding: 4px 8px; }
    .fc .fc-daygrid-more-link { font-size: 11px; font-weight: 600; color: #0ea5e9; }
    .fc .fc-timegrid-slot { height: 28px; }
    .fc .fc-timegrid-slot-label-cushion { font-size: 11px; color: #64748b; }
    .fc .fc-scrollgrid { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
    .fc th, .fc td { border-color: #e2e8f0 !important; }
    .fc .fc-timegrid-col { border-left: 1px solid #e2e8f0; }
    .fc .fc-timegrid-divider { border-color: #e2e8f0; }
    .fc .fc-list-event:hover td { background: #f0f9ff; }
</style>
@endpush

@section('content')
<div x-data="schedulerApp()" x-init="init()">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-5 gap-3">
        <div>
            <h2 class="text-lg font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-sky-500"></i> Monitoring Scheduler
            </h2>
            <p class="text-xs text-gray-400 mt-0.5">Jadwal pembayaran, penjemputan, dan pemulangan kendaraan secara real-time.</p>
        </div>
        <button @click="exportCalendar()" class="flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 rounded-lg text-[12px] font-semibold text-navy-700 hover:border-sky-300 hover:text-sky-600 transition">
            <i class="fas fa-download text-[10px]"></i> Export
        </button>
    </div>

    {{-- Legend --}}
    <div class="glass-card rounded-2xl p-4 mb-5 border border-sky-100/50 shadow-sm">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
            <span class="text-[11px] font-bold text-navy-700 uppercase tracking-wide mr-1"><i class="fas fa-circle-info text-sky-400 mr-1"></i> Legenda:</span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#22c55e] shadow-sm"></span> Lunas
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#f59e0b] shadow-sm"></span> Sebagian
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#ef4444] shadow-sm"></span> Terlambat
            </span>
            <span class="w-px h-3 bg-gray-200"></span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#3b82f6] shadow-sm"></span> Mulai Sewa
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#6366f1] shadow-sm"></span> Selesai Sewa
            </span>
            <span class="w-px h-3 bg-gray-200"></span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#06b6d4] shadow-sm"></span> Penjemputan
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#8b5cf6] shadow-sm"></span> Pemulangan
            </span>
            <span class="w-px h-3 bg-gray-200"></span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#22c55e] shadow-sm"></span> Maintenance Rutin
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#f59e0b] shadow-sm"></span> Maintenance Medium
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#f97316] shadow-sm"></span> Maintenance High
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-600">
                <span class="w-3 h-3 rounded-full bg-[#ef4444] shadow-sm"></span> Maintenance Urgent
            </span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-5" x-show="summary" x-cloak>
        <template x-for="(item, key) in summary" :key="key">
            <div class="glass-card rounded-xl p-3 border border-sky-100/50 shadow-sm text-center">
                <div class="w-8 h-8 rounded-lg mx-auto mb-1.5 flex items-center justify-center" :style="'background:' + item.bg">
                    <i :class="item.icon" class="text-xs text-white"></i>
                </div>
                <p class="text-lg font-black text-navy-800" x-text="item.count"></p>
                <p class="text-[10px] font-semibold text-gray-400" x-text="item.label"></p>
            </div>
        </template>
    </div>

    {{-- Calendar --}}
    <div class="glass-card rounded-2xl border border-sky-100/50 shadow-sm overflow-hidden">
        <div id="calendar"></div>
    </div>

    {{-- Event Detail Modal --}}
    <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="showModal=false">
        <div class="fixed inset-0 bg-navy-900/40 backdrop-blur-sm"></div>
        <div class="relative glass-card rounded-2xl p-6 w-full max-w-md shadow-2xl border border-sky-100/50 animate-slide-up" @click.stop>
            <button @click="showModal=false" class="absolute top-3 right-3 w-7 h-7 rounded-lg bg-gray-100 hover:bg-red-100 flex items-center justify-center text-gray-400 hover:text-red-500 transition">
                <i class="fas fa-times text-xs"></i>
            </button>

            <template x-if="selectedEvent">
                <div>
                    {{-- Event type badge --}}
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-3 h-3 rounded-full shadow-sm" :style="'background:' + selectedEvent.backgroundColor"></span>
                        <span class="text-xs font-bold uppercase tracking-wide px-2.5 py-1 rounded-full"
                              :style="'background:' + selectedEvent.backgroundColor + '20; color:' + selectedEvent.backgroundColor"
                              x-text="getEventLabel(selectedEvent.extendedProps.type)"></span>
                    </div>

                    {{-- Booking code --}}
                    <div class="mb-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Kode Booking</p>
                        <p class="text-sm font-extrabold text-navy-800 font-mono" x-text="selectedEvent.extendedProps.booking_code"></p>
                    </div>

                    {{-- Details --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 bg-sky-50/50 rounded-xl">
                            <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                                <i class="fas fa-user text-sky-500 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-medium">Pelanggan</p>
                                <p class="text-xs font-bold text-navy-800" x-text="selectedEvent.extendedProps.user_name"></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-sky-50/50 rounded-xl">
                            <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                                <i class="fas fa-car text-sky-500 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-medium">Kendaraan / Item</p>
                                <p class="text-xs font-bold text-navy-800" x-text="selectedEvent.extendedProps.vehicle_name"></p>
                            </div>
                        </div>

                        <template x-if="selectedEvent.extendedProps.amount">
                            <div class="flex items-center gap-3 p-3 bg-sky-50/50 rounded-xl">
                                <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                                    <i class="fas fa-wallet text-sky-500 text-xs"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-[10px] text-gray-400 font-medium">Pembayaran</p>
                                    <p class="text-xs font-bold text-navy-800">Total: Rp <span x-text="formatRp(selectedEvent.extendedProps.amount)"></span></p>
                                    <template x-if="selectedEvent.extendedProps.paid_amount">
                                        <p class="text-[11px] font-semibold mt-0.5" :class="selectedEvent.extendedProps.paid_amount >= selectedEvent.extendedProps.amount ? 'text-emerald-600' : 'text-amber-600'">
                                            Terbayar: Rp <span x-text="formatRp(selectedEvent.extendedProps.paid_amount)"></span>
                                        </p>
                                    </template>
                                    <template x-if="selectedEvent.extendedProps.due_amount">
                                        <p class="text-[11px] font-semibold text-red-500 mt-0.5">
                                            Sisa: Rp <span x-text="formatRp(selectedEvent.extendedProps.due_amount)"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedEvent.extendedProps.pickup_location">
                            <div class="flex items-center gap-3 p-3 bg-sky-50/50 rounded-xl">
                                <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                                    <i class="fas fa-map-marker-alt text-sky-500 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-medium">Lokasi Jemput</p>
                                    <p class="text-xs font-bold text-navy-800" x-text="selectedEvent.extendedProps.pickup_location"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedEvent.extendedProps.dropoff_location">
                            <div class="flex items-center gap-3 p-3 bg-sky-50/50 rounded-xl">
                                <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                                    <i class="fas fa-map-pin text-sky-500 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-medium">Lokasi Pulang</p>
                                    <p class="text-xs font-bold text-navy-800" x-text="selectedEvent.extendedProps.dropoff_location"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedEvent.extendedProps.due_date && selectedEvent.extendedProps.type === 'terlambat'">
                            <div class="flex items-center gap-3 p-3 bg-red-50/50 rounded-xl border border-red-100">
                                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-red-500 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] text-red-400 font-medium">Jatuh Tempo</p>
                                    <p class="text-xs font-bold text-red-600" x-text="selectedEvent.extendedProps.due_date"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedEvent.extendedProps.type === 'maintenance'">
                            <div class="space-y-2">
                                <div class="flex items-center gap-3 p-3 bg-amber-50/50 rounded-xl">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                        <i class="fas fa-wrench text-amber-500 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-medium">Kode</p>
                                        <p class="text-xs font-bold text-navy-800 font-mono" x-text="selectedEvent.extendedProps.maintenance_code"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-sky-50/50 rounded-xl">
                                    <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                                        <i class="fas fa-tools text-sky-500 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-medium">Tipe Maintenance</p>
                                        <p class="text-xs font-bold text-navy-800" x-text="selectedEvent.extendedProps.maintenance_type"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-sky-50/50 rounded-xl">
                                    <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                                        <i class="fas fa-flag text-sky-500 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-medium">Prioritas</p>
                                        <p class="text-xs font-bold text-navy-800 capitalize" x-text="selectedEvent.extendedProps.priority"></p>
                                    </div>
                                </div>
                                <template x-if="selectedEvent.extendedProps.technician && selectedEvent.extendedProps.technician !== '-'">
                                <div class="flex items-center gap-3 p-3 bg-sky-50/50 rounded-xl">
                                    <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                                        <i class="fas fa-user-cog text-sky-500 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-medium">Teknisi</p>
                                        <p class="text-xs font-bold text-navy-800" x-text="selectedEvent.extendedProps.technician"></p>
                                    </div>
                                </div>
                                </template>
                                <template x-if="selectedEvent.extendedProps.estimated_cost > 0">
                                <div class="flex items-center gap-3 p-3 bg-sky-50/50 rounded-xl">
                                    <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                                        <i class="fas fa-coins text-sky-500 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-medium">Estimasi Biaya</p>
                                        <p class="text-xs font-bold text-navy-800">Rp <span x-text="formatRp(selectedEvent.extendedProps.estimated_cost)"></span></p>
                                    </div>
                                </div>
                                </template>
                                <div class="flex items-center gap-3 p-3 bg-sky-50/50 rounded-xl">
                                    <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                                        <i class="fas fa-info-circle text-sky-500 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-medium">Status</p>
                                        <p class="text-xs font-bold text-navy-800 capitalize" x-text="selectedEvent.extendedProps.status"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Action --}}
                    <div class="mt-5 pt-4 border-t border-gray-100">
                        <a :href="'{{ url('/bookings/') }}/' + selectedEvent.extendedProps.booking_id"
                           class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-sky-500/20">
                            <i class="fas fa-eye text-[10px]"></i> Lihat Detail Booking
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/locales/id.global.min.js"></script>
<script>
function schedulerApp() {
    return {
        calendar: null,
        showModal: false,
        selectedEvent: null,
        summary: {},

        init() {
            const self = this;
            const calendarEl = document.getElementById('calendar');

            this.calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'id',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    week: 'Minggu',
                    day: 'Hari'
                },
                events: '{{ route("superadmin.scheduler.events") }}',
                eventSourceSuccess: function(response) {
                    self.computeSummary(response);
                    return response;
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    self.selectedEvent = {
                        title: info.event.title,
                        backgroundColor: info.event.backgroundColor,
                        extendedProps: info.event.extendedProps
                    };
                    self.showModal = true;
                },
                eventDisplay: 'block',
                dayMaxEvents: 4,
                moreLinkText: function(n) {
                    return '+' + n + ' lagi';
                },
                height: 'auto',
                nowIndicator: true,
                datesSet: function() {
                    setTimeout(() => {
                        document.querySelectorAll('.fc-toolbar-chips').forEach(el => {
                            el.style.display = 'flex';
                            el.style.gap = '4px';
                        });
                    }, 50);
                }
            });

            this.calendar.render();
        },

        computeSummary(events) {
            const counts = {
                lunas: { count: 0, label: 'Lunas', bg: 'linear-gradient(135deg,#22c55e,#16a34a)', icon: 'fas fa-check-circle' },
                sebagian: { count: 0, label: 'Sebagian', bg: 'linear-gradient(135deg,#f59e0b,#d97706)', icon: 'fas fa-hourglass-half' },
                terlambat: { count: 0, label: 'Terlambat', bg: 'linear-gradient(135deg,#ef4444,#dc2626)', icon: 'fas fa-exclamation-triangle' },
                mulai: { count: 0, label: 'Mulai Sewa', bg: 'linear-gradient(135deg,#3b82f6,#2563eb)', icon: 'fas fa-play-circle' },
                selesai: { count: 0, label: 'Selesai Sewa', bg: 'linear-gradient(135deg,#6366f1,#4f46e5)', icon: 'fas fa-stop-circle' },
                penjemputan: { count: 0, label: 'Penjemputan', bg: 'linear-gradient(135deg,#06b6d4,#0891b2)', icon: 'fas fa-truck-pickup' },
                pemulangan: { count: 0, label: 'Pemulangan', bg: 'linear-gradient(135deg,#8b5cf6,#7c3aed)', icon: 'fas fa-home' }
            };

            events.forEach(e => {
                const t = e.extendedProps?.type;
                if (t && counts[t]) counts[t].count++;
            });

            this.summary = Object.fromEntries(
                Object.entries(counts).filter(([_, v]) => v.count > 0)
            );
        },

        getEventLabel(type) {
            const labels = {
                lunas: 'Pembayaran Lunas',
                sebagian: 'Pembayaran Sebagian',
                terlambat: 'Pembayaran Terlambat',
                mulai: 'Jadwal Mulai Sewa',
                selesai: 'Jadwal Selesai Sewa',
                penjemputan: 'Penjemputan Kendaraan',
                pemulangan: 'Pemulangan Kendaraan',
                maintenance: 'Jadwal Maintenance'
            };
            return labels[type] || type;
        },

        formatRp(val) {
            return Number(val).toLocaleString('id-ID');
        },

        exportCalendar() {
            if (this.calendar) {
                window.print();
            }
        }
    };
}
</script>
@endpush
@endsection
