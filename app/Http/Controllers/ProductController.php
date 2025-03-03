<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ImageProduct;
use App\Models\Product;
use App\Models\ProductHasWarehouse;
use App\Models\SubCategory;
use App\Models\Supplier;
use App\Models\Warehouse;
use DB;
use Illuminate\Http\Request;
use Storage;
use View;

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

        return redirect()->route('product.configuration')->with('status', 'Sukses mengubah data konfigurasi!');
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

        $subKat = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 10)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.name')
            ->first();
        $nameSubKat = SubCategory::all();
        return view('product.createproduct', ["category" => $category, "warehouse" => $warehouse, "unit" => $unitOptions, "subKat" => $subKat, "nameSubKat" => $nameSubKat]);
    }

    //dapetin data sub kategori di create dan edit produk
    public function getSubCategory($category_id)
    {
        $subCat = SubCategory::where('category_id', $category_id)->get();
        return response()->json($subCat);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $message = [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari 45 karakter.',
            'description.required' => 'Deskripsi wajib diisi.',
            'total_stock.required' => 'Total stok wajib diisi.',
            'total_stock.numeric' => 'Total stok harus berupa angka.',
            'cost.required' => 'Biaya wajib diisi.',
            'cost.numeric' => 'Biaya harus berupa angka.',
            'profit.required' => 'Keuntungan wajib diisi.',
            'profit.numeric' => 'Keuntungan harus berupa angka.',
            'unit.required' => 'Satuan wajib diisi.',
            'min_total_stock.required' => 'Stok minimum wajib diisi.',
            'min_total_stock.numeric' => 'Stok minimum harus berupa angka.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'warehouse_id.required' => 'Gudang wajib dipilih.',
            'file_images.*.image' => 'File harus berupa gambar.',
            'file_images.*.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'file_images.*.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
        ];
        $validated = $request->validate([
            'name' => 'required|string|max:45',
            'description' => 'required',
            'total_stock' => 'required|numeric',
            'cost' => 'required',
            'profit' => 'required|numeric',
            'unit' => 'required',
            'min_total_stock' => 'required|numeric',
            'category_id' => 'required',
            'warehouse_id' => 'required',
            '
            file_images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ], $message);

        //ambil id detail konfigurasi yg aktif
        $cogsChoose = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 1)
            ->first();
        $cogsMethod = $cogsChoose->name;

        $subKat = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 10)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.name')
            ->first();

        $data = new Product();
        $data->name = $validated['name'];
        $data->description = $validated['description'];
        $data->price = $validated['cost'] * ($validated['profit'] / 100) + $validated['cost'];
        $data->total_stock = $validated['total_stock'];
        $data->cost = $validated['cost'];
        $data->profit = $validated['profit'];
        $data->unit = $validated['unit'];
        $data->min_total_stock = $validated['min_total_stock'];
        $data->category_id = $validated['category_id'];

        if ($subKat != null) {
            $data->sub_categories_id = $request->get('sub_category');
        } else {
            $data->sub_categories_id = null;
        }
        $data->in_order = 0;
        $data->on_order = 0;
        $data->save();

        if ($cogsMethod == 'FIFO') {
            DB::table('product_fifo')->insert([
                'product_id' => $data->id_product,
                'purchase_date' => now()->toDateString(),
                'price' => $data->price,
                'cost' => $data->cost,
                'initial_stock' => $data->total_stock,
                'sold' => 0,
                'in_order' => 0,
                'on_order' => 0
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
                    ->insert([
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
        $images = DB::table('image_products')
            ->where('product_id', $product->id_product)
            ->pluck('file_image'); // Mengambil hanya nilai file_image dalam bentuk array

        // dd($images);
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
        $categoryId = $product->category_id;
        // dd($categoryId);
        $konfigSubCat = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 10)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.name')
            ->first();
        $subCat = SubCategory::where('category_id', $categoryId)->get();
        // dd($subCat);
        // Get the column details for 'unit'
        $unit = DB::select('SHOW COLUMNS FROM products WHERE Field = "unit"')[0];
        // Extract the ENUM options from the 'Type' attribute of the column
        preg_match("/^enum\('(.*)'\)$/", $unit->Type, $matches);
        $unitOptions = explode("','", $matches[1]);
        return view('product.edit', compact('images', 'product', 'supplier', 'category', 'warehouse', 'unitOptions', 'pemProd', 'konfigSubCat', 'subCat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $message = [
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari 45 karakter.',
            'total_stock.numeric' => 'Total stok harus berupa angka.',
            'cost.numeric' => 'Harga pokok harus berupa angka.',
            'profit.numeric' => 'Keuntungan harus berupa angka.',
            'min_total_stock.numeric' => 'Stok minimum harus berupa angka.',
            'file_images.*.image' => 'File harus berupa gambar.',
            'file_images.*.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'file_images.*.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
        ];
        $validated = $request->validate([
            'name' => 'string|max:45',
            'total_stock' => 'numeric',
            'cost' => 'numeric',
            'profit' => 'numeric',
            'min_total_stock' => 'numeric',
            'file_images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ], $message);

        // $product->name = $request->name;
        // $product->description = $request->description;
        // $product->category_id = $request->category_id;
        // $product->total_stock = $request->total_stock;
        // $product->cost = $request->cost;
        // $product->price = ($request->cost * ($request->profit / 100)) + $request->cost;
        // // dd($product->price);
        // $product->profit = $request->profit;
        // $product->unit = $request->unit;
        // $product->min_total_stock = $request->min_total_stock;
        // $product->sub_categories_id = $request->sub_category_id;
        // // dd($request->all(), $request->hasFile('imageUpload'), $_FILES);
        $product->name = $validated['name'];
        $product->description = $request->description;
        $product->category_id = $request->category_id;
        $product->total_stock = $validated['total_stock'];
        $product->cost = $validated['cost'];
        $product->price = ($validated['cost'] * ($validated['profit'] / 100)) + $validated['cost'];
        $product->profit = $validated['profit'];
        $product->unit = $request->unit;
        $product->min_total_stock = $validated['min_total_stock'];
        $product->sub_categories_id = $request->sub_category_id;

        // Cek apakah ada file gambar baru
        if ($request->hasFile('imageUpload')) {
            $image = $request->file('imageUpload');
            // dd($image);
            // Simpan gambar baru di storage
            $imagePath = $image->store('product_images', 'public'); // Simpan di storage/app/public/product_images
            $imageData = file_get_contents($image->getRealPath()); // Ambil data gambar untuk BLOB

            // Cek apakah produk sudah punya gambar di tabel image_products
            $existingImage = DB::table('image_products')
                ->where('product_id', $product->id_product)
                ->select('file_image')
                ->first();

            // ImageProduct::where('product_id', $product->id)->first();
            // dd($existingImage);
            if ($existingImage) {
                // Hapus gambar lama dari storage jika ada
                if ($existingImage->file_image && Storage::disk('public')->exists($existingImage->file_image)) {
                    Storage::disk('public')->delete($existingImage->file_image);
                }

                DB::table('image_products')
                    ->where('product_id', $product->id_product)
                    ->update([
                        'file_image' => $imagePath, // Simpan path gambar
                    ]);
            }
        }
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

    public function getProductsByWarehouse($warehouse_id)
    {
        $products = DB::table('product_has_warehouses as phw')
            ->join('products as p', 'p.id_product', '=', 'phw.product_id')
            ->where('phw.warehouse_id', operator: $warehouse_id)
            ->select('p.id_product', 'p.name', 'phw.stock')
            ->get();

        return response()->json($products);
    }

}
