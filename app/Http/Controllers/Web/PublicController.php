<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)->get();
        $catCounts = [];
        foreach ($categories as $cat) {
            $catCounts[$cat->slug] = match($cat->slug) {
                'mobil', 'motor' => Vehicle::where('category_id', $cat->id)->where('status', 'available')->where('is_active', true)->count(),
                'sewa-hp' => Phone::where('status', 'available')->where('is_active', true)->count(),
                'sewa-kamera' => Camera::where('status', 'available')->where('is_active', true)->count(),
                'sewa-tenda' => CampingEquipment::where('status', 'available')->where('is_active', true)->count(),
                default => 0,
            };
        }

        $allProducts = $this->getAllProducts($request, 6);
        $featuredVehicles = $allProducts->random(min(6, $allProducts->count()));

        return view('public.index', compact('categories', 'catCounts', 'featuredVehicles'));
    }

    public function about()
    {
        return view('pages.about');
    }

    public function products(Request $request)
    {
        $products = $this->getAllProducts($request, 12);
        $brands = $this->getBrands();

        return view('pages.products', ['products' => $products, 'brands' => $brands]);
    }

    public function brands()
    {
        $hiddenBrands = ['hp' => ['Nothing'], 'kamera' => ['RED'], 'tenda' => ['CAMP']];

        $categories = [
            'mobil' => [
                'label' => 'Mobil', 'icon' => 'fa-car', 'slug' => 'mobil',
                'color' => 'from-sky-500 to-blue-600', 'bg' => 'from-sky-50 to-blue-50',
            ],
            'motor' => [
                'label' => 'Motor', 'icon' => 'fa-motorcycle', 'slug' => 'motor',
                'color' => 'from-amber-500 to-orange-600', 'bg' => 'from-amber-50 to-orange-50',
            ],
            'hp' => [
                'label' => 'Handphone', 'icon' => 'fa-mobile-alt', 'slug' => 'hp',
                'color' => 'from-blue-500 to-indigo-600', 'bg' => 'from-blue-50 to-indigo-50',
            ],
            'kamera' => [
                'label' => 'Kamera', 'icon' => 'fa-camera', 'slug' => 'kamera',
                'color' => 'from-violet-500 to-purple-600', 'bg' => 'from-violet-50 to-purple-50',
            ],
            'tenda' => [
                'label' => 'Alat Camping', 'icon' => 'fa-campground', 'slug' => 'tenda',
                'color' => 'from-emerald-500 to-teal-600', 'bg' => 'from-emerald-50 to-teal-50',
            ],
        ];

        $brandData = [];
        $catalogPhotos = \App\Models\BrandCatalogPhoto::where('is_active', true)
            ->orderBy('item_type')
            ->orderBy('brand_name')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('item_type');

        foreach ($categories as $typeKey => $catConfig) {
            $modelClass = match($typeKey) {
                'mobil', 'motor' => Vehicle::class,
                'hp' => Phone::class,
                'kamera' => Camera::class,
                'tenda' => CampingEquipment::class,
            };

            $query = $modelClass::where('is_active', true)->where('status', 'available');

            if ($typeKey === 'mobil' || $typeKey === 'motor') {
                $query->whereHas('category', fn($q) => $q->where('slug', $catConfig['slug']));
            }

            $brands = $query->selectRaw('brand, COUNT(*) as item_count, MIN(daily_price) as min_price')
                ->whereNotNull('brand')
                ->where('brand', '!=', '')
                ->groupBy('brand')
                ->orderBy('brand')
                ->get();

            $brands = $brands->reject(function ($b) use ($typeKey, $hiddenBrands) {
                return in_array(strtolower($b->brand), array_map('strtolower', $hiddenBrands[$typeKey] ?? []));
            })->values();

            if ($brands->isNotEmpty()) {
                $itemType = $typeKey === 'tenda' ? 'camping' : $typeKey;
                $photosForType = $catalogPhotos->get($itemType, collect())->groupBy('brand_name');
                $brandData[$typeKey] = [
                    'config' => $catConfig,
                    'brands' => $brands,
                    'photos' => $photosForType,
                ];
            }
        }

        return view('pages.brands', ['brandData' => $brandData]);
    }

    public function brand(Request $request, string $type, string $brand)
    {
        $brandDecoded = urldecode($brand);
        $validTypes = ['mobil', 'motor', 'hp', 'kamera', 'tenda'];

        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $config = match($type) {
            'mobil' => ['model' => Vehicle::class, 'category_slug' => 'mobil', 'label' => 'Mobil', 'icon' => 'fa-car'],
            'motor' => ['model' => Vehicle::class, 'category_slug' => 'motor', 'label' => 'Motor', 'icon' => 'fa-motorcycle'],
            'hp' => ['model' => Phone::class, 'category_slug' => 'sewa-hp', 'label' => 'Handphone', 'icon' => 'fa-mobile-alt'],
            'kamera' => ['model' => Camera::class, 'category_slug' => 'sewa-kamera', 'label' => 'Kamera', 'icon' => 'fa-camera'],
            'tenda' => ['model' => CampingEquipment::class, 'category_slug' => 'sewa-tenda', 'label' => 'Alat Camping', 'icon' => 'fa-campground'],
        };

        $model = $config['model'];

        $query = $model::with('category')
            ->where('brand', $brandDecoded)
            ->where('is_active', true)
            ->where('status', 'available');

        if ($type === 'mobil' || $type === 'motor') {
            $query->whereHas('category', fn($q) => $q->where('slug', $config['category_slug']));
        }

        $products = $query->orderBy('daily_price')->get()->map(fn($item) => $this->normalizeItem($item, $type));

        $otherBrands = $model::where('brand', '!=', $brandDecoded)
            ->where('is_active', true)
            ->where('status', 'available')
            ->distinct()
            ->pluck('brand')
            ->filter()
            ->sort()
            ->values()
            ->take(8);

        $totalProducts = $products->count();

        return view('pages.brand', [
            'products' => $products,
            'brand' => $brandDecoded,
            'type' => $type,
            'config' => $config,
            'otherBrands' => $otherBrands,
            'totalProducts' => $totalProducts,
        ]);
    }

    private function normalizeItem($item, string $type): array
    {
        if ($type === 'mobil' || $type === 'motor') {
            return [
                'type' => 'vehicle',
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'brand' => $item->brand,
                'subtitle' => $item->model . ' ' . $item->year,
                'daily_price' => (float) $item->daily_price,
                'image' => $item->image,
                'category_slug' => $item->category->slug ?? '',
                'icon' => ($item->category->slug ?? '') == 'motor' ? 'fa-motorcycle' : 'fa-car',
                'tags' => array_filter([
                    $item->seats ? $item->seats . ' Kursi' : null,
                    $item->year,
                    ucfirst($item->transmission),
                    $item->with_driver ? 'Driver' : null,
                ]),
                'link' => route('public.vehicle', $item->slug),
            ];
        }

        return [
            'type' => $type,
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'brand' => $item->brand,
            'subtitle' => match ($type) {
                'hp' => $item->phone_model,
                'kamera' => $item->camera_model,
                'tenda' => $item->equipment_model,
                default => '-',
            },
            'daily_price' => (float) $item->daily_price,
            'image' => $item->image,
            'category_slug' => $item->category->slug ?? '',
            'icon' => $this->getItemConfig($type)['icon'],
            'tags' => array_filter([
                $item->color ?? null,
                match ($type) {
                    'hp' => $item->storage_gb ? $item->storage_gb . 'GB' : null,
                    'kamera' => $item->sensor_type ?? null,
                    'tenda' => $item->equipment_type ?? null,
                    default => null,
                },
            ]),
            'link' => route('public.item', [$type, $item->slug]),
        ];
    }

    public function getBrands(): array
    {
        $mobilBrands = Vehicle::where('is_active', true)->where('status', 'available')
            ->whereHas('category', fn($q) => $q->where('slug', 'mobil'))
            ->distinct()->pluck('brand')->filter()->sort()->values();
        $motorBrands = Vehicle::where('is_active', true)->where('status', 'available')
            ->whereHas('category', fn($q) => $q->where('slug', 'motor'))
            ->distinct()->pluck('brand')->filter()->sort()->values();
        $hpBrands = Phone::where('is_active', true)->where('status', 'available')
            ->distinct()->pluck('brand')->filter()->sort()->values();
        $cameraBrands = Camera::where('is_active', true)->where('status', 'available')
            ->distinct()->pluck('brand')->filter()->sort()->values();
        $campingBrands = CampingEquipment::where('is_active', true)->where('status', 'available')
            ->distinct()->pluck('brand')->filter()->sort()->values();

        return [
            'mobil' => $mobilBrands,
            'motor' => $motorBrands,
            'hp' => $hpBrands,
            'kamera' => $cameraBrands,
            'tenda' => $campingBrands,
        ];
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function show(string $slug)
    {
        $vehicle = Vehicle::with(['category', 'owner', 'reviews.user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedVehicles = Vehicle::where('category_id', $vehicle->category_id)
            ->where('id', '!=', $vehicle->id)
            ->where('is_active', true)
            ->where('status', 'available')
            ->limit(4)
            ->get();

        return view('public.show', compact('vehicle', 'relatedVehicles'));
    }

    public function showItem(string $type, string $slug)
    {
        $config = $this->getItemConfig($type);
        $model = $config['model'];
        $item = $model::with('category')->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $related = $model::where('category_id', $item->category_id)
            ->where('id', '!=', $item->id)
            ->where('is_active', true)
            ->where('status', 'available')
            ->limit(4)
            ->get();

        return view('public.show-item', compact('item', 'type', 'config', 'related'));
    }

    private function getItemConfig(string $type): array
    {
        return match($type) {
            'hp' => [
                'icon' => 'fa-mobile-alt',
                'label' => 'Sewa HP',
                'model' => Phone::class,
                'subtitle' => fn($item) => $item->phone_model,
                'specs' => fn($item) => array_filter([
                    $item->storage_capacity,
                    $item->ram ? $item->ram . ' RAM' : null,
                    $item->color,
                    $item->screen_size ? $item->screen_size . '"' : null,
                    $item->battery_capacity ? $item->battery_capacity . ' mAh' : null,
                ]),
            ],
            'kamera' => [
                'icon' => 'fa-camera',
                'label' => 'Sewa Kamera',
                'model' => Camera::class,
                'subtitle' => fn($item) => $item->camera_model,
                'specs' => fn($item) => array_filter([
                    $item->sensor_size,
                    $item->lens_included != 'Body Only' ? $item->lens_included : null,
                    $item->resolution,
                    $item->video_resolution,
                    $item->weight,
                ]),
            ],
            'tenda' => [
                'icon' => 'fa-campground',
                'label' => 'Sewa Alat Camping',
                'model' => CampingEquipment::class,
                'subtitle' => fn($item) => $item->equipment_model,
                'specs' => fn($item) => array_filter([
                    $item->type,
                    $item->capacity ? $item->capacity . ' Orang' : null,
                    $item->weight,
                    $item->material,
                ]),
            ],
            default => [
                'icon' => 'fa-box',
                'label' => 'Sewa Barang',
                'model' => Phone::class,
                'subtitle' => fn($item) => '-',
                'specs' => fn($item) => [],
            ],
        };
    }

    public function getAllProducts(Request $request, int $limit = 12)
    {
        $products = collect();

        $vehicleQuery = Vehicle::with('category')
            ->where('is_active', true)
            ->where('status', 'available');
        $phoneQuery = Phone::with('category')
            ->where('is_active', true)
            ->where('status', 'available');
        $cameraQuery = Camera::with('category')
            ->where('is_active', true)
            ->where('status', 'available');
        $campingQuery = CampingEquipment::with('category')
            ->where('is_active', true)
            ->where('status', 'available');

        if ($request->category) {
            $vehicleQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
            $phoneQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
            $cameraQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
            $campingQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->brand) {
            $vehicleQuery->where('brand', $request->brand);
            $phoneQuery->where('brand', $request->brand);
            $cameraQuery->where('brand', $request->brand);
            $campingQuery->where('brand', $request->brand);
        }

        if ($request->model) {
            $vehicleQuery->where('model', $request->model);
            $phoneQuery->where('phone_model', $request->model);
            $cameraQuery->where('camera_model', $request->model);
            $campingQuery->where('equipment_model', $request->model);
        }

        if ($request->search) {
            $search = "%{$request->search}%";
            $vehicleQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)->orWhere('brand', 'like', $search)->orWhere('model', 'like', $search);
            });
            $phoneQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)->orWhere('brand', 'like', $search)->orWhere('phone_model', 'like', $search);
            });
            $cameraQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)->orWhere('brand', 'like', $search)->orWhere('camera_model', 'like', $search);
            });
            $campingQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)->orWhere('brand', 'like', $search)->orWhere('equipment_model', 'like', $search);
            });
        }

        if ($request->max_price) {
            $max = $request->max_price;
            $vehicleQuery->where('daily_price', '<=', $max);
            $phoneQuery->where('daily_price', '<=', $max);
            $cameraQuery->where('daily_price', '<=', $max);
            $campingQuery->where('daily_price', '<=', $max);
        }

        $vehicles = $vehicleQuery->get()->map(fn($v) => $this->normalizeVehicle($v));
        $phones = $phoneQuery->get()->map(fn($p) => $this->normalizePhone($p));
        $cameras = $cameraQuery->get()->map(fn($c) => $this->normalizeCamera($c));
        $campings = $campingQuery->get()->map(fn($c) => $this->normalizeCamping($c));

        $products = $vehicles->concat($phones)->concat($cameras)->concat($campings);

        $sort = $request->input('sort', 'newest');
        $products = match($sort) {
            'price_asc' => $products->sortBy('daily_price')->values(),
            'price_desc' => $products->sortByDesc('daily_price')->values(),
            'name' => $products->sortBy('name')->values(),
            default => $products->sortByDesc('id')->values(),
        };

        $currentPage = (int) $request->input('page', 1);
        $perPage = 12;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $products->forPage($currentPage, $perPage),
            $products->count(),
            $perPage,
            $currentPage,
            [
                'path' => route('products'),
                'query' => $request->except('page'),
            ]
        );

        return $paginated;
    }

    private function normalizeVehicle(Vehicle $v): array
    {
        return [
            'type' => 'vehicle',
            'id' => $v->id,
            'name' => $v->name,
            'slug' => $v->slug,
            'brand' => $v->brand,
            'subtitle' => $v->model . ' ' . $v->year,
            'daily_price' => (float) $v->daily_price,
            'image' => $v->image,
            'category' => $v->category,
            'status' => $v->status,
            'icon' => $v->category->slug == 'motor' ? 'fa-motorcycle' : 'fa-car',
            'icon_color' => $v->category->slug == 'motor' ? 'text-amber' : 'text-sky',
            'tags' => array_filter([
                $v->seats ? $v->seats . ' Kursi' : null,
                ucfirst($v->transmission),
                $v->with_driver ? 'Driver' : null,
            ]),
            'rating' => round($v->getAverageRating()),
            'review_count' => $v->reviews->count(),
            'created_at' => $v->created_at,
        ];
    }

    private function normalizePhone(Phone $p): array
    {
        return [
            'type' => 'phone',
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'brand' => $p->brand,
            'subtitle' => $p->phone_model,
            'daily_price' => (float) $p->daily_price,
            'image' => $p->image,
            'category' => $p->category,
            'status' => $p->status,
            'icon' => 'fa-mobile-alt',
            'icon_color' => 'text-blue',
            'tags' => array_filter([
                $p->storage_capacity,
                $p->ram . ' RAM',
                $p->color,
            ]),
            'rating' => round($p->getAverageRating()),
            'review_count' => $p->reviews->count(),
            'created_at' => $p->created_at,
        ];
    }

    private function normalizeCamera(Camera $c): array
    {
        return [
            'type' => 'camera',
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'brand' => $c->brand,
            'subtitle' => $c->camera_model,
            'daily_price' => (float) $c->daily_price,
            'image' => $c->image,
            'category' => $c->category,
            'status' => $c->status,
            'icon' => 'fa-camera',
            'icon_color' => 'text-violet',
            'tags' => array_filter([
                $c->sensor_size,
                $c->lens_included != 'Body Only' ? $c->lens_included : null,
            ]),
            'rating' => round($c->getAverageRating()),
            'review_count' => $c->reviews->count(),
            'created_at' => $c->created_at,
        ];
    }

    private function normalizeCamping(CampingEquipment $c): array
    {
        return [
            'type' => 'camping',
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'brand' => $c->brand,
            'subtitle' => $c->equipment_model,
            'daily_price' => (float) $c->daily_price,
            'image' => $c->image,
            'category' => $c->category,
            'status' => $c->status,
            'icon' => 'fa-campground',
            'icon_color' => 'text-emerald',
            'tags' => array_filter([
                $c->type,
                $c->capacity ? $c->capacity . ' Orang' : null,
                $c->weight,
            ]),
            'rating' => round($c->getAverageRating()),
            'review_count' => $c->reviews->count(),
            'created_at' => $c->created_at,
        ];
    }
}
