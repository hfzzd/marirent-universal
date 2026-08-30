<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BrandCatalogPhoto;

class BrandCatalogPhotoSeeder extends Seeder
{
    public function run(): void
    {
        $photos = [
            // Mobil
            ['brand_name' => 'Toyota', 'item_type' => 'mobil', 'photo_path' => 'brand-catalog/mobil/toyota.png', 'sort_order' => 1],
            ['brand_name' => 'Honda', 'item_type' => 'mobil', 'photo_path' => 'brand-catalog/mobil/Honda.png', 'sort_order' => 1],
            ['brand_name' => 'Daihatsu', 'item_type' => 'mobil', 'photo_path' => 'brand-catalog/mobil/Daihatsu.png', 'sort_order' => 1],
            ['brand_name' => 'Suzuki', 'item_type' => 'mobil', 'photo_path' => 'brand-catalog/mobil/suzuki.png', 'sort_order' => 1],
            ['brand_name' => 'Mitsubishi', 'item_type' => 'mobil', 'photo_path' => 'brand-catalog/mobil/Mitsubishi.png', 'sort_order' => 1],
            ['brand_name' => 'Nissan', 'item_type' => 'mobil', 'photo_path' => 'brand-catalog/mobil/nissan.png', 'sort_order' => 1],
            ['brand_name' => 'Hyundai', 'item_type' => 'mobil', 'photo_path' => 'brand-catalog/mobil/Hyundai.png', 'sort_order' => 1],
            ['brand_name' => 'Kia', 'item_type' => 'mobil', 'photo_path' => 'brand-catalog/mobil/kia.png', 'sort_order' => 1],
            ['brand_name' => 'Mazda', 'item_type' => 'mobil', 'photo_path' => 'brand-catalog/mobil/mazda.png', 'sort_order' => 1],

            // Motor
            ['brand_name' => 'Honda', 'item_type' => 'motor', 'photo_path' => 'brand-catalog/motor/honda.png', 'sort_order' => 1],
            ['brand_name' => 'Yamaha', 'item_type' => 'motor', 'photo_path' => 'brand-catalog/motor/yamaha.png', 'sort_order' => 1],
            ['brand_name' => 'Kawasaki', 'item_type' => 'motor', 'photo_path' => 'brand-catalog/motor/kawasaki.png', 'sort_order' => 1],
            ['brand_name' => 'Vespa', 'item_type' => 'motor', 'photo_path' => 'brand-catalog/motor/vespa.png', 'sort_order' => 1],

            // HP
            ['brand_name' => 'Apple', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/apple.jpg', 'sort_order' => 1],
            ['brand_name' => 'Samsung', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/samsung.jpg', 'sort_order' => 1],
            ['brand_name' => 'Xiaomi', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/logo-baru-xiaomi.jpeg', 'sort_order' => 1],
            ['brand_name' => 'Google', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/google.png', 'sort_order' => 1],
            ['brand_name' => 'OnePlus', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/oneplus.jpg', 'sort_order' => 1],
            ['brand_name' => 'Realme', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/realme.png', 'sort_order' => 1],
            ['brand_name' => 'Oppo', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/oppo.png', 'sort_order' => 1],
            ['brand_name' => 'Vivo', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/vivo.jpeg', 'sort_order' => 1],
            ['brand_name' => 'Sony', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/Xperia_logo-580-75.jpg', 'sort_order' => 1],
            ['brand_name' => 'ASUS', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/asus.jpg', 'sort_order' => 1],
            ['brand_name' => 'Motorola', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/motorola.png', 'sort_order' => 1],
            ['brand_name' => 'Infinix', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/infinix.png', 'sort_order' => 1],
            ['brand_name' => 'Tecno', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/tecno.jpg', 'sort_order' => 1],
            ['brand_name' => 'iQOO', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/iqoo.jpg', 'sort_order' => 1],
            ['brand_name' => 'Poco', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/poco.png', 'sort_order' => 1],
            ['brand_name' => 'Huawei', 'item_type' => 'hp', 'photo_path' => 'brand-catalog/hp/huawei.png', 'sort_order' => 1],

            // Kamera (tanpa RED)
            ['brand_name' => 'Sony', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/sony.png', 'sort_order' => 1],
            ['brand_name' => 'Canon', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/canon.png', 'sort_order' => 1],
            ['brand_name' => 'GoPro', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/goPro.png', 'sort_order' => 1],
            ['brand_name' => 'Nikon', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/nikon.jpg', 'sort_order' => 1],
            ['brand_name' => 'Fujifilm', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/fujifilm.png', 'sort_order' => 1],
            ['brand_name' => 'DJI', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/dji.png', 'sort_order' => 1],
            ['brand_name' => 'Panasonic', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/panasonic.png', 'sort_order' => 1],
            ['brand_name' => 'Sigma', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/sigma.png', 'sort_order' => 1],
            ['brand_name' => 'Insta360', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/insta360.png', 'sort_order' => 1],
            ['brand_name' => 'OM System', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/OM system.png', 'sort_order' => 1],
            ['brand_name' => 'Leica', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/leica.png', 'sort_order' => 1],
            ['brand_name' => 'Blackmagic', 'item_type' => 'kamera', 'photo_path' => 'brand-catalog/kamera/Blackmagic_Design_logo.png', 'sort_order' => 1],

            // Camping / Tenda (tanpa CAMP)
            ['brand_name' => 'Eiger', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/eiger.png', 'sort_order' => 1],
            ['brand_name' => 'Naturehike', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/naturehike.jpg', 'sort_order' => 1],
            ['brand_name' => 'Consina', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/consina.jpg', 'sort_order' => 1],
            ['brand_name' => 'Jeep', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/jeep.jpg', 'sort_order' => 1],
            ['brand_name' => 'Marmot', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/Marmot-logo.png', 'sort_order' => 1],
            ['brand_name' => 'MSR', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/msr.jpg', 'sort_order' => 1],
            ['brand_name' => 'Coleman', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/coleman.png', 'sort_order' => 1],
            ['brand_name' => 'The North Face', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/the north face.jpg', 'sort_order' => 1],
            ['brand_name' => 'Snugpak', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/snugpak.png', 'sort_order' => 1],
            ['brand_name' => 'Klymit', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/klymit.png', 'sort_order' => 1],
            ['brand_name' => 'Osprey', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/osprey.png', 'sort_order' => 1],
            ['brand_name' => 'Deuter', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/deuter.jpg', 'sort_order' => 1],
            ['brand_name' => 'Gregory', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/gregory.jpg', 'sort_order' => 1],
            ['brand_name' => 'Black Diamond', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/blackdiamond.jpg', 'sort_order' => 1],
            ['brand_name' => 'Petzl', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/petzl.jpg', 'sort_order' => 1],
            ['brand_name' => 'Jetboil', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/Jetboil_logo.jpg', 'sort_order' => 1],
            ['brand_name' => 'Sea to Summit', 'item_type' => 'camping', 'photo_path' => 'brand-catalog/camping/seatosubmit.png', 'sort_order' => 1],

            // Playstation
            ['brand_name' => 'Sony', 'item_type' => 'ps', 'photo_path' => 'brand-catalog/ps/playstation.jpg', 'sort_order' => 1],

            // Drone
            ['brand_name' => 'DJI', 'item_type' => 'drone', 'photo_path' => 'brand-catalog/drone/dji.png', 'sort_order' => 1],

            // Musik
            ['brand_name' => 'Yamaha', 'item_type' => 'musik', 'photo_path' => 'brand-catalog/musik/yamaha.png', 'sort_order' => 1],
            ['brand_name' => 'Fender', 'item_type' => 'musik', 'photo_path' => 'brand-catalog/musik/fender.jpg', 'sort_order' => 1],
            ['brand_name' => 'Roland', 'item_type' => 'musik', 'photo_path' => 'brand-catalog/musik/roland.png', 'sort_order' => 1],
            ['brand_name' => 'Casio', 'item_type' => 'musik', 'photo_path' => 'brand-catalog/musik/casio.png', 'sort_order' => 1],
            ['brand_name' => 'Cort', 'item_type' => 'musik', 'photo_path' => 'brand-catalog/musik/cort.png', 'sort_order' => 1],
        ];

        foreach ($photos as $photo) {
            BrandCatalogPhoto::updateOrCreate(
                ['brand_name' => $photo['brand_name'], 'item_type' => $photo['item_type']],
                [
                    'photo_path' => $photo['photo_path'],
                    'caption' => $photo['caption'] ?? null,
                    'sort_order' => $photo['sort_order'] ?? 0,
                    'is_active' => true,
                ]
            );
        }

        echo "Brand catalog photos seeded: " . count($photos) . " records\n";
    }
}
