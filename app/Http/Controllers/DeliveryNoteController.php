<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteHasProducts;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use DB;
use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $pindah = DeliveryNote::with(DeliveryNoteHasProducts::class)->where('type', 'pindah')->get();
        $pindah = DB::table('delivery_note as dn')
            ->join('delivery_note_has_products as dnp', 'dn.id', '=', 'dnp.delivery_note_id')
            ->join('products as p', 'dnp.product_id', '=', 'p.id_product')
            ->join('warehouses as w_in', 'dn.warehouses_id_in', '=', 'w_in.id_warehouse')  // Join untuk Gudang Tujuan
            ->join('warehouses as w_out', 'dn.warehouses_id_out', '=', 'w_out.id_warehouse') // Join untuk Gudang Asal
            ->where('dn.type', 'pindah')
            ->select(
                'dn.id as delivery_note_id',
                'dn.date',
                'dn.note',
                'dn.warehouses_id_in',
                'w_in.name as warehouse_in_name',  // Nama Gudang Tujuan
                'dn.warehouses_id_out',
                'w_out.name as warehouse_out_name', // Nama Gudang Asal
                'dnp.product_id',
                'dnp.quantity',
                'p.name as product_name'
            )
            ->orderBy('dn.id', 'desc')
            ->get();


        // dd($pindah);
        return view('deliveryNote.index', compact('pindah'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouse = Warehouse::all();
        return view('deliveryNote.create', ['warehouse' => $warehouse]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $cogsChoose = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 1)
            ->first();
        $cogsMethod = $cogsChoose->name;

        $dn = new DeliveryNote();
        $dn->date = $request->get('date');
        $dn->type = 'pengiriman';
        $dn->note = null;
        $dn->warehouses_id_in = $request->get('warehouse_id_in');
        $dn->sales_id = $request->sales_id;
        $dn->save();

        foreach ($request->products as $product) {
            DB::table('delivery_note_has_products')->insert([
                'delivery_note_id' => $dn->id,
                'product_id' => $product['id_product'],
                'quantity' => $product['total_quantity'],
                'note' => $product['note'] ?? null,
            ]);

            if ($cogsMethod == "FIFO") {
                $remainingQty = $product['total_quantity'];

                // Ambil stok berdasarkan FIFO, tidak hanya `in_order`
                $fifoStocks = DB::table('product_fifo')
                    ->where('product_id', $product['id_product'])
                    ->whereRaw('(initial_stock - sold) > 0') // Pastikan masih ada stok tersisa
                    ->orderBy('purchase_date', 'asc')
                    ->get();

                foreach ($fifoStocks as $stock) {
                    if ($remainingQty <= 0)
                        break;

                    $availableQty = $stock->initial_stock - $stock->sold;
                    $deductQty = min($remainingQty, $availableQty);

                    DB::table('product_fifo')
                        ->where('id_product_fifo', $stock->id_product_fifo)
                        ->update([
                            'sold' => $stock->sold + $deductQty
                        ]);

                    $remainingQty -= $deductQty;
                }

                // Kurangi `in_order` dan `total_stock` pada `products`, tapi pastikan tidak negatif
                DB::table('products')
                    ->where('id_product', $product['id_product'])
                    ->update([
                        'in_order' => DB::raw("GREATEST(in_order - {$product['total_quantity']}, 0)"),
                        'total_stock' => DB::raw("GREATEST(total_stock - {$product['total_quantity']}, 0)")
                    ]);

                DB::table('product_fifo')
                    ->where('product_id', $product['id_product'])
                    ->update([
                        'in_order' => DB::raw("GREATEST(in_order - {$product['total_quantity']}, 0)"),
                    ]);

                DB::table('product_has_warehouses')
                    ->where('product_id', $product['id_product'])
                    ->where('warehouse_id', $request->warehouse_id_in)
                    ->decrement('stock', $product['total_quantity']);
            } else if ($cogsMethod == 'Average') {
                DB::table('products')
                    ->where('id_product', $product['id_product'])
                    ->update([
                        'in_order' => DB::raw("GREATEST(in_order - {$product['total_quantity']}, 0)"),
                        'total_stock' => DB::raw("GREATEST(total_stock - {$product['total_quantity']}, 0)")
                    ]);

                DB::table('product_has_warehouses')
                    ->where('product_id', $product['id_product'])
                    ->where('warehouse_id', $request->warehouse_id_in)
                    ->decrement('stock', $product['total_quantity']);
            }
        }

        return redirect()->route('sales.index')->with('status', 'Berhasil Kirim Pesanan!');
    }

    public function storeTerima(Request $request)
    {
        $cogsChoose = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 1)
            ->first();
        $cogsMethod = $cogsChoose->name;
        $dn = new DeliveryNote();
        $dn->date = $request->get('date');
        $dn->note = null;
        $dn->type = 'terima';
        $dn->warehouses_id_in = $request->get('warehouse_id_in');
        $dn->purchase_id = $request->purchase_id;
        // dd(request()->all());
        $dn->save();
        foreach ($request->products as $product) {
            DB::table('delivery_note_has_products')->insert([
                'delivery_note_id' => $dn->id,
                'product_id' => $product['id_product'],
                'quantity' => $product['total_quantity'],
                'note' => $product['note'] ?? null,
            ]);
            $productId = $product['id_product'] ?? null;

            if (!$productId) {
                return back()->withErrors(['error' => 'Produk tidak ditemukan dalam permintaan.']);
            }

            $productData = DB::table('products')->where('id_product', $productId)->first();
            // Ambil cost dari purchase_details berdasarkan purchase_id
            $purchaseDetail = DB::table('purchase_details')
                ->where('purchase_id', $request->purchase_id)
                ->where('product_id', $productId)
                ->first();
            // dd($purchaseDetail);
            $cost = $purchaseDetail->total_price / $purchaseDetail->total_quantity_product;
            $totalStock = $productData->total_stock + $product['total_quantity'];
            $onOrder = max(0, $productData->on_order - $product['total_quantity']);

            if ($cogsMethod == "FIFO") {
                $profit = $productData->profit / 100;
                $price = ($cost * $profit) + $cost;
                // $totalStock = $productData->total_stock + $product['quantity'];

                DB::table('product_fifo')->insert([
                    'product_id' => $product['id_product'],
                    'initial_stock' => $product['total_quantity'],
                    'purchase_date' => $request->get('date'),
                    'cost' => $cost,
                    'price' => $price,
                    'sold' => 0,
                    'in_order' => 0,
                    'on_order' => $onOrder
                ]);

                DB::table('products')
                    ->where('id_product', $product['id_product'])
                    ->update([
                        'price' => $price,
                        'total_stock' => $totalStock,
                        'cost' => $cost,
                        'on_order' => $onOrder,
                    ]);
            } else if ($cogsMethod == 'Average') {
                $profit = $productData->profit / 100;
                $oldStock = $productData->total_stock;
                $oldCost = $productData->cost;
                $totalOldCost = $oldCost * $oldStock;

                $newStock = $product['total_quantity'];
                $totalNewCost = $purchaseDetail->total_price;
                $newCost = $totalNewCost / $newStock;

                $totalAllCost = $totalOldCost + $totalNewCost;
                $totalAllStock = $oldStock + $newStock;
                $averageCost = $totalAllCost / $totalAllStock;
                $newPrice = ($averageCost * $profit) + $averageCost;

                DB::table('products')
                    ->where('id_product', $product['id_product'])
                    ->update([
                        'total_stock' => $totalAllStock,
                        'cost' => $newCost,
                        'price' => $newPrice,
                        'on_order' => $onOrder,
                    ]);
            }
            DB::table('product_has_warehouses')
                ->where('product_id', $product['id_product'])
                ->where('warehouse_id', $request->get('warehouse_id_in'))
                ->update([
                    'stock' => DB::raw("stock + {$product['total_quantity']}")
                ]);
        }

        return redirect()->route('purchase.index')->with('status', 'Berhasil Terima Produk!');
    }

    public function storePindah(Request $request)
    {
        $dn = new DeliveryNote();
        $dn->date = $request->get('date');
        $dn->note = $request->get('note');
        $dn->type = 'pindah';
        $dn->warehouses_id_in = $request->get('warehouses_id_in');
        $dn->warehouses_id_out = $request->get('warehouses_id_out');
        $dn->save();

        $products = json_decode($request->get('products'), true);
        if (!empty($products)) {
            foreach ($products as $product) {
                // Kurangi stok dari gudang asal
                DB::table('product_has_warehouses')
                    ->where('product_id', $product['id'])
                    ->where('warehouse_id', $dn->warehouses_id_in)
                    ->decrement('stock', $product['quantity']);

                // Cek apakah produk sudah ada di gudang tujuan
                $existing = DB::table('product_has_warehouses')
                    ->where('product_id', $product['id'])
                    ->where('warehouse_id', $dn->warehouses_id_in)
                    ->first();

                if ($existing) {
                    // Jika sudah ada, tambahkan stoknya
                    DB::table('product_has_warehouses')
                        ->where('product_id', $product['id'])
                        ->where('warehouse_id', $dn->warehouses_id_in)
                        ->increment('stock', $product['quantity']);
                } else {
                    // Jika belum ada, buat entri baru
                    DB::table('product_has_warehouses')->insert([
                        'product_id' => $product['id'],
                        'warehouse_id' => $dn->warehouses_id_in,
                        'stock' => $product['quantity']
                    ]);
                }
            }
        }

        return redirect()->route('pindahProduk.index')->with('status', 'Berhasil pindah produk!');
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
