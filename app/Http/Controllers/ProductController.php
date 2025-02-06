<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ImageProduct;
use App\Models\Product;
use App\Models\ProductHasWarehouse;
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
        // dd($product);
        return view('product.index', ["product" => $product]);
    }

    public function move()
    {
        return view('product.move');
    }

    public function showReportStock()
    {
        $product = DB::table('products')
            ->join('product_has_warehouses', 'products.id_product', '=', 'product_has_warehouses.product_id')
            ->join('warehouses', 'product_has_warehouses.warehouse_id', '=', 'warehouses.id_warehouse')
            ->select('products.*', 'warehouses.name as warehouse_name')
            ->get();

        return view('product.reportstock', ['product' => $product]);
    }
    public function showConfiguration()
    {
        $configuration = DB::select('select * from configurations where menu_id = 3');
        foreach ($configuration as $config) {
            $config->details = DB::select('select * from detail_configurations where configuration_id = ?', [$config->id_configuration]);
        }

        return view('product.configuration', ['configuration' => $configuration]);
    }

    public function save(Request $request)
    {
        $configurations = $request->input('configurations', []);

        // Ambil semua konfigurasi terkait menu_id = 3
        $allConfigurations = DB::table('detail_configurations')
            ->join('configurations', 'detail_configurations.configuration_id', '=', 'configurations.id_configuration')
            ->where('configurations.menu_id', 3)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.configuration_id')
            ->get();

        // Loop untuk memproses setiap konfigurasi yang dikirim
        foreach ($allConfigurations as $config) {
            $isActive = 0; // Default: tidak aktif

            // Jika konfigurasi ini dipilih oleh user, aktifkan
            if (
                isset($configurations[$config->configuration_id]) &&
                in_array($config->id_detail_configuration, $configurations[$config->configuration_id])
            ) {
                $isActive = 1;
            }


            // Update status aktif untuk detail konfigurasi ini
            DB::table('detail_configurations')
                ->where('id_detail_configuration', $config->id_detail_configuration)
                ->update(['status_active' => DB::raw($isActive)]);
        }

        return redirect()->route('product.configuration')->with('status', 'Configurations updated successfully!');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $category = Category::all();
        $warehouse = Warehouse::all();

        // Get the column details for 'unit'
        $unit = DB::select('SHOW COLUMNS FROM products WHERE Field = "unit"')[0];
        // Extract the ENUM options from the 'Type' attribute of the column
        preg_match("/^enum\('(.*)'\)$/", $unit->Type, $matches);
        $unitOptions = explode("','", $matches[1]);

        return view('product.createproduct', ["category" => $category,  "warehouse" => $warehouse, "unit" => $unitOptions]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //ambil id detail konfigurasi yg aktif
        $cogsChoose = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 1)
            ->first();
        $cogsMethod = $cogsChoose->name;

        $data = new Product();
        $data->name = $request->get('name');
        $data->description = $request->get('description');
        $data->price = $request->get('cost') * ($request->get('profit') / 100) + $request->get('cost');
        $data->total_stock = $request->get('total_stock');
        $data->cost = $request->get('cost');
        $data->profit = $request->get('profit');
        $data->unit = $request->get('unit');
        $data->min_total_stock = $request->get('min_total_stock');
        $data->category_id = $request->get('category_id');
        // $data->supplier_id = $request->get('supplier_id');
        $data->save();

        if ($cogsMethod == 'FIFO') {
            DB::table('product_fifo')->insert([
                'product_id' => $data->id_product,
                'purchase_date' => now()->toDateString(),
                'price' => $data->price,
                'cost' => $data->cost,
                'stock' => $data->total_stock
            ]);
        }

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
        // dd($product);
        $supplier = Supplier::all();
        $category = Category::all();
        // $warehouse = Warehouse::all();
        $warehouse = ProductHasWarehouse::where('product_id', $product->id_product)
            ->join('warehouses', 'product_has_warehouses.warehouse_id', '=', 'warehouses.id_warehouse')
            ->select('warehouses.name')
            ->first();
        // dd($warehouse);
        //pembelian prod
        $pemProd = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 7)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.name')
            ->first();
        // dd($pemProd);
        // Get the column details for 'unit'
        $unit = DB::select('SHOW COLUMNS FROM products WHERE Field = "unit"')[0];
        // Extract the ENUM options from the 'Type' attribute of the column
        preg_match("/^enum\('(.*)'\)$/", $unit->Type, $matches);
        $unitOptions = explode("','", $matches[1]);
        return view('product.edit', compact('product', 'supplier', 'category', 'warehouse', 'unitOptions', 'pemProd'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $product->name = $request->name;
        $product->description = $request->description;
        $product->category_id = $request->category_id;
        $product->total_stock = $request->total_stock;
        $product->cost = $request->cost;
        $product->price = $request->price;
        $product->profit = $request->profit;
        $product->unit = $request->unit;
        $product->min_total_stock = $request->min_total_stock;
        $product->save();
        return redirect()->route('product.index')->with('status', 'Data Berhasil Diubah!');
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
