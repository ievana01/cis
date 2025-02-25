<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductMoving;
use DB;
use Illuminate\Http\Request;

class ProductMovingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $product = DB::table('product_has_warehouses')
        ->join('products', 'product_has_warehouses.product_id', '=', 'products.id_product')
        ->join('warehouses', 'warehouses.id_warehouse', '=', 'product_has_warehouses.warehouse_id')
        ->select('product_has_warehouses.*', 'warehouses.name as warehouse_name', 'products.name as product_name', 'products.id_product as id_product')
        ->orderBy('products.name')
        ->orderBy('warehouses.name')
        ->get();

        $multiWh = DB::table('detail_configurations')
        ->where('status_active', 1)
        ->where('configuration_id', 11)
        ->first();
        // dd($multiWh);
        return view('product.move', ['product' => $product, 'multiWh' => $multiWh]);
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
    public function show(ProductMoving $productMoving)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = DB::table('products')
            ->join('product_has_warehouses', 'products.id_product', '=', 'product_has_warehouses.product_id')
            ->join('warehouses', 'product_has_warehouses.warehouse_id', '=', 'warehouses.id_warehouse')
            ->select('products.*', 'warehouses.name as warehouse_name', 'product_has_warehouses.warehouse_id')
            ->where('products.id_product', $id)
            ->first();
        // dd($product);
        // Ambil semua lokasi gudang
        $warehouses = DB::table('warehouses')->get();
        return view('product.updateloc', compact('product', 'warehouses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $moveStock = $request->move_stock;
        $newWarehouse = $request->warehouse_id;
        //ambil data stok product di gudang asal
        $currentProdWhs = DB::table('product_has_warehouses')->where('product_id', $id)->first();
        // dd($currentProdWhs);
        //kurangi stok dari gudang asal
        DB::table('product_has_warehouses')
            ->where('product_id', $id)
            ->where('warehouse_id', $currentProdWhs->warehouse_id)
            ->update([
                'stock' => $currentProdWhs->stock - $moveStock,
            ]);

        // Tambahkan stok ke gudang tujuan
        $targetWarehouse = DB::table('product_has_warehouses')
            ->where('product_id', $id)
            ->where('warehouse_id', $newWarehouse)
            ->first();
        if ($targetWarehouse) {
            // Update stok jika produk sudah ada di gudang tujuan
            DB::table('product_has_warehouses')
                ->where('product_id', $id)
                ->where('warehouse_id', $newWarehouse)
                ->update([
                    'stock' => $targetWarehouse->stock + $moveStock,
                ]);
        } else {
            // Tambahkan entry baru jika belum ada di gudang tujuan
            DB::table('product_has_warehouses')->insert([
                'product_id' => $id,
                'warehouse_id' => $newWarehouse,
                'stock' => $moveStock,
            ]);
        }
        // Catat pergerakan produk di tabel product_moving
        DB::table('product_moving')->insert([
            'product_id' => $id,
            'warehouse_id_in' => $currentProdWhs->warehouse_id,
            'warehouse_id_out' => $newWarehouse,
            'move_stock' => $moveStock,
            'date' => now()->toDateString(),
            'note' => $request->note,
        ]);

        return redirect()->route('productMove.index')->with('status', 'Produk berhasil dipindahkan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductMoving $productMoving)
    {
        //
    }
}
