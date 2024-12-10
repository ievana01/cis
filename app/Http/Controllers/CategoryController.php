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
            ->where('configuration_id', 8)
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

    public function formSubCategory(Request $request)
    {
        $id = $request->id;
        $category = Category::find($id);
        // dd($subCategory);
        return response()->json(array(
            'status' => 'oke',
            'msg' => view('category.formSubCategory', compact('category'))->render()
        ), 200);
    }

    public function addSub(Request $request)
    {
        DB::table('sub_categories')->insert([
            'category_id' => $request->get('category_id'),
            'code_sub_category' => $request->get('code_sub_category'),
            'name' => $request->get('name'),
        ]);
        return redirect()->route('category.index')->with('status', 'Subcategory added successfully!');
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
        return redirect()->route('category.index')->with('status', 'Successfully added data!');
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
