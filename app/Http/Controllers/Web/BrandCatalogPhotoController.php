<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BrandCatalogPhoto;
use App\Models\Vehicle;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Playstation;
use App\Models\Drone;
use App\Models\MusicalInstrument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BrandCatalogPhotoController extends Controller
{
    public function index(Request $request)
    {
        $itemType = $request->item_type;
        $allowedTypes = $this->allowedItemTypes();

        $existingBrands = collect();
        foreach ($this->typeMap() as $type => $model) {
            if (!in_array($type, $allowedTypes, true)) {
                continue;
            }

            $query = $model::withoutGlobalScope(\App\Models\Scopes\MerchantScope::class)
                ->where('is_active', true)
                ->whereNotNull('brand')
                ->where('brand', '!=', '');

            if ($type === 'mobil') {
                $query->whereHas('category', fn($q) => $q->where('slug', 'mobil'));
            } elseif ($type === 'motor') {
                $query->whereHas('category', fn($q) => $q->where('slug', 'motor'));
            }

            $brands = $query->distinct()
                ->pluck('brand')
                ->map(fn($b) => ['brand' => $b, 'item_type' => $type]);
            $existingBrands = $existingBrands->concat($brands);
        }

        $existingBrands = $existingBrands->groupBy('item_type')->map(fn($items) => $items->pluck('brand')->unique()->sort()->values());

        $query = BrandCatalogPhoto::query();
        if ($request->filled('brand')) {
            $query->where('brand_name', $request->brand);
        }
        if ($itemType) {
            $query->where('item_type', $itemType);
        } elseif ($allowedTypes !== array_keys($this->typeMap())) {
            $query->whereIn('item_type', $allowedTypes);
        }

        $photos = $query->orderBy('item_type')->orderBy('brand_name')->orderBy('sort_order')->get();
        $photosByBrand = $photos->groupBy(fn($p) => $p->item_type . '|' . $p->brand_name);

        $allBrands = collect();
        foreach ($existingBrands as $type => $brands) {
            foreach ($brands as $brand) {
                $key = $type . '|' . $brand;
                $allBrands->push([
                    'brand' => $brand,
                    'item_type' => $type,
                    'photos' => $photosByBrand->get($key, collect()),
                ]);
            }
        }

        if ($itemType) {
            $allBrands = $allBrands->where('item_type', $itemType);
        } else {
            $allBrands = $allBrands->whereIn('item_type', $allowedTypes);
        }

        $typeLabels = $this->typeLabels();

        return view('admin.brand-catalog.index', [
            'allBrands' => $allBrands,
            'typeLabels' => $typeLabels,
            'activeType' => $itemType,
        ]);
    }

    private function typeLabels(): array
    {
        return ['mobil' => 'Mobil', 'motor' => 'Motor', 'hp' => 'HP', 'kamera' => 'Kamera', 'camping' => 'Camping', 'ps' => 'Playstation', 'drone' => 'Drone', 'musik' => 'Musik'];
    }

    private function typeMap(): array
    {
        return [
            'mobil' => Vehicle::class,
            'motor' => Vehicle::class,
            'hp' => Phone::class,
            'kamera' => Camera::class,
            'camping' => CampingEquipment::class,
            'ps' => Playstation::class,
            'drone' => Drone::class,
            'musik' => MusicalInstrument::class,
        ];
    }

    private function allowedItemTypes(): array
    {
        if (!Auth::check()) {
            return array_keys($this->typeMap());
        }

        $user = Auth::user();
        if (!$user->isMerchantStaff()) {
            return array_keys($this->typeMap());
        }

        $categoryId = $user->merchantCategoryId();
        if (!$categoryId) {
            return array_keys($this->typeMap());
        }

        return match ($user->merchantCategory?->slug) {
            'mobil' => ['mobil'],
            'motor' => ['motor'],
            'sewa-hp' => ['hp'],
            'sewa-kamera' => ['kamera'],
            'sewa-tenda' => ['camping'],
            'sewa-ps' => ['ps'],
            'sewa-drone' => ['drone'],
            'sewa-alat-musik' => ['musik'],
            default => array_keys($this->typeMap()),
        };
    }

    public function create(Request $request)
    {
        $typeLabels = $this->typeLabels();
        $existingBrands = $this->getExistingBrands();

        return view('admin.brand-catalog.create', [
            'typeLabels' => $typeLabels,
            'existingBrands' => $existingBrands,
            'prefill_brand' => $request->brand,
            'prefill_type' => $request->item_type,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:255',
            'item_type' => 'required|in:mobil,motor,hp,kamera,camping,ps,drone,musik',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'caption' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (!in_array($validated['item_type'], $this->allowedItemTypes(), true)) {
            abort(403, 'Kategori tidak sesuai dengan hak akses Anda');
        }

        $photoPath = $request->file('photo')->store('brand-catalog', 'public');

        BrandCatalogPhoto::create([
            'brand_name' => $validated['brand_name'],
            'item_type' => $validated['item_type'],
            'photo_path' => $photoPath,
            'caption' => $validated['caption'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.brand-catalog.index')->with('success', 'Foto katalog berhasil ditambahkan');
    }

    public function edit(BrandCatalogPhoto $brandCatalogPhoto)
    {
        $typeLabels = $this->typeLabels();
        $existingBrands = $this->getExistingBrands();

        return view('admin.brand-catalog.edit', [
            'photo' => $brandCatalogPhoto,
            'typeLabels' => $typeLabels,
            'existingBrands' => $existingBrands,
        ]);
    }

    public function update(Request $request, BrandCatalogPhoto $brandCatalogPhoto)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:255',
            'item_type' => 'required|in:mobil,motor,hp,kamera,camping,ps,drone,musik',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'caption' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if (!in_array($validated['item_type'], $this->allowedItemTypes(), true)) {
            abort(403, 'Kategori tidak sesuai dengan hak akses Anda');
        }

        $data = [
            'brand_name' => $validated['brand_name'],
            'item_type' => $validated['item_type'],
            'caption' => $validated['caption'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ];

        if ($request->hasFile('photo')) {
            if ($brandCatalogPhoto->photo_path) {
                Storage::disk('public')->delete($brandCatalogPhoto->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('brand-catalog', 'public');
        }

        $brandCatalogPhoto->update($data);

        return redirect()->route('admin.brand-catalog.index')->with('success', 'Foto katalog berhasil diupdate');
    }

    public function destroy(BrandCatalogPhoto $brandCatalogPhoto)
    {
        if ($brandCatalogPhoto->photo_path) {
            Storage::disk('public')->delete($brandCatalogPhoto->photo_path);
        }

        $brandCatalogPhoto->delete();

        return back()->with('success', 'Foto katalog berhasil dihapus');
    }

    public function toggleActive(BrandCatalogPhoto $brandCatalogPhoto)
    {
        $brandCatalogPhoto->update(['is_active' => !$brandCatalogPhoto->is_active]);

        return back()->with('success', 'Status foto katalog berhasil diubah');
    }

    public function destroyBrand(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:255',
            'item_type' => 'required|in:mobil,motor,hp,kamera,camping,ps,drone,musik',
        ]);

        if (!in_array($validated['item_type'], $this->allowedItemTypes(), true)) {
            abort(403, 'Kategori tidak sesuai dengan hak akses Anda');
        }

        $photos = BrandCatalogPhoto::where('brand_name', $validated['brand_name'])
            ->where('item_type', $validated['item_type'])
            ->get();

        foreach ($photos as $photo) {
            if ($photo->photo_path) {
                Storage::disk('public')->delete($photo->photo_path);
            }
            $photo->delete();
        }

        return back()->with('success', 'Katalog brand "' . $validated['brand_name'] . '" berhasil dihapus (' . $photos->count() . ' foto)');
    }

    protected function getExistingBrands(): array
    {
        $allowedTypes = $this->allowedItemTypes();

        $result = [];
        foreach ($this->typeMap() as $type => $model) {
            if (!in_array($type, $allowedTypes, true)) {
                $result[$type] = [];
                continue;
            }

            $query = $model::where('is_active', true)
                ->whereNotNull('brand')
                ->where('brand', '!=', '');

            if ($type === 'mobil') {
                $query->whereHas('category', fn($q) => $q->where('slug', 'mobil'));
            } elseif ($type === 'motor') {
                $query->whereHas('category', fn($q) => $q->where('slug', 'motor'));
            }

            $result[$type] = $query->distinct()
                ->pluck('brand')
                ->sort()
                ->values()
                ->toArray();
        }
        return $result;
    }
}
