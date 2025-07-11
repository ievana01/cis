<?php

namespace App\Http\Controllers;

use App\Models\DetailConfiguration;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductMoving;
use App\Models\PurchaseOrder;
use App\Models\SalesDetail;
use App\Models\StoreData;
use App\Models\Supplier;
use App\Models\Warehouse;
use Auth;
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
                'detail_configurations.name as payment_method',
                'purchase_orders.expected_arrival as expected_arrival'
            )
            ->orderBy('purchase_invoice', 'desc')
            ->get();
        // dd($purchase);
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

    // public function save(Request $request)
    // {
    //     $configurations = $request->input('configurations', []);
    //     $profitValues = $request->input('profit_values', []);

    //     $allConfigurations = DB::table('detail_configurations')
    //         ->join('configurations', 'detail_configurations.configuration_id', '=', 'configurations.id_configuration')
    //         ->where('configurations.menu_id', 2)
    //         ->select('detail_configurations.id_detail_configuration', 'detail_configurations.configuration_id')
    //         ->get();

    //     foreach ($allConfigurations as $config) {
    //         // Ambil konfigurasi yang dipilih
    //         $selectedConfigurations = $configurations[$config->configuration_id] ?? [];

    //         // Jika hanya satu nilai (radio button), ubah menjadi array
    //         if (!is_array($selectedConfigurations)) {
    //             $selectedConfigurations = [$selectedConfigurations];
    //         }

    //         // Jika id_configuration == 7, pastikan hanya satu opsi aktif
    //         if ($config->configuration_id == 7) {
    //             DB::table('detail_configurations')
    //                 ->where('configuration_id', $config->configuration_id)
    //                 ->update(['status_active' => 0]); // Nonaktifkan semua dulu

    //             DB::table('detail_configurations')
    //                 ->where('id_detail_configuration', reset($selectedConfigurations))
    //                 ->update(['status_active' => 1]); // Aktifkan hanya satu yang dipilih
    //             if ($selectedId == 17 && isset($profitValues[5])) {
    //                 DB::table('detail_configurations')
    //                     ->where('id_detail_configuration', 17)
    //                     ->update(['value' => $profitValues[5]]);
    //             }
    //         } else {
    //             // Untuk checkbox (bisa lebih dari satu)
    //             DB::table('detail_configurations')
    //                 ->where('id_detail_configuration', $config->id_detail_configuration)
    //                 ->update(['status_active' => in_array($config->id_detail_configuration, $selectedConfigurations) ? 1 : 0]);
    //         }
    //     }

    //     return redirect()->route('purchase.configuration')->with('status', 'Berhasil mengubah konfigurasi!');
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function save(Request $request)
    {
        $configurations = $request->input('configurations', []);
        $profitValues = $request->input('profit_values', []);

        $allConfigurations = DB::table('detail_configurations')
            ->join('configurations', 'detail_configurations.configuration_id', '=', 'configurations.id_configuration')
            ->where('configurations.menu_id', 2)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.configuration_id')
            ->get();

        foreach ($allConfigurations as $config) {
            // Ambil konfigurasi yang dipilih
            $selectedConfigurations = $configurations[$config->configuration_id] ?? [];

            // Jika hanya satu nilai (radio button), ubah menjadi array
            if (!is_array($selectedConfigurations)) {
                $selectedConfigurations = [$selectedConfigurations];
            }

            if ($config->configuration_id == 7) {
                // Nonaktifkan semua dulu
                DB::table('detail_configurations')
                    ->where('configuration_id', $config->configuration_id)
                    ->update(['status_active' => 0]);

                // Aktifkan yang dipilih
                $selectedId = reset($selectedConfigurations);
                DB::table('detail_configurations')
                    ->where('id_detail_configuration', $selectedId)
                    ->update(['status_active' => 1]);

                // Cek jika konfigurasi yang dipilih adalah ID 17 (profit) dan simpan nilainya
                if ($selectedId == 17 && isset($profitValues[5])) {
                    DB::table('detail_configurations')
                        ->where('id_detail_configuration', 17)
                        ->update(['value' => $profitValues[5]]);
                }
            } else {
                // Untuk checkbox (bisa lebih dari satu)
                DB::table('detail_configurations')
                    ->where('id_detail_configuration', $config->id_detail_configuration)
                    ->update(['status_active' => in_array($config->id_detail_configuration, $selectedConfigurations) ? 1 : 0]);
            }
        }

        return redirect()->route('purchase.configuration')->with('status', 'Berhasil mengubah konfigurasi!');
    }

    public function create()
    {
        $invoiceNumber = $this->generateInvoiceNumber();
        $supplier = Supplier::all();
        $product = Product::all();

        $payProd = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 9)
            ->get();
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

        //detail pada supplier
        $hisPurchase = DB::table('purchase_orders')
            ->leftJoin('purchase_details', 'purchase_orders.id_purchase', '=', 'purchase_details.purchase_id')
            ->leftJoin('products', 'purchase_details.product_id', '=', 'products.id_product')
            ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id_supplier')
            ->select(
                'purchase_orders.purchase_invoice as purchase_invoice',
                'purchase_orders.date as date',
                'suppliers.name as supplier_name',
                'products.name as product_name',
                'purchase_details.total_quantity_product as total'
            )
            ->get();
        // dd($hisPurchase);
        $paymentMethod = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 1)
            ->where('dc.status_active', 1)
            ->select('dc.id_detail_configuration', 'dc.name')
            ->get();

        $pengiriman = DetailConfiguration::where('configuration_id', operator: 8)->where('status_active', 1)->get();
        // dd($pengiriman);
        return view('purchase.create', [
            'invoiceNumber' => $invoiceNumber,
            'supplier' => $supplier,
            'product' => $product,
            'warehouse' => $warehouse,
            'purchasePriceMethod' => $purchasePriceMethod,
            'receiveProdMethod' => $receiveProdMethod,
            'payProd' => $payProd,
            'hisPurchase' => $hisPurchase,
            'paymentMethod' => $paymentMethod,
            'pengiriman' => $pengiriman
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
        $purchase = new PurchaseOrder();
        $invoiceNumber = $this->generateInvoiceNumber();
        $purchase->purchase_invoice = $invoiceNumber;
        $purchase->date = $request->get(key: 'date');
        $purchase->expected_arrival = $request->get('expected_arrival') ?? null;
        $purchase->total_purchase = $request->get('total_price');
        $purchase->payment_method = $request->get('payment_method') ?? null;
        $purchase->supplier_id = $request->get('id_supplier');
        $purchase->employee_id = Auth::user()->employee->id_employee;
        $purchase->warehouse_id = $request->id_warehouse;
        // dd($request->all());
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

            $purchase->refreshCost($product, $request->get('date'), $request->get('metode_pengiriman'), $request->id_warehouse);
        }
        return redirect()->route('purchase.showNota', $purchase->id_purchase)->with('status', 'Nota berhasil dibuat!');
    }


    public function showData()
    {
        $data = DB::table('purchase_orders')
            ->join('purchase_details', 'id_purchase', '=', 'purchase_details.purchase_id')
            ->join('products', 'purchase_details.product_id', '=', 'products.id_product')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id_supplier')
            ->leftJoin('detail_configurations', 'purchase_orders.payment_method', '=', 'detail_configurations.id_detail_configuration')
            ->leftJoin('employees', 'purchase_orders.employee_id', '=', 'employees.id_employee')
            ->select(
                'purchase_orders.purchase_invoice as purchase_invoice',
                'purchase_orders.date as date',
                'suppliers.name as supplier_name',
                'products.name as product_name',
                'purchase_details.total_quantity_product as total_quantity_product',
                'purchase_orders.total_purchase as total_purchase',
                'detail_configurations.name as payment_method',
                'employees.name as emp_name'
            )->get();

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


    public function showProd($id)
    {
        $purchase = DB::table('purchase_orders')
            ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id_supplier')
            ->join('employees', 'purchase_orders.employee_id', '=', 'employees.id_employee')
            ->join('warehouses', 'purchase_orders.warehouse_id', '=', 'warehouses.id_warehouse')
            ->where('purchase_orders.id_purchase', $id)
            ->select(
                'purchase_orders.*',
                'suppliers.name as supplier_name',
                'employees.name as e_name',
                'warehouses.name as warehouse_name'
            )
            ->first();
        // dd($purchase);
        $purchaseDetail = DB::table('purchase_details')
            ->join('products', 'purchase_details.product_id', '=', 'products.id_product')
            ->where('purchase_details.purchase_id', $id)
            ->select('purchase_details.*', 'products.name as product_name')
            ->get();
        // dd($purchaseDetail);
        $gudang = Warehouse::all();

        $terima = DB::table('delivery_note_has_products as dnp')
            ->join('delivery_note as dn', 'dnp.delivery_note_id', '=', 'dn.id')
            ->where('dn.purchase_id', $id)
            ->select(
                'dn.purchase_id',
                'dnp.product_id',
                DB::raw('SUM(dnp.quantity) as total_terima')
            )
            ->groupBy('dn.purchase_id', 'dnp.product_id')
            ->get();
        // dd($terima);

        return view('purchase.terima', compact('purchase', 'purchaseDetail', 'gudang', 'terima'));
    }

    public function laporanTerimaProd()
    {
        $data = DB::table('delivery_note as dn')
            ->join('delivery_note_has_products as dnp', 'dn.id', '=', 'dnp.delivery_note_id')
            ->join('purchase_orders as po', 'po.id_purchase', '=', 'dn.purchase_id')
            ->join('products as p', 'p.id_product', '=', 'dnp.product_id')
            ->leftJoin('purchase_details as pd', function ($join) {
                $join->on('pd.purchase_id', '=', 'dn.purchase_id')
                    ->on('pd.product_id', '=', 'p.id_product'); // Hanya menghubungkan ke produk terkait
            })
            ->where('dn.type', 'terima')
            ->select(
                'dn.id as delivery_id',
                'dn.date as tanggal_kirim',
                'p.name as prod_name',
                DB::raw('SUM(dnp.quantity) as jumlah_terima'), // Menjumlahkan jumlah pengiriman
                'po.purchase_invoice as invoice',
                DB::raw('COALESCE(SUM(pd.total_quantity_product), 0) as jumlah_beli') // Menjumlahkan jumlah beli dengan nilai default 0
            )
            ->groupBy('dn.id', 'dn.date', 'p.name', 'po.purchase_invoice') // Kelompokkan untuk mencegah duplikasi
            ->get()
            ->groupBy('delivery_id');

        // dd($data);
        return view('purchase.laporanTerimaProd', compact('data'));
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
            ->where('configurations.id_configuration', 1)
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

    public function laporanLabaKotor()
    {
        $products = Product::all();
        $dataToko = StoreData::first();

        // Ambil metode perhitungan HPP (FIFO atau Average)
        $cogsChoose = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 5)
            ->first();

        if (!$cogsChoose) {
            return redirect()->back()->with('error', 'Konfigurasi COGS tidak ditemukan.');
        }

        $cogsMethod = $cogsChoose->name;

        $laporan = $products->map(function ($product) use ($cogsMethod) {
            if ($cogsMethod == 'FIFO') {
                // Ambil semua transaksi berdasarkan FIFO
                $productFifo = DB::table('product_fifo')
                    ->where('product_id', $product->id_product)
                    ->orderBy('purchase_date', 'asc') // FIFO = yang masuk pertama dijual dulu
                    ->get();

                if ($productFifo->isEmpty()) {
                    return null; // Jika tidak ada data FIFO, lewati produk ini
                }

                $jumlahTerjual = $productFifo->sum('sold');
                $pendapatan = $productFifo->sum(fn($fifo) => $fifo->price * $fifo->sold);
                $hpp = $productFifo->sum(fn($fifo) => $fifo->cost * $fifo->sold);
                $labaKotor = $pendapatan - $hpp;

            } elseif ($cogsMethod == "Average") {
                // Ambil jumlah terjual dari sales_details
                $jumlahTerjual = SalesDetail::where('product_id', $product->id_product)->sum('total_quantity');
                // dd($jumlahTerjual);
                // Ambil harga pokok rata-rata dari tabel products
                $cost = $product->cost; // Pastikan cost tidak null
                $hpp = $cost * $jumlahTerjual;
                $pendapatan = $product->price * $jumlahTerjual;
                $labaKotor = $pendapatan - $hpp;
            } else {
                return null; // Jika metode COGS tidak dikenali, lewati produk ini
            }

            return [
                'produk' => $product->name,
                'jumlah_terjual' => $jumlahTerjual,
                'hpp' => $hpp,
                'pendapatan' => $pendapatan,
                'laba_kotor' => $labaKotor,
            ];
        })->filter(); // Hapus data yang null
        // dd($laporan);
        return view('laporanlaba', compact('laporan', 'dataToko'));
    }


}
