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

        return view('pages.products', ['products' => $products]);
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

        $products = $products->sortByDesc('id')->values();

        $currentPage = $request->input('page', 1);
        $perPage = 12;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $products->forPage($currentPage, $perPage),
            $products->count(),
            $perPage,
            $currentPage,
            $request->query()
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
