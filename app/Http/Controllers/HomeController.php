<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
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
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
