<?php

namespace App\Console\Commands;

use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Drone;
use App\Models\MusicalInstrument;
use App\Models\Phone;
use App\Models\Playstation;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AssignKatalogImages extends Command
{
    protected $signature = 'katalog:assign-images {--force : Timpa foto yang sudah ada}';

    protected $description = 'Isi kolom image produk yang kosong dari folder public/images/katalog';

    public function handle(): int
    {
        $map = [
            'mobil' => [Vehicle::class, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', 'mobil'))],
            'motor' => [Vehicle::class, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', 'motor'))],
            'hp' => [Phone::class, fn ($q) => $q],
            'kamera' => [Camera::class, fn ($q) => $q],
            'tenda' => [CampingEquipment::class, fn ($q) => $q],
            'ps' => [Playstation::class, fn ($q) => $q],
            'drone' => [Drone::class, fn ($q) => $q],
            'musik' => [MusicalInstrument::class, fn ($q) => $q],
        ];

        $force = (bool) $this->option('force');
        $total = 0;

        foreach ($map as $slug => [$model, $scope]) {
            $files = collect(File::files(public_path('images/katalog/' . $slug)))
                ->filter(fn ($f) => in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp']))
                ->sortBy(fn ($f) => $f->getFilename())
                ->values();

            if ($files->isEmpty()) {
                $this->warn("  {$slug}: tidak ada foto di public/images/katalog/{$slug} — dilewati");
                continue;
            }

            // Salin ke storage publik agar bisa diakses via asset('storage/...').
            $destDir = storage_path('app/public/katalog/' . $slug);
            if (! File::isDirectory($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }
            foreach ($files as $file) {
                $dest = $destDir . '/' . $file->getFilename();
                if (! File::exists($dest)) {
                    File::copy($file->getPathname(), $dest);
                }
            }

            $query = $scope($model::query());
            if (! $force) {
                $query->where(function ($q) {
                    $q->whereNull('image')->orWhere('image', '');
                });
            }

            $i = 0;
            $count = 0;
            foreach ($query->orderBy('id')->cursor() as $item) {
                $file = $files[$i % $files->count()];
                $item->image = 'katalog/' . $slug . '/' . $file->getFilename();
                $item->save();
                $i++;
                $count++;
            }

            $total += $count;
            $this->line("  {$slug}: <info>{$count}</info> produk diberi foto");
        }

        $this->newLine();
        $this->info("Selesai! Total {$total} produk diberi foto." . ($force ? '' : ' (yang sudah punya foto dilewati; pakai --force untuk timpa)'));

        return self::SUCCESS;
    }
}
