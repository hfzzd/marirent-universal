<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Playstation;
use App\Models\Drone;
use App\Models\MusicalInstrument;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicController extends Controller
{
    /**
     * Cache profil merchant (toko) per owner id untuk menghindari query berulang.
     */
    private array $merchantCache = [];

    public function store(Request $request, string $slug)
    {
        $merchant = \App\Models\Merchant::where('slug', $slug)
            ->where('is_active', true)
            ->where('status', 'active')
            ->with('owner')
            ->firstOrFail();

        $userId = $merchant->user_id;

        $products = collect();
        $models = [
            Vehicle::class, Phone::class, Camera::class,
            CampingEquipment::class, Playstation::class, Drone::class,
            MusicalInstrument::class,
        ];

        foreach ($models as $modelClass) {
            $type = $this->typeForModel($modelClass);
            $query = $modelClass::with(['category', 'company'])
                ->where('owner_id', $userId)
                ->where('is_active', true)
                ->where('status', 'available');

            if ($request->category && str_starts_with(strtolower($request->category), 'mobil')) {
                $query->whereHas('category', fn($q) => $q->where('slug', 'mobil'));
            }
            if ($request->category && str_starts_with(strtolower($request->category), 'motor')) {
                $query->whereHas('category', fn($q) => $q->where('slug', 'motor'));
            }

            $products = $products->concat(
                $query->get()->map(fn($item) => $this->normalizeCatalogItem($item, $type))
            );
        }

        $products = $products->sortByDesc('id')->values();

        $currentPage = (int) $request->input('page', 1);
        $perPage = 12;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $products->forPage($currentPage, $perPage),
            $products->count(),
            $perPage,
            $currentPage,
            ['path' => route('public.store', $merchant->slug), 'query' => $request->except('page')]
        );

        return view('public.store', [
            'merchant' => $merchant,
            'products' => $paginated,
        ]);
    }

    private function typeForModel(string $modelClass): string
    {
        return match ($modelClass) {
            Vehicle::class => 'vehicle',
            Phone::class => 'phone',
            Camera::class => 'camera',
            CampingEquipment::class => 'camping',
            Playstation::class => 'ps',
            Drone::class => 'drone',
            MusicalInstrument::class => 'musik',
            default => 'item',
        };
    }

    private function normalizeCatalogItem($item, string $type): array
    {
        if ($type === 'vehicle') {
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
                'company' => $item->company?->name,
                'tags' => array_filter([
                    $item->seats ? $item->seats . ' Kursi' : null,
                    ucfirst($item->transmission),
                    $item->with_driver ? 'Driver' : null,
                ]),
                'merchant' => $this->merchantInfo($item->owner_id),
                'link' => route('public.vehicle', $item->slug),
            ];
        }

        $config = $this->getItemConfig($type === 'camping' ? 'tenda' : $type);
        return [
            'type' => $type,
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'brand' => $item->brand,
            'subtitle' => match ($type) {
                'phone' => $item->phone_model,
                'camera' => $item->camera_model,
                'camping' => $item->equipment_model,
                'ps' => $item->console_model,
                'drone' => $item->drone_model,
                'musik' => $item->instrument_model,
                default => '-',
            },
            'daily_price' => (float) $item->daily_price,
            'image' => $item->image,
            'category_slug' => $item->category->slug ?? '',
            'icon' => $config['icon'],
            'tags' => array_filter([$item->color ?? null]),
            'merchant' => $this->merchantInfo($item->owner_id),
            'company' => $item->company?->name,
            'link' => route('public.item', [$this->itemRouteType($type), $item->slug]),
        ];
    }

    private function itemRouteType(string $type): string
    {
        return match ($type) {
            'camping' => 'tenda',
            default => $type,
        };
    }

    private function merchantInfo(?int $ownerId): ?array
    {
        if (!$ownerId) {
            return null;
        }
        if (array_key_exists($ownerId, $this->merchantCache)) {
            return $this->merchantCache[$ownerId];
        }
        $m = \App\Models\Merchant::where('user_id', $ownerId)->where('is_active', true)->first();
        $this->merchantCache[$ownerId] = $m ? [
            'name' => $m->name,
            'slug' => $m->slug,
            'logo' => $m->logo,
            'city' => $m->city,
            'rating' => ($m->getAverageRating() ?: 0),
        ] : null;
        return $this->merchantCache[$ownerId];
    }

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
                'sewa-ps' => Playstation::where('status', 'available')->where('is_active', true)->count(),
                'sewa-drone' => Drone::where('status', 'available')->where('is_active', true)->count(),
                'sewa-alat-musik' => MusicalInstrument::where('status', 'available')->where('is_active', true)->count(),
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
            'ps' => [
                'label' => 'Playstation', 'icon' => 'fa-gamepad', 'slug' => 'ps',
                'color' => 'from-indigo-500 to-blue-700', 'bg' => 'from-indigo-50 to-blue-50',
            ],
            'drone' => [
                'label' => 'Drone', 'icon' => 'fa-drone', 'slug' => 'drone',
                'color' => 'from-cyan-500 to-sky-700', 'bg' => 'from-cyan-50 to-sky-50',
            ],
            'musik' => [
                'label' => 'Alat Musik', 'icon' => 'fa-guitar', 'slug' => 'musik',
                'color' => 'from-rose-500 to-pink-700', 'bg' => 'from-rose-50 to-pink-50',
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
                'ps' => Playstation::class,
                'drone' => Drone::class,
                'musik' => MusicalInstrument::class,
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
        $validTypes = ['mobil', 'motor', 'hp', 'kamera', 'tenda', 'ps', 'drone', 'musik'];

        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $config = match($type) {
            'mobil' => ['model' => Vehicle::class, 'category_slug' => 'mobil', 'label' => 'Mobil', 'icon' => 'fa-car'],
            'motor' => ['model' => Vehicle::class, 'category_slug' => 'motor', 'label' => 'Motor', 'icon' => 'fa-motorcycle'],
            'hp' => ['model' => Phone::class, 'category_slug' => 'sewa-hp', 'label' => 'Handphone', 'icon' => 'fa-mobile-alt'],
            'kamera' => ['model' => Camera::class, 'category_slug' => 'sewa-kamera', 'label' => 'Kamera', 'icon' => 'fa-camera'],
            'tenda' => ['model' => CampingEquipment::class, 'category_slug' => 'sewa-tenda', 'label' => 'Alat Camping', 'icon' => 'fa-campground'],
            'ps' => ['model' => Playstation::class, 'category_slug' => 'sewa-ps', 'label' => 'Playstation', 'icon' => 'fa-gamepad'],
            'drone' => ['model' => Drone::class, 'category_slug' => 'sewa-drone', 'label' => 'Drone', 'icon' => 'fa-drone'],
            'musik' => ['model' => MusicalInstrument::class, 'category_slug' => 'sewa-alat-musik', 'label' => 'Alat Musik', 'icon' => 'fa-guitar'],
        };

        $model = $config['model'];

        $query = $model::with(['category', 'company'])
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
                'company' => $item->company?->name,
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
            'company' => $item->company?->name,
            'subtitle' => match ($type) {
                'hp' => $item->phone_model,
                'kamera' => $item->camera_model,
                'tenda' => $item->equipment_model,
                'ps' => $item->console_model,
                'drone' => $item->drone_model,
                'musik' => $item->instrument_model,
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
                    'ps' => $item->storage_capacity ?? null,
                    'drone' => $item->flight_time ?? null,
                    'musik' => $item->instrument_type ?? null,
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
        $psBrands = Playstation::where('is_active', true)->where('status', 'available')
            ->distinct()->pluck('brand')->filter()->sort()->values();
        $droneBrands = Drone::where('is_active', true)->where('status', 'available')
            ->distinct()->pluck('brand')->filter()->sort()->values();
        $musikBrands = MusicalInstrument::where('is_active', true)->where('status', 'available')
            ->distinct()->pluck('brand')->filter()->sort()->values();

        return [
            'mobil' => $mobilBrands,
            'motor' => $motorBrands,
            'hp' => $hpBrands,
            'kamera' => $cameraBrands,
            'tenda' => $campingBrands,
            'ps' => $psBrands,
            'drone' => $droneBrands,
            'musik' => $musikBrands,
        ];
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function show(string $slug)
    {
        $vehicle = Vehicle::with(['category', 'owner', 'company', 'reviews.user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedVehicles = Vehicle::where('category_id', $vehicle->category_id)
            ->where('id', '!=', $vehicle->id)
            ->where('is_active', true)
            ->where('status', 'available')
            ->limit(4)
            ->get();

        $merchant = $this->merchantInfo($vehicle->owner_id);

        return view('public.show', compact('vehicle', 'relatedVehicles', 'merchant'));
    }

    public function showItem(string $type, string $slug)
    {
        $config = $this->getItemConfig($type);
        $model = $config['model'];
        $item = $model::with(['category', 'company'])->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $related = $model::where('category_id', $item->category_id)
            ->where('id', '!=', $item->id)
            ->where('is_active', true)
            ->where('status', 'available')
            ->limit(4)
            ->get();

        $merchant = $this->merchantInfo($item->owner_id);

        return view('public.show-item', compact('item', 'type', 'config', 'related', 'merchant'));
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
            'ps' => [
                'icon' => 'fa-gamepad',
                'label' => 'Sewa Playstation',
                'model' => Playstation::class,
                'subtitle' => fn($item) => $item->console_model,
                'specs' => fn($item) => array_filter([
                    $item->storage_capacity,
                    $item->controllers_count ? $item->controllers_count . ' Controller' : null,
                    $item->color,
                ]),
            ],
            'drone' => [
                'icon' => 'fa-drone',
                'label' => 'Sewa Drone',
                'model' => Drone::class,
                'subtitle' => fn($item) => $item->drone_model,
                'specs' => fn($item) => array_filter([
                    $item->camera_resolution,
                    $item->flight_time,
                    $item->max_range,
                    $item->weight,
                ]),
            ],
            'musik' => [
                'icon' => 'fa-guitar',
                'label' => 'Sewa Alat Musik',
                'model' => MusicalInstrument::class,
                'subtitle' => fn($item) => $item->instrument_model,
                'specs' => fn($item) => array_filter([
                    ucfirst($item->instrument_type),
                    $item->color,
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

        $vehicleQuery = Vehicle::with(['category', 'company'])
            ->where('is_active', true)
            ->where('status', 'available');
        $phoneQuery = Phone::with(['category', 'company'])
            ->where('is_active', true)
            ->where('status', 'available');
        $cameraQuery = Camera::with(['category', 'company'])
            ->where('is_active', true)
            ->where('status', 'available');
        $campingQuery = CampingEquipment::with(['category', 'company'])
            ->where('is_active', true)
            ->where('status', 'available');
        $psQuery = Playstation::with(['category', 'company'])
            ->where('is_active', true)
            ->where('status', 'available');
        $droneQuery = Drone::with(['category', 'company'])
            ->where('is_active', true)
            ->where('status', 'available');
        $musikQuery = MusicalInstrument::with(['category', 'company'])
            ->where('is_active', true)
            ->where('status', 'available');

        if ($request->category) {
            $vehicleQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
            $phoneQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
            $cameraQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
            $campingQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
            $psQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
            $droneQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
            $musikQuery->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->brand) {
            $vehicleQuery->where('brand', $request->brand);
            $phoneQuery->where('brand', $request->brand);
            $cameraQuery->where('brand', $request->brand);
            $campingQuery->where('brand', $request->brand);
            $psQuery->where('brand', $request->brand);
            $droneQuery->where('brand', $request->brand);
            $musikQuery->where('brand', $request->brand);
        }

        if ($request->model) {
            $vehicleQuery->where('model', $request->model);
            $phoneQuery->where('phone_model', $request->model);
            $cameraQuery->where('camera_model', $request->model);
            $campingQuery->where('equipment_model', $request->model);
            $psQuery->where('console_model', $request->model);
            $droneQuery->where('drone_model', $request->model);
            $musikQuery->where('instrument_model', $request->model);
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
            $psQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)->orWhere('brand', 'like', $search)->orWhere('console_model', 'like', $search);
            });
            $droneQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)->orWhere('brand', 'like', $search)->orWhere('drone_model', 'like', $search);
            });
            $musikQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)->orWhere('brand', 'like', $search)->orWhere('instrument_model', 'like', $search);
            });
        }

        if ($request->max_price) {
            $max = $request->max_price;
            $vehicleQuery->where('daily_price', '<=', $max);
            $phoneQuery->where('daily_price', '<=', $max);
            $cameraQuery->where('daily_price', '<=', $max);
            $campingQuery->where('daily_price', '<=', $max);
            $psQuery->where('daily_price', '<=', $max);
            $droneQuery->where('daily_price', '<=', $max);
            $musikQuery->where('daily_price', '<=', $max);
        }

        $vehicles = $vehicleQuery->get()->map(fn($v) => $this->normalizeVehicle($v));
        $phones = $phoneQuery->get()->map(fn($p) => $this->normalizePhone($p));
        $cameras = $cameraQuery->get()->map(fn($c) => $this->normalizeCamera($c));
        $campings = $campingQuery->get()->map(fn($c) => $this->normalizeCamping($c));
        $psList = $psQuery->get()->map(fn($p) => $this->normalizePs($p));
        $drones = $droneQuery->get()->map(fn($d) => $this->normalizeDrone($d));
        $instruments = $musikQuery->get()->map(fn($m) => $this->normalizeMusik($m));

        $products = $vehicles->concat($phones)->concat($cameras)->concat($campings)
            ->concat($psList)->concat($drones)->concat($instruments);

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
            'company' => $v->company?->name,
            'tags' => array_filter([
                $v->seats ? $v->seats . ' Kursi' : null,
                ucfirst($v->transmission),
                $v->with_driver ? 'Driver' : null,
            ]),
            'rating' => round($v->getAverageRating()),
            'review_count' => $v->reviews->count(),
            'merchant' => $this->merchantInfo($v->owner_id),
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
            'merchant' => $this->merchantInfo($p->owner_id),
            'company' => $p->company?->name,
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
            'merchant' => $this->merchantInfo($c->owner_id),
            'company' => $c->company?->name,
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
            'merchant' => $this->merchantInfo($c->owner_id),
            'company' => $c->company?->name,
            'created_at' => $c->created_at,
        ];
    }

    private function normalizePs(Playstation $p): array
    {
        return [
            'type' => 'ps',
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'brand' => $p->brand,
            'subtitle' => $p->console_model,
            'daily_price' => (float) $p->daily_price,
            'image' => $p->image,
            'category' => $p->category,
            'status' => $p->status,
            'icon' => 'fa-gamepad',
            'icon_color' => 'text-indigo',
            'tags' => array_filter([
                $p->storage_capacity,
                $p->controllers_count ? $p->controllers_count . ' Controller' : null,
                $p->color,
            ]),
            'rating' => round($p->getAverageRating()),
            'review_count' => $p->reviews->count(),
            'merchant' => $this->merchantInfo($p->owner_id),
            'company' => $p->company?->name,
            'created_at' => $p->created_at,
        ];
    }

    private function normalizeDrone(Drone $d): array
    {
        return [
            'type' => 'drone',
            'id' => $d->id,
            'name' => $d->name,
            'slug' => $d->slug,
            'brand' => $d->brand,
            'subtitle' => $d->drone_model,
            'daily_price' => (float) $d->daily_price,
            'image' => $d->image,
            'category' => $d->category,
            'status' => $d->status,
            'icon' => 'fa-drone',
            'icon_color' => 'text-cyan',
            'tags' => array_filter([
                $d->camera_resolution,
                $d->flight_time,
                $d->max_range,
            ]),
            'rating' => round($d->getAverageRating()),
            'review_count' => $d->reviews->count(),
            'merchant' => $this->merchantInfo($d->owner_id),
            'company' => $d->company?->name,
            'created_at' => $d->created_at,
        ];
    }

    private function normalizeMusik(MusicalInstrument $m): array
    {
        return [
            'type' => 'musik',
            'id' => $m->id,
            'name' => $m->name,
            'slug' => $m->slug,
            'brand' => $m->brand,
            'subtitle' => $m->instrument_model,
            'daily_price' => (float) $m->daily_price,
            'image' => $m->image,
            'category' => $m->category,
            'status' => $m->status,
            'icon' => 'fa-guitar',
            'icon_color' => 'text-rose',
            'tags' => array_filter([
                $m->instrument_type ? ucfirst($m->instrument_type) : null,
                $m->color,
            ]),
            'rating' => round($m->getAverageRating()),
            'review_count' => $m->reviews->count(),
            'merchant' => $this->merchantInfo($m->owner_id),
            'company' => $m->company?->name,
            'created_at' => $m->created_at,
        ];
    }
}
