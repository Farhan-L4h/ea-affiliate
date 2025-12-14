<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\AffiliatePayout;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Display a listing of sales
     */
    public function index(Request $request)
    {
        $query = Sale::with(['order', 'affiliate.user']);

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product', 'like', "%{$search}%")
                  ->orWhereHas('order', function($q2) use ($search) {
                      $q2->where('order_id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('affiliate', function($q2) use ($search) {
                      $q2->where('ref_code', 'like', "%{$search}%")
                         ->orWhereHas('user', function($q3) use ($search) {
                             $q3->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->where('sale_date', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->where('sale_date', '<=', $request->date_to . ' 23:59:59');
        }

        $sales = $query->latest('sale_date')->paginate(20);

        // Statistics
        $stats = [
            'total' => Sale::count(),
            'total_amount' => Sale::sum('sale_amount'),
            'total_commission' => Sale::sum('commission_amount'),
            'net_revenue' => Sale::sum('sale_amount') - Sale::sum('commission_amount'),
        ];

        return view('admin.sales.index', compact('sales', 'stats'));
    }

    /**
     * Display the specified sale
     */
    public function show($id)
    {
        $sale = Sale::with(['order', 'affiliate.user', 'payouts'])->findOrFail($id);
        
        return view('admin.sales.show', compact('sale'));
    }
}
