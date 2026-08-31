<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerRevenueController extends Controller
{
    public function index(Request $request)
    {
        $owner = Auth::user();
        $categories = Category::all();

        $query = Invoice::where('owner_id', $owner->id)
            ->where('type', 'manual_income')
            ->with(['category', 'user', 'items']);

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(15);

        $revenuePerCategory = [];
        foreach ($categories as $cat) {
            $revenuePerCategory[$cat->id] = [
                'name' => $cat->name,
                'slug' => $cat->slug,
                'total' => (float) Invoice::where('owner_id', $owner->id)
                    ->where('type', 'manual_income')
                    ->where('category_id', $cat->id)
                    ->where('status', 'paid')
                    ->sum('total_amount'),
                'pending' => (float) Invoice::where('owner_id', $owner->id)
                    ->where('type', 'manual_income')
                    ->where('category_id', $cat->id)
                    ->where('status', '!=', 'paid')
                    ->sum('due_amount'),
            ];
        }

        $totalRevenue = collect($revenuePerCategory)->sum('total');
        $totalPending = collect($revenuePerCategory)->sum('pending');

        $platformFee = Invoice::where('owner_id', $owner->id)
            ->where('status', 'paid')
            ->sum('platform_fee');
        $netRevenue = Invoice::where('owner_id', $owner->id)
            ->where('status', 'paid')
            ->sum('merchant_revenue');

        return view('owner.revenue.index', compact('invoices', 'categories', 'revenuePerCategory', 'totalRevenue', 'totalPending', 'platformFee', 'netRevenue'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('owner.revenue.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:1',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:2000',
        ]);

        $subtotal = $validated['amount'];
        $taxPercent = $validated['tax_percent'] ?? 0;
        $taxAmount = round($subtotal * $taxPercent / 100, 2);
        $discountAmount = $validated['discount_amount'] ?? 0;
        $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);

        $owner = Auth::user();
        $category = Category::find($validated['category_id']);

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('manual_income'),
            'booking_id' => null,
            'user_id' => $owner->id,
            'owner_id' => $owner->id,
            'category_id' => $validated['category_id'],
            'type' => 'manual_income',
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'due_amount' => $totalAmount,
            'status' => 'draft',
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => "[{$category->name}] {$validated['description']}",
            'quantity' => 1,
            'unit_price' => $subtotal,
            'total_price' => $subtotal,
        ]);

        return redirect()->route('owner.revenue.show', $invoice)->with('success', 'Pendapatan manual berhasil dicatat.');
    }

    public function show(Invoice $invoice)
    {
        abort_unless($invoice->owner_id === Auth::id(), 403);
        $invoice->load(['category', 'user', 'items', 'payments']);
        return view('owner.revenue.show', compact('invoice'));
    }

    public function destroy(Invoice $invoice)
    {
        abort_unless($invoice->owner_id === Auth::id(), 403);
        abort_unless($invoice->status === 'draft', 403);

        $invoice->delete();
        return redirect()->route('owner.revenue.index')->with('success', 'Pendapatan berhasil dihapus.');
    }
}
