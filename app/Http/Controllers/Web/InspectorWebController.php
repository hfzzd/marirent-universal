<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InspectorWebController extends Controller
{
    private function authorizeManage(): void
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Hanya superadmin dan owner yang dapat mengelola akun inspektur.');
        }
    }

    private function guardInspector(User $inspector): void
    {
        if (Auth::user()->isOwner() && (int) $inspector->owner_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke inspektur ini.');
        }
    }

    private function currentCompanyList(): \Illuminate\Support\Collection
    {
        if (Auth::user()->isSuperAdmin()) {
            return Company::where('is_active', true)->orderBy('name')->get();
        }
        return collect([$this->resolveOwnCompany()])->filter();
    }

    private function resolveOwnCompany(): ?Company
    {
        return Company::where('user_id', Auth::id())->first();
    }

    public function index(Request $request)
    {
        $this->authorizeManage();

        $query = User::with(['merchant.company'])
            ->where('role', 'inspector');

        if (Auth::user()->isOwner()) {
            $query->where('owner_id', Auth::id());
        } elseif ($request->filled('company_id')) {
            $ownerId = Company::where('id', $request->company_id)->value('user_id');
            $query->where('owner_id', $ownerId);
        }

        if ($request->search) {
            $search = "%{$request->search}%";
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('phone', 'like', $search);
            });
        }

        $inspectors = $query->latest()->paginate(15)->withQueryString();
        $companies = Auth::user()->isSuperAdmin() ? Company::where('is_active', true)->orderBy('name')->get() : collect();

        return view('inspectors.index', compact('inspectors', 'companies'));
    }

    public function create()
    {
        $this->authorizeManage();

        $companies = $this->currentCompanyList();
        $isSuperadmin = Auth::user()->isSuperAdmin();

        return view('inspectors.create', compact('companies', 'isSuperadmin'));
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $company = $this->resolveCompanyFromRequest($request);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'role' => 'inspector',
            'owner_id' => $company?->user_id ?? Auth::id(),
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        $companyName = $company?->name ?? 'tanpa company';
        return redirect()->route('inspectors.index')->with('success', "Akun inspektur {$validated['name']} berhasil ditambahkan ke {$companyName}.");
    }

    public function edit(User $inspector)
    {
        $this->authorizeManage();
        $this->guardInspector($inspector);

        $inspector->load(['merchant.company']);
        $companies = $this->currentCompanyList();
        $isSuperadmin = Auth::user()->isSuperAdmin();

        return view('inspectors.edit', compact('inspector', 'companies', 'isSuperadmin'));
    }

    public function update(Request $request, User $inspector)
    {
        $this->authorizeManage();
        $this->guardInspector($inspector);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        $company = null;
        if (Auth::user()->isSuperAdmin()) {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
            ]);
            $company = Company::findOrFail($request->company_id);
        } else {
            $company = $this->resolveOwnCompany() ?? $inspector->owner->company ?? null;
        }

        $data = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'owner_id' => $company?->user_id ?? $inspector->owner_id,
            'is_active' => $request->boolean('is_active', $inspector->is_active),
        ];
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $inspector->update($data);

        return redirect()->route('inspectors.index')->with('success', "Akun inspektur {$inspector->name} berhasil diperbarui.");
    }

    public function destroy(User $inspector)
    {
        $this->authorizeManage();
        $this->guardInspector($inspector);

        $name = $inspector->name ?? ('#' . $inspector->id);
        $inspector->delete();

        return redirect()->route('inspectors.index')->with('success', "Akun inspektur {$name} berhasil dihapus.");
    }

    private function resolveCompanyFromRequest(Request $request): ?Company
    {
        if (Auth::user()->isSuperAdmin()) {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
            ]);
            return Company::findOrFail($request->company_id);
        }

        return $this->resolveOwnCompany();
    }
}