<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $category = Category::all();
        $catProd = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', operator: 10)
            ->first();
        $subCategory = SubCategory::all();
        // dd($subCategory);
        return view('category.index', ["category" => $category, "catProd" => $catProd, "subCategory" => $subCategory]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category.createcategory');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = new Category();
        $data->name = $request->get('name');
        $data->code_category = $request->get('code_category');
        $data->save();
        return redirect()->route('category.index')->with('status', 'Sukses menambahkan data!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $category->name = $request->name;
        $category->code_category = $request->code_category;
        $category->save();
        return redirect()->route('category.index')->with('status', 'Data Berhasil Diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return redirect()->route('category.index')->with('status', 'Data Berhasil Dihapus');
        } catch (\PDOException) {
            $msg = "Failed to deleted data because there are related data with " . $category->name;
            return redirect()->route('category.index')->with('status', $msg);
        }
    }
}
