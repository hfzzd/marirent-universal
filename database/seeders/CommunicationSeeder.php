<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ContactMessage;
use App\Models\InboxMessage;
use App\Models\Conversation;
use App\Models\ChatMessage;

class CommunicationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'superadmin')->first() ?? User::first();
        $owner = User::where('role', 'owner')->first();
        $driver = User::where('role', 'driver')->first();
        $user = User::where('role', 'user')->first();

        // 1. Seed Contact Messages from web
        ContactMessage::create([
            'name' => 'Rian Pratama',
            'email' => 'rian.pratama@gmail.com',
            'phone' => '081298765432',
            'subject' => 'Tanya Paket Sewa Mobil Lepas Kunci Mingguan',
            'message' => 'Halo MariRent, apakah unit Innova Reborn tersedia untuk sewa lepas kunci selama 7 hari mulai akhir pekan ini? Mohon infokan syarat dan diskonnya. Terima kasih.',
            'is_read' => false,
            'created_at' => now()->subHours(2),
        ]);

        ContactMessage::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi.lestari@yahoo.com',
            'phone' => '085612345678',
            'subject' => 'Sewa Kamera Sony A7III & Lensa',
            'message' => 'Selamat siang, saya mau tanya ketersediaan kamera Sony A7III untuk acara wisuda tanggal 28 nanti. Apakah sudah termasuk memori dan baterai cadangan?',
            'is_read' => true,
            'read_at' => now()->subHour(),
            'replied_at' => now()->subMinutes(30),
            'reply_message' => 'Halo Ibu Dewi, unit Sony A7III tersedia lengkap dengan 2 baterai dan SD Card 64GB. Silakan booking melalui website.',
            'created_at' => now()->subDay(),
        ]);

        ContactMessage::create([
            'name' => 'Faisal Rahman',
            'email' => 'faisal.r@outlook.com',
            'phone' => '087799881122',
            'subject' => 'Kerjasama Mitra Rental Kendaraan',
            'message' => 'Halo manajemen MariRent, saya memiliki 3 unit Toyota Avanza dan ingin bergabung menjadi mitra Owner di platform Anda. Bagaimana alur pendaftarannya?',
            'is_read' => false,
            'created_at' => now()->subMinutes(45),
        ]);

        // 2. Seed Internal Inbox Messages
        if ($admin && $owner) {
            InboxMessage::create([
                'sender_id' => $admin->id,
                'receiver_id' => $owner->id,
                'subject' => 'Laporan Rekapitulasi Pembagian Hasil Sewa Bulan Ini',
                'body' => "Halo Bapak Owner,\n\nTerlampir rekapitulasi penyewaan armada kendaraan Anda untuk periode bulan berjalan. Seluruh dana invoice yang telah lunas telah dikalkulasikan ke saldo akun Anda.\n\nSalam,\nTim Manajemen MariRent",
                'is_read' => true,
                'is_starred_receiver' => true,
                'created_at' => now()->subDays(2),
            ]);

            InboxMessage::create([
                'sender_id' => $owner->id,
                'receiver_id' => $admin->id,
                'subject' => 'Konfirmasi Penambahan Unit Honda PCX 160',
                'body' => "Halo Admin,\n\nSaya telah mendaftarkan 1 unit motor Honda PCX 160 warna hitam ke dalam sistem. Mohon bantu verifikasi dokumen STNK agar dapat segera aktif di katalog publik.\n\nTerima kasih.",
                'is_read' => false,
                'is_starred_sender' => true,
                'created_at' => now()->subHours(5),
            ]);
        }

        if ($admin && $driver) {
            InboxMessage::create([
                'sender_id' => $admin->id,
                'receiver_id' => $driver->id,
                'subject' => 'Instruksi Standar Operasional & Kebersihan Unit',
                'body' => "Halo Driver,\n\nHarap pastikan unit selalu dalam keadaan bersih dan bahan bakar terisi sesuai checklist sebelum melakukan penjemputan tamu. Lakukan inspeksi foto sebelum dan sesudah trip.\n\nSemangat bertugas!",
                'is_read' => false,
                'created_at' => now()->subDay(),
            ]);
        }

        // 3. Seed Live Chat Conversation & Messages
        if ($admin && $driver) {
            $conv1 = Conversation::findOrCreateBetween($admin->id, $driver->id);
            ChatMessage::create([
                'conversation_id' => $conv1->id,
                'sender_id' => $admin->id,
                'message' => 'Halo Ahmad, tolong bersiap untuk penjemputan tamu jam 09.00 di Bandara ya.',
                'is_read' => true,
                'read_at' => now()->subHours(3),
                'created_at' => now()->subHours(3),
            ]);
            ChatMessage::create([
                'conversation_id' => $conv1->id,
                'sender_id' => $driver->id,
                'message' => 'Siap Pak Admin, unit Innova sudah bersih dan saya sudah dalam perjalanan ke lokasi penjemputan.',
                'is_read' => true,
                'read_at' => now()->subHours(2),
                'created_at' => now()->subHours(2),
            ]);
            ChatMessage::create([
                'conversation_id' => $conv1->id,
                'sender_id' => $driver->id,
                'message' => 'Tamu sudah masuk kendaraan, perjalanan menuju hotel dimulai.',
                'is_read' => false,
                'created_at' => now()->subMinutes(15),
            ]);
            $conv1->update(['last_message_at' => now()->subMinutes(15)]);
        }

        if ($admin && $user) {
            $conv2 = Conversation::findOrCreateBetween($admin->id, $user->id);
            ChatMessage::create([
                'conversation_id' => $conv2->id,
                'sender_id' => $user->id,
                'message' => 'Halo CS MariRent, saya mau tanya apakah bisa ubah jam penjemputan untuk booking mobil saya besok?',
                'is_read' => true,
                'read_at' => now()->subHour(),
                'created_at' => now()->subHours(1),
            ]);
            ChatMessage::create([
                'conversation_id' => $conv2->id,
                'sender_id' => $admin->id,
                'message' => 'Halo Kak Budi, tentu bisa. Mau diundur ke jam berapa ya kak? Biar kami koordinasikan dengan driver terkait.',
                'is_read' => true,
                'read_at' => now()->subMinutes(40),
                'created_at' => now()->subMinutes(45),
            ]);
            ChatMessage::create([
                'conversation_id' => $conv2->id,
                'sender_id' => $user->id,
                'message' => 'Sekitar jam 10.30 pagi ya kak. Terima kasih banyak!',
                'is_read' => false,
                'created_at' => now()->subMinutes(10),
            ]);
            $conv2->update(['last_message_at' => now()->subMinutes(10)]);
        }
    }
}
