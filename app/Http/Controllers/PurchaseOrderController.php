<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductMoving;
use App\Models\PurchaseOrder;
use App\Models\SalesDetail;
use App\Models\StoreData;
use App\Models\Supplier;
use App\Models\Warehouse;
use DB;
use Illuminate\Http\Request;
use Log;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchase = DB::table('purchase_orders')
            ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id_supplier')
            ->leftJoin('detail_configurations', 'purchase_orders.payment_method', '=', 'detail_configurations.id_detail_configuration')
            ->select(
                'purchase_orders.id_purchase as id_purchase',
                'purchase_orders.purchase_invoice',
                'purchase_orders.date',
                'purchase_orders.expected_arrival',
                'purchase_orders.total_purchase',
                'suppliers.name as supplier_name',
                'detail_configurations.name as payment_method'
            )
            ->orderBy('purchase_invoice', 'desc')
            ->get();

        $payProd = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 9)
            ->first();
        // dd($payProd);
        return view('purchase.index', ["purchase" => $purchase, 'payProd' => $payProd]);
    }

    public function showConfiguration()
    {
        $configuration = DB::select('select * from configurations where menu_id = 2');
        foreach ($configuration as $config) {
            $config->details = DB::select('select * from detail_configurations where configuration_id = ?', [$config->id_configuration]);
        }

        return view('purchase.configuration', ['configuration' => $configuration]);
    }

    public function save(Request $request)
    {
        $configurations = $request->input('configurations', []);
        $allConfigurations = DB::table('detail_configurations')
            ->join('configurations', 'detail_configurations.configuration_id', '=', 'configurations.id_configuration')
            ->where('configurations.menu_id', 2)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.configuration_id')
            ->get();

        // Loop untuk memproses setiap konfigurasi yang dikirim
        foreach ($allConfigurations as $config) {
            $isActive = 0; // Default: tidak aktif

            // Jika konfigurasi ini dipilih oleh user, aktifkan
            if (
                isset($configurations[$config->configuration_id]) &&
                $configurations[$config->configuration_id] == $config->id_detail_configuration
            ) {
                $isActive = 1;
            }

            // Update status aktif untuk detail konfigurasi ini
            DB::table('detail_configurations')
                ->where('id_detail_configuration', $config->id_detail_configuration)
                ->update(['status_active' => DB::raw($isActive)]);
        }

        return redirect()->route('purchase.configuration')->with('status', 'Berhasil mengubah konfigurasi!');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $invoiceNumber = $this->generateInvoiceNumber();
        $supplier = Supplier::all();
        $product = Product::all();

        $payProd = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 9)
            ->first();
        // dd($payProd);

        $warehouse = Warehouse::all();

        //mendapatkan konfigurasi harga beli 
        $purchasePriceMethod = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 7)
            ->first();

        //mendapatkan konfiguasi penerimaan barang
        $receiveProdMethod = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 8)
            ->first();
        // dd($receiveProdMethod);

        $hisPurchase = ProductMoving::whereNotNull('purchase_id')
            ->join('products', 'product_moving.product_id', '=', 'products.id_product')
            ->join('purchase_orders', 'product_moving.purchase_id', '=', 'purchase_orders.id_purchase')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id_supplier') // Inner join dengan tabel products
            ->select('product_moving.*', 'products.name as product_name', 'suppliers.name as supplier_name') // Pilih kolom yang dibutuhkan
            ->orderBy('product_moving.date', 'desc')
            ->get();
        // dd($hisPurchase);
        return view('purchase.create', [
            'invoiceNumber' => $invoiceNumber,
            'supplier' => $supplier,
            'product' => $product,
            'warehouse' => $warehouse,
            'purchasePriceMethod' => $purchasePriceMethod,
            'receiveProdMethod' => $receiveProdMethod,
            'payProd' => $payProd,
            'hisPurchase' => $hisPurchase
        ]);
    }
    public function generateInvoiceNumber()
    {
        $lastInvoice = PurchaseOrder::orderBy('id_purchase', 'desc')->first();
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->purchase_invoice, 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        return 'P' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
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

        $payProd = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 9)
            ->first();

        $purchase = new PurchaseOrder();
        $invoiceNumber = $this->generateInvoiceNumber();
        $purchase->purchase_invoice = $invoiceNumber;
        $purchase->date = $request->get(key: 'date');
        $purchase->expected_arrival = $request->get('expected_arrival');
        $purchase->total_purchase = $request->get('total_price');
        if ($payProd->id_detail_configuration == 22) {
            $purchase->payment_method = $request->get('payment_method') ?? 6;
        }
        // $purchase->payment_method = $request->get('payment_method');
        $purchase->supplier_id = $request->get('id_supplier');
        $purchase->employee_id = $request->get('employee_id') ?? 1;
        $purchase->save();

        $products = json_decode($request->get('products'), true);
        foreach ($products as $product) {
            DB::table('purchase_details')->insert(
                [
                    'purchase_id' => $purchase->id_purchase,
                    'product_id' => $product['id'],
                    'total_quantity_product' => $product['quantity'],
                    'total_price' => $product['amount'],
                ]
            );

            $purchase->refreshCost($cogsMethod, $product, $request->get('date'));

            DB::table('product_moving')->insert([
                'move_stock' => $product['quantity'],
                'date' => $purchase->date,
                'product_id' => $product['id'],
                'warehouse_id_in' => $request->get('id_warehouse'),
                'purchase_id' => $purchase->id_purchase,
            ]);

            DB::table('product_has_warehouses')
                ->where('product_id', $product['id'])
                ->increment('stock', $product['quantity']);
        }
        // return redirect()->route('purchase.index')->with('status', 'Data successfully added');
        return redirect()->route('purchase.showNota', $purchase->id_purchase)->with('status', 'Nota berhasil dibuat!');

    }


    public function showData()
    {
        $data = DB::table('product_moving')
            ->join('products', 'product_moving.product_id', '=', 'products.id_product')
            ->join('purchase_orders', 'product_moving.purchase_id', '=', 'purchase_orders.id_purchase')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id_supplier')
            ->select(
                'purchase_orders.purchase_invoice as purchase_invoice',
                'suppliers.name as supplier_name',
                'products.name as product_name',
                'product_moving.*'
            )
            ->orderBy('purchase_invoice', 'asc')
            ->get();
        // dd($data);
        return view('purchase.datapurchase', ['data' => $data]);
    }

    public function getNota(Request $request)
    {
        $id_purchase = $request->input('id_purchase');

        if (empty($id_purchase)) {
            return response()->json(['error' => 'ID Purchase kosong'], 400);
        }

        $purchase = PurchaseOrder::find($id_purchase);

        if (!$purchase) {
            return response()->json(['error' => 'Sales order dengan ID tersebut tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Data successfully processed',
            'id_purchase' => $id_purchase,
        ]);
    }

    public function showNota($id)
    {
        $dataToko = StoreData::first();
        $purchase = DB::table('purchase_orders')
            ->join('employees', 'purchase_orders.employee_id', '=', 'employees.id_employee')
            ->where('id_purchase', $id)
            ->select('employees.name as e_name', 'purchase_orders.*')
            ->first();
        // dd($purchase);
        $purchaseDetail = DB::table('purchase_details')
            ->join('products', 'purchase_details.product_id', '=', 'products.id_product')
            ->where('purchase_details.purchase_id', $id)
            ->select('purchase_details.*', 'products.name as product_name')
            ->get();
        // dd($purchaseDetail);
        if (!$purchase || $purchaseDetail->isEmpty()) {
            return redirect()->back()->with('error', 'Sales order not found');
        }

        return view('purchase.nota', compact('purchase', 'purchaseDetail', 'dataToko'));
    }
    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        return view('purchase.paymentForm', compact('purchaseOrder'));
    }

    public function paymentForm(Request $request)
    {
        $id = $request->id;
        $paymentMethod = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 3)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.name')
            ->get();
        $purchase = PurchaseOrder::find($id);
        return response()->json(array(
            'status' => 'oke',
            'msg' => view('purchase.paymentForm', compact('purchase', 'paymentMethod'))->render()
        ), 200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $id = $request->input('id_purchase');

        $updated = DB::table('purchase_orders')
            ->where('id_purchase', $id)
            ->update(['payment_method' => $request->get('payment_method')]);

        // dd($request->get('payment_method'));


        return redirect()->route('purchase.index')->with('status', 'Sukses melakukan Pembayaran!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        //
    }

    // public function laporanLabaKotor()
    // {
    //     $dataToko = StoreData::first();
    //     $product = Product::select('id_product', 'name', 'price', 'cost')->get();
    //     $laporan = $product->map(function ($product) {
    //         // Hitung jumlah barang terjual dari sales_detail
    //         $jumlahTerjual = SalesDetail::where('product_id', $product->id_product)->sum('total_quantity');

    //         // Hitung pendapatan (harga jual * jumlah terjual)
    //         $pendapatan = $product->price * $jumlahTerjual;
    //         // Hitung laba kotor (pendapatan - HPP * jumlah terjual)
    //         $labaKotor = $pendapatan - ($product->cost * $jumlahTerjual);
    //         // dd($labaKotor);

    //         // Kembalikan data yang diperlukan
    //         return [
    //             'name' => $product->name,
    //             'jumlahTerjual' => $jumlahTerjual,
    //             'hargaJual' => $product->price,
    //             'hpp' => $product->cost,
    //             'pendapatan' => $pendapatan,
    //             'labaKotor' => $labaKotor,
    //         ];
    //     });
    //     // dd($laporan);
    //     return view('laporanlaba', ['dataToko' => $dataToko, 'product' => $product, 'laporan' => $laporan]);
    // }
    public function laporanLabaKotor()
    {
        $products = Product::all();
        $dataToko = StoreData::first();
        $laporan = $products->map(function ($product) {
            $jumlahTerjual = SalesDetail::where('product_id', $product->id_product)->sum('total_quantity');
            $pendapatan = $product->price * $jumlahTerjual;

            // FIFO - Ambil stok yang tersisa berdasarkan pembelian awal (terlama)
            $totalCOGS = 0;
            $remainingToDeduct = $jumlahTerjual;

            $fifoStocks = DB::table('product_fifo')
                ->where('product_id', $product->id_product)
                ->where('stock', '>', 0)
                ->orderBy('purchase_date', 'asc')
                ->get();

            foreach ($fifoStocks as $fifo) {
                if ($remainingToDeduct <= 0)
                    break;

                if ($fifo->stock >= $remainingToDeduct) {
                    $totalCOGS += $remainingToDeduct * $fifo->cost;
                    $remainingToDeduct = 0;
                } else {
                    $totalCOGS += $fifo->stock * $fifo->cost;
                    $remainingToDeduct -= $fifo->stock;
                }
            }

            $labaKotor = $pendapatan - $totalCOGS;

            return [
                'name' => $product->name,
                'jumlahTerjual' => $jumlahTerjual,
                'hargaJual' => $product->price,
                'hpp' => ($jumlahTerjual > 0) ? $totalCOGS / $jumlahTerjual : 0,
                'pendapatan' => $pendapatan,
                'labaKotor' => $labaKotor,
            ];
        });
        // dd($laporan);
        return view('laporanlaba', ['laporan' => $laporan, 'dataToko' => $dataToko]);
    }

}
