<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Affiliate;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'affiliate']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by order_id or telegram_username
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhere('telegram_username', 'like', "%{$search}%")
                  ->orWhere('telegram_chat_id', 'like', "%{$search}%")
                  ->orWhereHas('affiliate', function($q2) use ($search) {
                      $q2->where('ref_code', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $orders = $query->latest()->paginate(20);

        // Statistics
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'paid' => Order::where('status', 'paid')->count(),
            'expired' => Order::where('status', 'expired')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('status', 'paid')->sum('base_amount'),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $order = Order::with(['user', 'affiliate.user', 'sale.payouts'])->findOrFail($id);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order status manually
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,expired,cancelled',
        ]);

        $order = Order::findOrFail($id);
        
        $oldStatus = $order->status;
        $order->status = $request->status;
        
        if ($request->status === 'paid' && $oldStatus !== 'paid') {
            $order->paid_at = now();
        }
        
        $order->save();

        return redirect()->back()->with('success', 'Status order berhasil diupdate!');
    }

    /**
     * Delete order
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        
        // Only allow delete if not paid
        if ($order->status === 'paid') {
            return redirect()->back()->with('error', 'Tidak bisa menghapus order yang sudah dibayar!');
        }

        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order berhasil dihapus!');
    }
}
