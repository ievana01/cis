<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ImageProduct;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use DB;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $product = Product::all();
        $product = Product::with('images')->get();
        return view('product.index', ["product" => $product]);
    }

    public function showConfiguration()
    {
    $configuration = DB::select('select * from configurations where menu_id = 3');
        foreach ($configuration as $config) {
            $config->details = DB::select('select * from detail_configurations where configuration_id = ?', [$config->id_configuration]);
        }

        return view('sales.configuration', ['configuration' => $configuration]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $category = Category::all();
        $supplier = Supplier::all();
        $warehouse = Warehouse::all();
        // Get the column details for 'tax'
        $taxColumn = DB::select('SHOW COLUMNS FROM products WHERE Field = "tax"')[0];
        // Extract the ENUM options from the 'Type' attribute of the column
        preg_match("/^enum\('(.*)'\)$/", $taxColumn->Type, $matches);
        $taxOptions = explode("','", $matches[1]);

        // Get the column details for 'unit'
        $unit = DB::select('SHOW COLUMNS FROM products WHERE Field = "unit"')[0];
        // Extract the ENUM options from the 'Type' attribute of the column
        preg_match("/^enum\('(.*)'\)$/", $unit->Type, $matches);
        $unitOptions = explode("','", $matches[1]);

        return view('product.createproduct', ["category" => $category, "supplier" => $supplier, "warehouse" => $warehouse, "tax" => $taxOptions, "unit" => $unitOptions]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = new Product();
        $data->name = $request->get('name');
        $data->description = $request->get('description');
        $data->price = $request->get('price');
        $data->total_stock = $request->get('total_stock');
        $data->cost = $request->get('cost');
        $data->tax = $request->get('tax');
        $data->unit = $request->get('unit');
        $data->min_total_stock = $request->get('min_total_stock');
        $data->category_id = $request->get('category_id');
        $data->supplier_id = $request->get('supplier_id');
        // $data->image_id = $imageId;
        $data->save();

        $warehouseId = $request->get('warehouse_id');
        DB::table('product_has_warehouses')
            ->insert([
                'product_id' => $data->id_product,
                'warehouse_id' => $warehouseId,
                'stock' => $data->total_stock
            ]);

        //simpan gambar di image_product
        if ($request->hasFile('file_images')) {
            foreach ($request->file('file_images') as $file) {
                $path = $file->store('product_images', 'public');
                DB::table('image_products')
                    ->insert(values: [
                        'product_id' => $data->id_product,
                        'file_image' => $path,
                    ]);
            }
        }
        return redirect()->route('product.index')->with('status', 'Data Berhasil Disimpan');
    }

    // public function getPrice($id)
    // {
    //     $product = Product::find($id);
    //     return response()->json(['price' => $product ? $product->price : 0]);
    // }


    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $supplier = Supplier::all();
        $category = Category::all();
        $warehouse = Warehouse::all();
        $taxColumn = DB::select('SHOW COLUMNS FROM products WHERE Field = "tax"')[0];
        // Extract the ENUM options from the 'Type' attribute of the column
        preg_match("/^enum\('(.*)'\)$/", $taxColumn->Type, $matches);
        $taxOptions = explode("','", $matches[1]);

        // Get the column details for 'unit'
        $unit = DB::select('SHOW COLUMNS FROM products WHERE Field = "unit"')[0];
        // Extract the ENUM options from the 'Type' attribute of the column
        preg_match("/^enum\('(.*)'\)$/", $unit->Type, $matches);
        $unitOptions = explode("','", $matches[1]);
        return view('product.edit', compact('product', 'supplier', 'category', 'warehouse', 'taxOptions', 'unitOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $product->name = $request->name;
        $product->description = $request->description;
        $product->supplier_id = $request->supplier_id;
        $product->category_id = $request->category_id;
        $product->total_stock = $request->total_stock;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->tax = $request->tax;
        $product->unit = $request->unit;
        $product->min_total_stock = $request->min_total_stock;
        $product->save();
        return redirect()->route('product.index')->with('status', 'Data Berhasil Diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('product.index')->with('status', 'Data Berhasil Dihapus');
        } catch (\PDOException) {
            $msg = "Failed to deleted data because there are related data with " . $product->name;
            return redirect()->route('product.index')->with('status', $msg);
        }
    }
}
