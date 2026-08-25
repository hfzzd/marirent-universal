<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Booking;
use App\Models\Category;
use App\Models\CampingEquipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SchedulerPaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan demo lama agar bisa dijalankan ulang
        Booking::where('booking_code', 'like', 'MR-SCH-%')->forceDelete();

        // ---- User demo ----
        $users = [];
        foreach ([
            ['Siti Rahma', 'siti@marirent.com'],
            ['Andi Wijaya', 'andi@marirent.com'],
            ['Dewi Lestari', 'dewi@marirent.com'],
            ['Rizky Pratama', 'rizky@marirent.com'],
        ] as [$name, $email]) {
            $users[$name] = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'phone' => '08' . random_int(1111111111, 9999999999),
                    'address' => 'Jl. Demo No. ' . random_int(1, 99) . ', Makassar',
                    'is_active' => true,
                ]
            )->id;
        }
        $budi = User::where('email', 'user@marirent.com')->value('id');

        $month = now()->copy()->startOfMonth();
        $today = today();
        $day = fn(int $d) => $month->copy()->setDay(min($d, (int) $month->daysInMonth))->format('Y-m-d');
        $relDay = fn(int $offset) => max($today->copy()->addDays($offset), $month->copy());

        $cameras = \App\Models\Camera::pluck('id', 'name')->toArray();
        $phones = \App\Models\Phone::pluck('id', 'name')->toArray();
        $tents = CampingEquipment::pluck('id', 'name')->toArray();
        $catMobil = Category::where('slug', 'mobil')->value('id');
        $catMotor = Category::where('slug', 'motor')->value('id');

        $n = 0;
        $make = function (array $cfg) use (&$n, $users, $budi, $catMobil, $catMotor, $cameras, $phones, $tents) {
            $n++;
            $vehicleId = $driverId = $itemType = $itemId = null;
            $categoryId = null;
            $withDriver = false;

            if (!empty($cfg['vehicle'])) {
                $vehicleId = $cfg['vehicle'];
                $categoryId = $vehicleId <= 3 || $vehicleId == 7 ? $catMobil : $catMotor;
            }
            if (!empty($cfg['driver'])) {
                $withDriver = true;
                $driverId = $cfg['driver'];
            }
            foreach ([['camera', \App\Models\Camera::class, $cameras], ['phone', \App\Models\Phone::class, $phones], ['tent', CampingEquipment::class, $tents]] as [$key, $class, $pool]) {
                if (!empty($cfg[$key]) && !empty($pool)) {
                    $itemType = $class;
                    $itemId = $pool[$cfg[$key]] ?? array_values($pool)[0];
                    break;
                }
            }

            Booking::create([
                'booking_code' => 'MR-SCH-' . str_pad((string) $n, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -5)),
                'user_id' => $users[$cfg['user']] ?? $budi,
                'vehicle_id' => $vehicleId,
                'driver_id' => $driverId,
                'category_id' => $categoryId ?? $catMotor,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'rental_type' => $cfg['type'] ?? 'daily',
                'start_date' => $cfg['start'],
                'end_date' => $cfg['end'],
                'pickup_location' => 'Kantor Pusat',
                'dropoff_location' => 'Kantor Pusat',
                'with_driver' => $withDriver,
                'base_price' => $cfg['price'],
                'driver_price' => $withDriver ? ($cfg['driver_price'] ?? 0) : 0,
                'total_price' => $cfg['price'] + ($withDriver ? ($cfg['driver_price'] ?? 0) : 0),
                'discount' => 0,
                'final_price' => $cfg['price'] + ($withDriver ? ($cfg['driver_price'] ?? 0) : 0),
                'status' => $cfg['status'],
                'payment_status' => $cfg['pay'],
                'payment_due_date' => $cfg['due'],
                'source' => $cfg['source'] ?? 'online',
                'notes' => $cfg['notes'] ?? null,
                'ktp_photo' => 'ktp/ktp-demo.jpg',
            ]);
        };

        // ================= LUNAS =================
        $make([ // Lunas tepat waktu - selesai awal bulan
            'user' => 'Budi Santoso', 'vehicle' => 1, // Avanza
            'start' => $month->copy()->subDays(2), 'end' => $day(2),
            'price' => 900000, 'status' => 'completed', 'pay' => 'paid', 'due' => $day(3),
            'notes' => 'Lunas tepat jadwal',
        ]);
        $make([ // Lunas - motor mingguan
            'user' => 'Siti Rahma', 'vehicle' => 4, // Vario
            'start' => $day(4), 'end' => $day(11),
            'price' => 1050000, 'type' => 'weekly', 'status' => 'completed', 'pay' => 'paid', 'due' => $day(6),
        ]);
        $make([ // Lunas - kamera (barang sewa)
            'user' => 'Rizky Pratama', 'camera' => 'Sony A7 IV Kit Lens',
            'start' => $day(13), 'end' => $day(16),
            'price' => 1050000, 'status' => 'completed', 'pay' => 'paid', 'due' => $day(15),
            'notes' => 'Sewa kamera untuk acara pernikahan',
        ]);
        $make([ // Lunas lebih awal untuk sewa berikutnya
            'user' => 'Rizky Pratama', 'vehicle' => 3, // Innova
            'start' => $day(29), 'end' => $month->copy()->addMonth()->setDay(2),
            'price' => 1400000, 'status' => 'confirmed', 'pay' => 'paid', 'due' => $day(28),
            'notes' => 'Bayar penuh di muka',
        ]);

        // ================= TERLAMBAT =================
        $make([ // Belum bayar sama sekali - terlambat lama
            'user' => 'Andi Wijaya', 'vehicle' => 2, // Brio
            'start' => $day(8), 'end' => $day(14),
            'price' => 825000, 'status' => 'ongoing', 'pay' => 'unpaid', 'due' => $day(9),
            'notes' => 'Menunggu pelunasan - sudah dikontak 2x',
        ]);
        $make([ // DP 50% terlambat - dengan driver
            'user' => 'Dewi Lestari', 'vehicle' => 3, 'driver' => 1, // Innova + driver
            'start' => $day(11), 'end' => $day(16),
            'price' => 2500000, 'driver_price' => 1500000, 'status' => 'ongoing', 'pay' => 'partial', 'due' => $day(12),
            'notes' => 'DP dibayar tunai, sisa menunggu gaji',
        ]);
        $make([ // Belum bayar - terlambat baru
            'user' => 'Siti Rahma', 'vehicle' => 5, // NMAX
            'start' => $day(17), 'end' => $day(20),
            'price' => 450000, 'status' => 'ongoing', 'pay' => 'unpaid', 'due' => $day(18),
        ]);
        $make([ // DP 50% terlambat - tenda (barang sewa)
            'user' => 'Budi Santoso', 'tent' => true,
            'start' => $day(22), 'end' => $day(26),
            'price' => 800000, 'status' => 'confirmed', 'pay' => 'partial', 'due' => $day(21),
            'source' => 'manual', 'notes' => 'DP via transfer, sisa saat pickup',
        ]);

        // ================= JATUH TEMPO HARI INI =================
        $make([
            'user' => 'Andi Wijaya', 'vehicle' => 6, // PCX
            'start' => $today->copy()->addDay(), 'end' => $today->copy()->addDays(4),
            'price' => 405000, 'status' => 'confirmed', 'pay' => 'unpaid', 'due' => $today->toDateString(),
            'notes' => 'Harus ditagih hari ini',
        ]);
        $make([ // DP 50% jatuh tempo hari ini - sewa HP
            'user' => 'Dewi Lestari', 'phone' => 'iPhone 15 Pro Max',
            'start' => $today->toDateString(), 'end' => $today->copy()->addDays(2),
            'price' => 900000, 'status' => 'confirmed', 'pay' => 'partial', 'due' => $today->toDateString(),
        ]);

        // ================= JATUH TEMPO BEBERAPA HARI KE DEPAN =================
        $make([
            'user' => 'Rizky Pratama', 'vehicle' => 7, // Fortuner
            'start' => $relDay(3), 'end' => $relDay(6),
            'price' => 1800000, 'status' => 'confirmed', 'pay' => 'unpaid', 'due' => $relDay(2),
        ]);
        $make([ // Mingguan + driver, DP 50%
            'user' => 'Siti Rahma', 'vehicle' => 1, 'driver' => 2,
            'start' => $relDay(4), 'end' => $relDay(11),
            'price' => 3850000, 'driver_price' => 1400000, 'type' => 'weekly',
            'status' => 'confirmed', 'pay' => 'partial', 'due' => $relDay(3),
            'notes' => 'Paket wisata keluarga + driver',
        ]);
        $make([
            'user' => 'Dewi Lestari', 'vehicle' => 2,
            'start' => $relDay(6), 'end' => $relDay(8),
            'price' => 550000, 'status' => 'confirmed', 'pay' => 'unpaid', 'due' => $relDay(5),
        ]);
        $make([ // Sewa HP
            'user' => 'Budi Santoso', 'phone' => 'Samsung Galaxy S24 Ultra',
            'start' => $relDay(7), 'end' => $relDay(9),
            'price' => 600000, 'status' => 'confirmed', 'pay' => 'unpaid', 'due' => $relDay(6),
            'source' => 'manual',
        ]);

        // ================= AKHIR BULAN =================
        $make([ // Bulanan pending
            'user' => 'Andi Wijaya', 'vehicle' => 5,
            'start' => $day(min((int) $month->daysInMonth, 28)), 'end' => now()->copy()->addMonth()->addDays(27),
            'price' => 2700000, 'type' => 'monthly', 'status' => 'pending', 'pay' => 'unpaid',
            'due' => $day(min((int) $month->daysInMonth, 28)),
        ]);

        $this->command->info("Scheduler demo: {$n} booking dibuat.");
    }
}
