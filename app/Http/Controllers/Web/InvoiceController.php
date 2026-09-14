<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Invoice::with('rental', 'user', 'items');

        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('rental', function ($q2) use ($search) {
                        $q2->where('rental_code', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function show($id)
    {
        $invoice = Invoice::with('rental', 'user', 'items')->findOrFail($id);

        $user = Auth::user();

        if ($user->isUser() && $invoice->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        return view('invoices.show', compact('invoice'));
    }

    public function printInvoice($id)
    {
        $invoice = Invoice::with('rental', 'rental.vehicle', 'rental.driver.user', 'user', 'items')->findOrFail($id);

        $user = Auth::user();

        if ($user->isUser() && $invoice->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        return view('invoices.print', compact('invoice'));
    }

    public function markPaid(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $invoice = Invoice::findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice sudah dibayar.');
        }

        $request->validate([
            'payment_method' => 'required|string|max:100',
        ]);

        try {
            $invoice->update([
                'status'          => 'paid',
                'paid_amount'     => $invoice->total,
                'payment_method'  => $request->payment_method,
                'payment_date'    => now(),
            ]);

            return redirect()->route('invoices.show', $invoice->id)
                ->with('success', 'Invoice berhasil ditandai sebagai dibayar.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status invoice: ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        $user = Auth::user();

        $query = Invoice::query();

        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        }

        $totalInvoices = (clone $query)->count();
        $paidInvoices = (clone $query)->where('status', 'paid')->count();
        $pendingInvoices = (clone $query)->where('status', 'pending')->count();
        $overdueInvoices = (clone $query)->where('status', 'overdue')->count();

        $totalRevenue = (clone $query)->where('status', 'paid')->sum('total');
        $totalPending = (clone $query)->where('status', 'pending')->sum('total');

        $recentInvoices = (clone $query)
            ->with('rental', 'user')
            ->latest()
            ->limit(10)
            ->get();

        $monthlyRevenue = (clone $query)
            ->where('status', 'paid')
            ->where('payment_date', '>=', now()->startOfMonth())
            ->sum('total');

        return view('invoices.dashboard', compact(
            'totalInvoices',
            'paidInvoices',
            'pendingInvoices',
            'overdueInvoices',
            'totalRevenue',
            'totalPending',
            'recentInvoices',
            'monthlyRevenue'
        ));
    }
}
