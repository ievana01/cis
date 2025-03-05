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
        $multiWh = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 11)
            ->first();

        $pindah = DB::table('delivery_note as dn')
            ->leftJoin('delivery_note_has_products as dnp', 'dn.id', '=', 'dnp.delivery_note_id')
            ->leftJoin('products as p', 'dnp.product_id', '=', 'p.id_product')
            ->leftJoin('warehouses as w', 'dn.warehouses_id_in', '=', 'w.id_warehouse')
            ->leftJoin('warehouses as w2', 'dn.warehouses_id_out', '=', 'w2.id_warehouse')
            ->where('dn.type', 'pindah')
            ->select('dn.*', 'w.name as w_in', 'w2.name as w_out', 'p.name as prod_name', 'dnp.product_id', 'dnp.quantity')
            ->get()
            ->groupBy('id'); // Kelompokkan berdasarkan ID delivery_note

        // Konversi ke format array (jika perlu)
        $pindah = $pindah->map(function ($items) {
            $first = $items->first(); // Ambil data utama
            $first->products = $items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'prod_name' => $item->prod_name,
                    'quantity' => $item->quantity,
                ];
            })->values(); // Buat array produk dalam satu pengiriman
            return $first;
        })->values();

        // Cek hasil
        // dd($pindah);

        return view('deliveryNote.index', compact('pindah', 'multiWh'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $invoiceNumber = $this->generateInvoiceNumber();
        $warehouse = Warehouse::all();
        return view('deliveryNote.create', ['warehouse' => $warehouse, 'invoiceNumber' => $invoiceNumber]);
    }

    public function generateInvoiceNumber()
    {
        $lastInvoice = DeliveryNote::orderBy('id', 'desc')->first();
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        return 'MV-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
    /**
     * Store a newly created resource in storage.
     */
    //kirim
    public function store(Request $request)
    {
        $cogsChoose = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 5)
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

                // DB::table('product_fifo')
                //     ->where('product_id', $product['id_product'])
                //     ->update([
                //         'in_order' => DB::raw("GREATEST(in_order - {$product['total_quantity']}, 0)"),
                //     ]);

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
            ->where('configuration_id', 5)
            ->first();
        $cogsMethod = $cogsChoose->name;
        $dn = new DeliveryNote();
        $dn->date = $request->get('date');
        $dn->note = null;
        $dn->type = 'terima';
        $dn->warehouses_id_in = $request->warehouse_id;
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
                    // 'in_order' => 0,
                    // 'on_order' => $onOrder
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
                ->where('warehouse_id', $request->get('warehouse_id'))
                ->update([
                    'stock' => DB::raw("stock + {$product['total_quantity']}")
                ]);
        }

        return redirect()->route('purchase.index')->with('status', 'Berhasil Terima Produk!');
    }

    public function storePindah(Request $request)
    {
        // Simpan data Delivery Note
        $dn = new DeliveryNote();
        $invoiceNumber = $this->generateInvoiceNumber();
        $dn->invoice_number = $invoiceNumber;
        $dn->date = $request->get('date');
        $dn->note = $request->get('note');
        $dn->type = 'pindah';
        $dn->warehouses_id_in = $request->get('warehouses_id_in'); // Gudang asal
        $dn->warehouses_id_out = $request->get('warehouses_id_out'); // Gudang tujuan
        $dn->save();

        // Decode produk dari request
        $products = json_decode($request->get('products'), true);
        foreach ($products as $product) {
            $productId = $product['id'];
            $quantity = $product['quantity'];

            DB::table('delivery_note_has_products')->insert([
                'delivery_note_id' => $dn->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);

            // Kurangi stok di gudang asal
            DB::table('product_has_warehouses')
                ->where('product_id', $productId)
                ->where('warehouse_id', $dn->warehouses_id_in)
                ->decrement('stock', $quantity);

            // Cek apakah produk sudah ada di gudang tujuan
            $existing = DB::table('product_has_warehouses')
                ->where('product_id', $productId)
                ->where('warehouse_id', $dn->warehouses_id_out)
                ->first();

            if ($existing) {
                // Jika produk sudah ada di gudang tujuan, tambahkan stoknya
                DB::table('product_has_warehouses')
                ->where('product_id', $productId)
                    ->where('warehouse_id', $dn->warehouses_id_out)
                    ->increment('stock', $quantity);
            } else {
                // Jika belum ada, buat entri baru
                DB::table('product_has_warehouses')->insert([
                    'product_id' => $productId,
                    'warehouse_id' => $dn->warehouses_id_out,
                    'stock' => $quantity
                ]);
            }
        }
        return redirect()->route('pindahProduk.index')->with('status', 'Berhasil pindah produk!');
    }

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
