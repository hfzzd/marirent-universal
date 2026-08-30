<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BrandCatalogPhoto;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Vehicle;
use App\Models\Playstation;
use App\Models\Drone;
use App\Models\MusicalInstrument;

class BrandCatalogPhotoSeeder extends Seeder
{
    public function run(): void
    {
        $count = $this->seedLogos();

        $count += $this->seedProductPhotos();

        echo "Brand catalog photos seeded: {$count} records\n";
    }

    protected function seedLogos(): int
    {
        $logos = [
            'mobil' => [
                'Toyota' => 'Gambar/Mobil/Brand/toyota.png',
                'Honda' => 'Gambar/Mobil/Brand/Honda.png',
                'Daihatsu' => 'Gambar/Mobil/Brand/Daihatsu.png',
                'Suzuki' => 'Gambar/Mobil/Brand/suzuki.png',
                'Mitsubishi' => 'Gambar/Mobil/Brand/Mitsubishi.png',
                'Nissan' => 'Gambar/Mobil/Brand/nissan.png',
                'Hyundai' => 'Gambar/Mobil/Brand/Hyundai.png',
                'Kia' => 'Gambar/Mobil/Brand/kia.png',
                'Mazda' => 'Gambar/Mobil/Brand/mazda.png',
            ],
            'motor' => [
                'Honda' => 'Gambar/Motor/Brand/honda.png',
                'Yamaha' => 'Gambar/Motor/Brand/yamaha.png',
                'Kawasaki' => 'Gambar/Motor/Brand/kawasaki.png',
                'Vespa' => 'Gambar/Motor/Brand/vespa.png',
            ],
            'hp' => [
                'Apple' => 'Gambar/Hp/brand/apple.jpg',
                'Samsung' => 'Gambar/Hp/brand/samsung.jpg',
                'Xiaomi' => 'Gambar/Hp/brand/logo-baru-xiaomi.jpeg',
                'Google' => 'Gambar/Hp/brand/google.png',
                'OnePlus' => 'Gambar/Hp/brand/oneplus.jpg',
                'Realme' => 'Gambar/Hp/brand/realme.png',
                'Oppo' => 'Gambar/Hp/brand/oppo.png',
                'Vivo' => 'Gambar/Hp/brand/vivo.jpeg',
                'Sony' => 'Gambar/Hp/brand/Xperia_logo-580-75.jpg',
                'ASUS' => 'Gambar/Hp/brand/asus.jpg',
                'Motorola' => 'Gambar/Hp/brand/motorola.png',
                'Infinix' => 'Gambar/Hp/brand/infinix.png',
                'Tecno' => 'Gambar/Hp/brand/tecno.jpg',
                'iQOO' => 'Gambar/Hp/brand/iqoo.jpg',
                'Poco' => 'Gambar/Hp/brand/poco.png',
                'Huawei' => 'Gambar/Hp/brand/huawei.png',
            ],
            'kamera' => [
                'Sony' => 'Gambar/Kamera/brand/sony.png',
                'Canon' => 'Gambar/Kamera/brand/canon.png',
                'GoPro' => 'Gambar/Kamera/brand/goPro.png',
                'Nikon' => 'Gambar/Kamera/brand/nikon.jpg',
                'Fujifilm' => 'Gambar/Kamera/brand/fujifilm.png',
                'DJI' => 'Gambar/Kamera/brand/dji.png',
                'Panasonic' => 'Gambar/Kamera/brand/panasonic.png',
                'Sigma' => 'Gambar/Kamera/brand/sigma.png',
                'Insta360' => 'Gambar/Kamera/brand/insta360.png',
                'OM System' => 'Gambar/Kamera/brand/OM system.png',
                'Leica' => 'Gambar/Kamera/brand/leica.png',
                'Blackmagic' => 'Gambar/Kamera/brand/Blackmagic_Design_logo.png',
            ],
            'camping' => [
                'Eiger' => 'Gambar/tenda/brand/eiger.png',
                'Naturehike' => 'Gambar/tenda/brand/naturehike.jpg',
                'Consina' => 'Gambar/tenda/brand/consina.jpg',
                'Jeep' => 'Gambar/tenda/brand/jeep.jpg',
                'Marmot' => 'Gambar/tenda/brand/Marmot-logo.png',
                'MSR' => 'Gambar/tenda/brand/msr.jpg',
                'Coleman' => 'Gambar/tenda/brand/coleman.png',
                'The North Face' => 'Gambar/tenda/brand/the north face.jpg',
                'Snugpak' => 'Gambar/tenda/brand/snugpak.png',
                'Klymit' => 'Gambar/tenda/brand/klymit.png',
                'Osprey' => 'Gambar/tenda/brand/osprey.png',
                'Deuter' => 'Gambar/tenda/brand/deuter.jpg',
                'Gregory' => 'Gambar/tenda/brand/gregory.jpg',
                'Black Diamond' => 'Gambar/tenda/brand/blackdiamond.jpg',
                'Petzl' => 'Gambar/tenda/brand/petzl.jpg',
                'Jetboil' => 'Gambar/tenda/brand/Jetboil_logo.jpg',
                'Sea to Summit' => 'Gambar/tenda/brand/seatosubmit.png',
            ],
            'ps' => [
                'Sony' => 'brand-catalog/ps/playstation.jpg',
            ],
            'drone' => [
                'DJI' => 'brand-catalog/drone/dji.png',
            ],
            'musik' => [
                'Yamaha' => 'Gambar/Musik/Yamaha.png',
                'Fender' => 'Gambar/Musik/Fender.jpg',
                'Roland' => 'Gambar/Musik/Roland.png',
                'Casio' => 'Gambar/Musik/casio.png',
                'Cort' => 'Gambar/Musik/Cort.png',
            ],
        ];

        $count = 0;
        foreach ($logos as $type => $brands) {
            $order = 1;
            foreach ($brands as $brand => $path) {
                BrandCatalogPhoto::updateOrCreate(
                    ['brand_name' => $brand, 'item_type' => $type],
                    ['photo_path' => $path, 'caption' => null, 'sort_order' => $order, 'is_active' => true]
                );
                $count++;
                $order++;
            }
        }

        return $count;
    }

    protected function seedProductPhotos(): int
    {
        $sources = [
            ['type' => 'mobil', 'items' => Vehicle::whereHas('category', fn ($q) => $q->where('slug', 'mobil'))->get(), 'folder' => 'Mobil/Mobil'],
            ['type' => 'motor', 'items' => Vehicle::whereHas('category', fn ($q) => $q->where('slug', 'motor'))->get(), 'folder' => 'Motor/Motor'],
            ['type' => 'hp', 'items' => Phone::all(), 'folder' => 'Hp'],
            ['type' => 'kamera', 'items' => Camera::all(), 'folder' => 'Kamera'],
            ['type' => 'camping', 'items' => CampingEquipment::all(), 'folder' => 'tenda'],
            ['type' => 'ps', 'items' => Playstation::all(), 'folder' => 'Playstation'],
            ['type' => 'drone', 'items' => Drone::all(), 'folder' => 'Drone'],
            ['type' => 'musik', 'items' => MusicalInstrument::all(), 'folder' => 'Musik'],
        ];

        $count = 0;
        $order = 100;
        foreach ($sources as $src) {
            foreach ($src['items'] as $item) {
                $model = $item->model
                    ?? $item->phone_model
                    ?? $item->camera_model
                    ?? $item->equipment_model
                    ?? $item->console_model
                    ?? $item->drone_model
                    ?? $item->instrument_model
                    ?? null;

                $image = SeedMediaHelper::resolve($src['folder'], (string) $item->name, $model, (string) $item->brand);
                if (!$image) {
                    continue;
                }

                $created = BrandCatalogPhoto::firstOrCreate(
                    ['brand_name' => $item->brand, 'item_type' => $src['type'], 'photo_path' => $image],
                    ['sort_order' => $order++, 'is_active' => true]
                );
                if ($created->wasRecentlyCreated) {
                    $count++;
                }
            }
        }

        return $count;
    }
}