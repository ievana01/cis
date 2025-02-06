<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $product = DB::table('products')->count();
        $sales = DB::table('sales_orders')
            ->whereDate('date', Carbon::today()->toDateString()) // Filter berdasarkan tanggal hari ini
            ->sum('total_price');
        $purchase = DB::table('purchase_orders')
            ->whereDate('date', Carbon::today()->toDateString()) // Filter berdasarkan tanggal hari ini
            ->sum('total_purchase');
        // dd($purchase);
        return view('welcome', [
            'product' => $product,
            'sales' => $sales,
            'purchase' => $purchase
        ]);

        // return view('home');
    }
}
