<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Customer;
use App\Models\DetailConfiguration;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductHasWarehouse;
use App\Models\ProductMoving;
use App\Models\SalesDetail;
use App\Models\SalesOrder;
use App\Models\StoreData;
use App\Models\Warehouse;
use Auth;
use DB;
use Illuminate\Http\Request;
use Log;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = DB::table('sales_orders')
            ->leftJoin('customers', 'sales_orders.customer_id', '=', 'customers.id_customer')
            ->leftJoin('detail_configurations', 'sales_orders.payment_method', '=', 'detail_configurations.id_detail_configuration')
            ->select(
                'sales_orders.id_sales',
                'sales_orders.sales_invoice',
                'sales_orders.date',
                'sales_orders.delivery_date',
                'sales_orders.total_price',
                'customers.name as customer_name',  // Menampilkan nama customer dari tabel customers
                'sales_orders.customer_name as custname',  // Menampilkan nama customer yang diinput manual
                'detail_configurations.name as payment_method_name'  // Menampilkan metode pembayaran
            )
            // ->where('employee_id', Auth::user()->employee->id_employee)
            ->orderBy('sales_invoice', 'desc')
            ->get();
        return view('sales.index', ["sales" => $sales]);
    }

    public function showConfiguration()
    {
        $configuration = DB::select('select * from configurations where menu_id = 1');
        foreach ($configuration as $config) {
            $config->details = DB::select('select * from detail_configurations where configuration_id = ?', [$config->id_configuration]);
        }
        return view('sales.configuration', ['configuration' => $configuration]);
    }

    public function save(Request $request)
    {
        $configurations = $request->input('configurations', []);
        $discountValues = $request->input('discount_values', []);
        $taxValues = $request->input('tax_values', []);
        // dd($taxValues);
        $allConfigurations = DB::table('detail_configurations')
            ->join('configurations', 'detail_configurations.configuration_id', '=', 'configurations.id_configuration')
            ->where('configurations.menu_id', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.configuration_id', 'detail_configurations.type', 'detail_configurations.value')
            ->get();
        // dd($allConfigurations);
        foreach ($allConfigurations as $config) {
            $isActive = 0;
            if (isset($configurations[$config->configuration_id]) && is_array($configurations[$config->configuration_id])) {
                if (in_array($config->id_detail_configuration, $configurations[$config->configuration_id])) {
                    $isActive = 1;
                }
            } else if (isset($configurations[$config->configuration_id]) && $configurations[$config->configuration_id] == $config->id_detail_configuration) {
                $isActive = 1;
            }
            if ($config->type === 'mandatory') {
                $isActive = 1;
            }
            DB::table('detail_configurations')
                ->where('id_detail_configuration', $config->id_detail_configuration)
                ->update(['status_active' => $isActive]);

            if (isset($discountValues[$config->id_detail_configuration])) {
                $discountValue = $discountValues[$config->id_detail_configuration];
                // Update the discount value in the database (if needed)
                DB::table('detail_configurations')
                    ->where('id_detail_configuration', $config->id_detail_configuration)
                    ->update(['value' => $discountValue]);
            }

            if ($config->id_detail_configuration == 4 && isset($taxValues[4])) {
                $taxValues = $taxValues[4];
                DB::table('detail_configurations')
                    ->where('id_detail_configuration', 4)
                    ->update(['value' => $taxValues]);
            }
        }
        return redirect()->route('sales.configuration')->with('status', 'Sukses mengubah konfigurasi!');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $invoiceNumber = $this->generateInvoiceNumber();
        $sales = SalesOrder::all();
        $customer = Customer::all();
        $product = Product::all();
        // dd($product);
        $paymentMethod = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 3)
            ->where('dc.status_active', 1)
            ->select('dc.id_detail_configuration', 'dc.name')
            ->get();

        $taxRate = 0;
        $tax = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 2)
            ->where('dc.status_active', 1)
            ->select('dc.value')
            ->first();
        // dd($tax);
        if ($tax) {
            $taxRate = floatval(str_replace('%', '', $tax->value)) / 100; // Menghasilkan 0.11 untuk 11%
        }

        $discount = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 6)
            ->where('dc.status_active', 1)
            ->select('dc.*')
            ->get();

        $shippingMethod = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 4)
            ->where('dc.status_active', 1)
            ->select('dc.*')
            ->get();
        // dd($shippingMethod);

        $multiDiskon = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 5)
            ->where('dc.status_active', 1)
            ->select('dc.id_detail_configuration', 'dc.name')
            ->get();
        // dd($multiDiskon);

        $pengiriman = DetailConfiguration::where('configuration_id', operator: 4)->where('status_active', 1)->get();
        // dd($pengiriman);
        return view('sales.create', [
            "invoiceNumber" => $invoiceNumber,
            "sales" => $sales,
            "customer" => $customer,
            "product" => $product,
            "paymentMethod" => $paymentMethod,
            "taxRate" => $taxRate,
            "discount" => $discount,
            "shippingMethod" => $shippingMethod,
            "multiDiskon" => $multiDiskon,
            "pengiriman" => $pengiriman
        ]);
    }

    public function generateInvoiceNumber()
    {
        $lastInvoice = SalesOrder::orderBy('id_sales', 'desc')->first();
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->sales_invoice, 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        return 'S' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function showData()
    {
        $data = DB::table('sales_orders')
            ->leftJoin('sales_details', 'sales_orders.id_sales', '=', 'sales_details.sales_id')
            ->leftJoin('products', 'sales_details.product_id', '=', 'products.id_product')
            ->leftJoin('customers', 'sales_orders.customer_id', '=', 'customers.id_customer')
            ->select(
                'sales_orders.sales_invoice as sales_invoice',
                'sales_orders.date as date',
                'sales_orders.customer_name as cust_name',
                'customers.name as cust_name_by_id',
                'products.name as product_name',
                'sales_details.total_quantity as total_quantity'
            )
            ->orderBy('sales_orders.sales_invoice', 'asc')
            ->get();
        // $data = DB::table('sales_orders')
        //     ->leftJoin('product_moving', 'sales_orders.id_sales', '=', 'product_moving.sales_id')
        //     ->leftJoin('products', 'product_moving.product_id', '=', 'products.id_product')
        //     ->leftJoin('customers', 'sales_orders.customer_id', '=', 'customers.id_customer')
        //     ->select(
        //         'sales_orders.sales_invoice as sales_invoice',
        //         'sales_orders.customer_name as cust_name',
        //         'customers.name as cust_name_by_id',
        //         'products.name as product_name',
        //         'product_moving.*'
        //     )
        //     ->orderBy('sales_invoice', 'asc')
        //     ->get();
        // dd($data);
        return view('sales.datasales', ['data' => $data]);
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

        $customerId = $request->get('id_customer');
        // dd(request()->all());
        $sales = new SalesOrder();
        $invoiceNumber = $this->generateInvoiceNumber();
        $sales->sales_invoice = $invoiceNumber;
        $sales->date = $request->get(key: 'date');
        $sales->delivery_date = $request->get(key: 'delivery_date');
        $sales->total_price = $request->get('total_price');
        if ($customerId == 'other') {
            $sales->customer_name = $request->get('customer_name');
        } else {
            $sales->customer_id = $request->get('id_customer');
        }
        $sales->customer_name = $request->get('customer_name');
        $sales->payment_method = $request->get('payment_method');
        $sales->shipping_cost = $request->get(key: 'hShippingCost') ?? 0;
        $sales->discount = $request->get('hDiscount') ?? 0;
        $sales->tax = $request->get('tax');
        $sales->employee_id = Auth::user()->employee->id_employee;

        $sales->save();

        $products = json_decode($request->get('products'), true);
        foreach ($products as $product) {
            DB::table('sales_details')->insert(
                [
                    'sales_id' => $sales->id_sales,
                    'product_id' => $product['id'],
                    'total_quantity' => $product['quantity'],
                    'total_price' => $product['amount'],
                ]
            );

            //ambil warehouse product di product_has_warehouse
            $warehouse = DB::table('product_has_warehouses')
                ->where('product_id', $product['id'])
                ->first();

            if ($request->metode_pengiriman == 'diambil') {
                if ($cogsMethod == 'FIFO') {
                    $quantity = $product['quantity'];
                    $stocks = DB::table('product_fifo')
                        ->where('product_id', $product['id'])
                        ->orderBy('purchase_date', 'asc') // Urutkan berdasarkan tanggal pembelian
                        ->get();

                    foreach ($stocks as $stock) {
                        if ($quantity <= 0) {
                            break; // Jika tidak ada stok yang tersisa untuk dikurangi, hentikan loop
                        }

                        // Tentukan stok yang bisa dikurangi dari stok ini
                        $toDecrement = min($quantity, $stock->initial_stock - $stock->sold);

                        // Jika stok habis atau ada stok yang cukup untuk dikurangi
                        if ($toDecrement > 0) {
                            // Mengurangi stok di product_fifo
                            DB::table('product_fifo')
                                ->where('id_product_fifo', $stock->id_product_fifo) // Pastikan hanya mengupdate stok yang sesuai
                                ->increment('sold', $toDecrement);

                            DB::table('products')
                                ->where('id_product', $product['id'])
                                ->decrement('total_stock', $toDecrement);

                            // Simpan pergerakan stok di product_moving
                            // DB::table('product_moving')->insert([
                            //     'product_id' => $product['id'],
                            //     'move_stock' => $toDecrement,
                            //     'date' => $sales->date,
                            //     'warehouse_id_in' => $warehouse->warehouse_id,
                            //     'sales_id' => $sales->id_sales
                            // ]);

                            // Update stok di product_has_warehouses jika perlu
                            DB::table('product_has_warehouses')
                                ->where('product_id', $product['id'])
                                ->decrement('stock', $toDecrement);

                            // Kurangi jumlah yang akan diproses
                            $quantity -= $toDecrement;
                        }
                    }
                } else if ($cogsMethod == 'Average') {
                    DB::table('products')
                        ->where('id_product', $product['id'])
                        ->decrement('total_stock', $product['quantity']);

                    // DB::table('product_moving')->insert([
                    //     'product_id' => $product['id'],
                    //     'move_stock' => $product['quantity'],
                    //     'date' => $sales->date,
                    //     'warehouse_id_in' => $warehouse->warehouse_id,
                    //     'sales_id' => $sales->id_sales,
                    // ]);

                    DB::table('product_has_warehouses')
                        ->where('product_id', $product['id'])
                        ->decrement('stock', $product['quantity']);
                }
            } else if ($request->metode_pengiriman == 'dikirim') {
                if ($cogsMethod == 'FIFO') {
                    DB::table('products')
                        ->where('id_product', $product['id'])
                        ->increment('in_order', $product['quantity']);

                    DB::table('product_fifo')
                        ->where('product_id', $product['id'])
                        ->increment('in_order', $product['quantity']);
                } else if ($cogsMethod == 'Average') {
                    DB::table('products')
                        ->where('id_product', $product['id'])
                        ->increment('in_order', $product['quantity']);
                }
            }

            // $sales->refreshCostSales($cogsMethod, $product, $warehouse, $sales);

        }
        // return redirect()->route('sales.index')->with('status', 'Data Berhasil Disimpan');
        return redirect()->route('sales.showNota', $sales->id_sales)->with('status', 'Nota berhasil dibuat!');
    }

    public function getNota(Request $request)
    {
        $id_sales = $request->input('id_sales');

        if (empty($id_sales)) {
            return response()->json(['error' => 'ID Sales kosong'], 400);
        }

        $sales = SalesOrder::find($id_sales);

        if (!$sales) {
            return response()->json(['error' => 'Sales order dengan ID tersebut tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Data successfully processed',
            'id_sales' => $id_sales,
        ]);
    }

    public function showNota($id)
    {
        $dataToko = StoreData::first();
        // $sales = DB::table('sales_orders')->where('id_sales', $id)->first();
        // $sales = DB::table('sales_orders')
        //     ->join('customers', 'sales_orders.customer_id', '=', 'customers.id_customer')
        //     ->where('sales_orders.id_sales', $id)
        //     ->select('sales_orders.*', 'customers.name as customer_name')
        //     ->first();
        $sales = DB::table('sales_orders')
            ->leftJoin('customers', 'sales_orders.customer_id', '=', 'customers.id_customer')
            ->join('employees', 'sales_orders.employee_id', '=', 'employees.id_employee')
            ->where('sales_orders.id_sales', $id)
            ->select(
                'sales_orders.*',
                'customers.name as customer_name_by_id',
                'employees.name as e_name'
            )
            ->first();
        // dd($sales);
        $salesDetail = DB::table('sales_details')
            ->join('products', 'sales_details.product_id', '=', 'products.id_product')
            ->where('sales_details.sales_id', $id)
            ->select('sales_details.*', 'products.name as product_name')
            ->get();
        // dd($salesDetail);
        if (!$sales || $salesDetail->isEmpty()) {
            return redirect()->back()->with('error', 'Sales order not found');
        }

        return view('sales.nota', compact('sales', 'salesDetail', 'dataToko'));
    }

    public function showProd($id)
    {
        $sales = DB::table('sales_orders')
            ->leftJoin('customers', 'sales_orders.customer_id', '=', 'customers.id_customer')
            ->join('employees', 'sales_orders.employee_id', '=', 'employees.id_employee')
            ->where('sales_orders.id_sales', $id)
            ->select(
                'sales_orders.*',
                'customers.name as customer_name_by_id',
                'employees.name as e_name'
            )
            ->first();
        // dd($sales);
        $salesDetail = DB::table('sales_details')
            ->join('products', 'sales_details.product_id', '=', 'products.id_product')
            ->where('sales_details.sales_id', $id)
            ->select('sales_details.*', 'products.name as product_name')
            ->get();
        // dd($salesDetail);
        $gudang = Warehouse::all();

        $stokProd = DB::table('product_has_warehouses as phw')
            ->join('warehouses as w', 'phw.warehouse_id', '=', 'w.id_warehouse')
            ->join('products as p', 'phw.product_id', '=', 'p.id_product')
            ->select('phw.*', 'w.name', 'p.name as product_name')
            ->get();
        // dd($stokProd);
        if (!$sales || $salesDetail->isEmpty()) {
            return redirect()->back()->with('error', 'Sales order not found');
        }

        $terkirim = DB::table('sales_details as sd')
            ->join('delivery_note as dn', 'sd.sales_id', '=', 'dn.sales_id')
            ->join('delivery_note_has_products as dnp', 'dn.id', '=', 'dnp.delivery_note_id')
            ->where('sd.sales_id', $id)
            ->select('dnp.quantity as terkirim', 'sd.total_quantity as jumlah', 'dnp.product_id')
            ->get();
        // dd($terkirim);
        return view('sales.kirim', compact('sales', 'salesDetail', 'gudang', 'stokProd', 'terkirim'));
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesOrder $salesOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesOrder $salesOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesOrder $salesOrder)
    {
        //
    }
}
