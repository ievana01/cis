<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class SeasonDisountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'required|array',
            'season_value' => 'required|array'
        ]);

        // Simpan data utama
        $seasonDiscountId = DB::table('season_discount')->insertGetId([
            'name' => $request->input('name'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'detail_configuration_id' => 9
        ]);

        // Simpan kategori dan besar diskon
        foreach ($request->category_id as $index => $categoryId) {
            DB::table('season_discount_has_categories')->insert([
                'season_discount_id' => $seasonDiscountId,
                'category_id' => $categoryId,
                'season_value' => $request->season_value[$index] ?? 0,
                'deleted_at'=>null
            ]);
        }

        return redirect()->route('sales.configuration')->with('status', 'Sukses menambahkan diskon musim!');
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
