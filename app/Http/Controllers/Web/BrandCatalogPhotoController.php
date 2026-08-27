<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BrandCatalogPhoto;
use App\Models\Vehicle;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandCatalogPhotoController extends Controller
{
    public function index(Request $request)
    {
        $itemType = $request->item_type;

        $typeMap = [
            'mobil' => Vehicle::class,
            'motor' => Vehicle::class,
            'hp' => Phone::class,
            'kamera' => Camera::class,
            'camping' => CampingEquipment::class,
        ];

        $hiddenBrands = ['hp' => ['Nothing'], 'kamera' => ['RED'], 'camping' => ['CAMP']];

        $existingBrands = collect();
        foreach ($typeMap as $type => $model) {
            $query = $model::where('is_active', true)
                ->whereNotNull('brand')
                ->where('brand', '!=', '');

            if ($type === 'mobil') {
                $query->whereHas('category', fn($q) => $q->where('slug', 'mobil'));
            } elseif ($type === 'motor') {
                $query->whereHas('category', fn($q) => $q->where('slug', 'motor'));
            }

            $brands = $query->distinct()
                ->pluck('brand')
                ->reject(fn($b) => in_array(strtolower($b), array_map('strtolower', $hiddenBrands[$type] ?? [])))
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
        }

        $typeLabels = ['mobil' => 'Mobil', 'motor' => 'Motor', 'hp' => 'HP', 'kamera' => 'Kamera', 'camping' => 'Camping'];

        return view('admin.brand-catalog.index', [
            'allBrands' => $allBrands,
            'typeLabels' => $typeLabels,
            'activeType' => $itemType,
        ]);
    }

    public function create(Request $request)
    {
        $typeLabels = ['mobil' => 'Mobil', 'motor' => 'Motor', 'hp' => 'HP', 'kamera' => 'Kamera', 'camping' => 'Camping'];
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
            'item_type' => 'required|in:mobil,motor,hp,kamera,camping',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'caption' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

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
        $typeLabels = ['mobil' => 'Mobil', 'motor' => 'Motor', 'hp' => 'HP', 'kamera' => 'Kamera', 'camping' => 'Camping'];
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
            'item_type' => 'required|in:mobil,motor,hp,kamera,camping',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'caption' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

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
            'item_type' => 'required|in:mobil,motor,hp,kamera,camping',
        ]);

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
        $typeMap = [
            'mobil' => Vehicle::class,
            'motor' => Vehicle::class,
            'hp' => Phone::class,
            'kamera' => Camera::class,
            'camping' => CampingEquipment::class,
        ];

        $hiddenBrands = ['hp' => ['Nothing'], 'kamera' => ['RED'], 'camping' => ['CAMP']];

        $result = [];
        foreach ($typeMap as $type => $model) {
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
                ->reject(fn($b) => in_array(strtolower($b), array_map('strtolower', $hiddenBrands[$type] ?? [])))
                ->sort()
                ->values()
                ->toArray();
        }
        return $result;
    }
}
