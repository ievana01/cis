<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
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
    public function create($id_category)
    {
        $category = Category::where('id_category', $id_category)->first();
        return view('subCategory.create', compact('category'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = new SubCategory();
        $data->name = $request->get('name');
        $data->code_sub_category = $request->get('code_sub_category');
        $data->category_id = $request->get('category_id');
        $data->save();
        return redirect()->route('category.index')->with('status', 'Sukses menambahkan data!');
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
    public function edit(SubCategory $subCategory)
    {
        return view('subCategory.edit', compact("subCategory"));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubCategory $subCategory)
    {
        $subCategory->name = $request->name;
        $subCategory->code_sub_category = $request->code_sub_category;
        $subCategory->save();
        return redirect()->route('category.index')->with('status', 'Data Berhasil Diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subCategory)
    {
        try {
            $subCategory->delete();
            return redirect()->route('category.index')->with('status', 'Data Berhasil Dihapus');
        } catch (\PDOException) {
            $msg = "Failed to deleted data because there are related data with " . $subCategory->name;
            return redirect()->route('category.index')->with('status', $msg);
        }
    }
}
