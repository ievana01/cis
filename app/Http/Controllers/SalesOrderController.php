<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Configuration;
use App\Models\Customer;
use App\Models\DetailConfiguration;
use App\Models\PaymentMethod;
use App\Models\PayModel;
use App\Models\Product;
use App\Models\ProductHasWarehouse;
use App\Models\ProductMoving;
use App\Models\SalesDetail;
use App\Models\SalesOrder;
use App\Models\SeasonDiscount;
use App\Models\StoreData;
use App\Models\Warehouse;
use Auth;
use Carbon\Carbon;
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
                'customers.name as customer_name',
                'sales_orders.customer_name as custname',
                'detail_configurations.name as payment_method_name'
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
        $category = Category::all();
        // dd($configuration);
        return view('sales.configuration', ['configuration' => $configuration, 'category' => $category]);
    }

    public function save(Request $request)
    {
        // dd(request()->all());
        $configurations = $request->input('configurations', []);
        $discountValues = $request->input('discount_values', []);
        $taxValues = $request->input('tax_values', []);
        $allConfigurations = DB::table('detail_configurations')
            ->join('configurations', 'detail_configurations.configuration_id', '=', 'configurations.id_configuration')
            ->where('configurations.menu_id', 1)
            ->select(
                'detail_configurations.id_detail_configuration',
                'detail_configurations.configuration_id',
                'detail_configurations.type',
                'detail_configurations.value',
                'detail_configurations.name'
            )
            ->get();
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
                DB::table('detail_configurations')
                    ->where('id_detail_configuration', $config->id_detail_configuration)
                    ->update(['value' => $discountValue]);
            }

            if ($config->id_detail_configuration == 16 && isset($taxValues[16])) {
                $taxValues = $taxValues[16];
                DB::table('detail_configurations')
                    ->where('id_detail_configuration', 16)
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
        $paymentMethod = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 1)
            ->where('dc.status_active', 1)
            ->select('dc.id_detail_configuration', 'dc.name')
            ->get();
        $taxRate = 0;
        $tax = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 6)
            ->where('dc.status_active', 1)
            ->select('dc.value')
            ->first();
        if ($tax) {
            $taxRate = floatval(str_replace('%', '', $tax->value)) / 100; // Menghasilkan 0.11 untuk 11%
        }
        $shippingMethod = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 2)
            ->where('dc.status_active', 1)
            ->select('dc.*')
            ->get();
        $multiDiskon = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 3)
            ->where('dc.status_active', 1)
            ->select('dc.id_detail_configuration', 'dc.name')
            ->get();
        $pengiriman = DetailConfiguration::where('configuration_id', operator: 2)->where('status_active', 1)->get();
        $discountUmum = DB::table('configurations as c')
            ->join('detail_configurations as dc', 'c.id_configuration', '=', 'dc.configuration_id')
            ->where('c.id_configuration', 4)
            ->where('dc.status_active', 1)
            ->select('dc.*')
            ->get();
        $diskonMusim = DetailConfiguration::where('id_detail_configuration', operator: 9)->where('status_active', 1)->first();
        // Ambil Diskon Musim yang aktif
        $seasonDiscounts = DB::table('season_discount as sd')
            ->join('season_discount_has_categories as sdc', 'sd.id', '=', 'sdc.season_discount_id')
            ->join('categories as c', 'sdc.category_id', '=', 'c.id_category')
            ->join('detail_configurations as dc', 'sd.detail_configuration_id', '=', 'dc.id_detail_configuration')
            ->select(
                'sd.id as season_discount_id',
                'sd.name as season_discount_name',
                'sd.start_date',
                'sd.end_date',
                'sdc.category_id',
                'sdc.season_value',
                'c.name as category_name',
                'dc.name as detail_configuration_name'
            )
            ->where('sd.detail_configuration_id', 9)
            ->whereNull('sdc.deleted_at')
            ->where('sd.end_date', '>=', now())
            ->where('sd.start_date', '<=', now())
            ->get();
        // dd($seasonDiscounts);
        // Format hasil query agar memiliki struktur nested array
        $groupedSeasonDiscounts = $seasonDiscounts->groupBy('season_discount_id')->map(function ($items) {
            $firstItem = $items->first();
            return [
                'jenis' => $firstItem->detail_configuration_name,
                'id' => $firstItem->season_discount_id,
                'name' => $firstItem->season_discount_name,
                'start_date' => $firstItem->start_date,
                'end_date' => $firstItem->end_date,
                'categories' => $items->map(function ($item) {
                    return [
                        'category_id' => $item->category_id,
                        'category_name' => $item->category_name,
                        'season_value' => $item->season_value,
                    ];
                })->values(),
            ];
        })->values();
        // dd($groupedSeasonDiscounts);
        return view('sales.create', [
            "invoiceNumber" => $invoiceNumber,
            "sales" => $sales,
            "customer" => $customer,
            "product" => $product,
            "paymentMethod" => $paymentMethod,
            "taxRate" => $taxRate,
            "shippingMethod" => $shippingMethod,
            "multiDiskon" => $multiDiskon,
            "pengiriman" => $pengiriman,
            'discountUmum' => $discountUmum,
            'groupedSeasonDiscounts' => $groupedSeasonDiscounts,
            'diskonMusim' => $diskonMusim,
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
            ->leftJoin('employees', 'sales_orders.employee_id', '=', 'employees.id_employee')
            ->select(
                'sales_orders.sales_invoice as sales_invoice',
                'sales_orders.date as date',
                'sales_orders.customer_name as cust_name',
                'customers.name as cust_name_by_id',
                'products.name as product_name',
                'sales_details.total_quantity as total_quantity',
                'employees.name as emp_name'
            )
            ->orderBy('sales_orders.sales_invoice', 'asc')
            ->get();
        return view('sales.datasales', ['data' => $data]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $customerId = $request->get('id_customer');
        $paymentMethod = $request->get('payment_method');
        $payAktif = (new PayModel())->cekPay($paymentMethod);
        if (!$payAktif) {
            return response()->json([
                'status' => 'error',
                'message' => 'Metode pembayaran yang dipilih tidak aktif.'
            ], 400);
        }
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
        $sales->payment_method = $paymentMethod;
        $sales->shipping_cost = $request->get(key: 'hShippingCost') ?? 0;
        $sales->discount = $request->get('hDiscount') ?? 0;
        $sales->tax = $request->get('tax');
        $sales->employee_id = Auth::user()->employee->id_employee;

        $sales->recipient_name = $request->get('recipient_name') ?? null;
        $sales->recipient_address = $request->get('recipient_address') ?? null;
        $sales->recipient_phone_num = $request->get('recipient_phone_num') ?? null;
        $sales->card_number = $request->get('card_number') ?? null;
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
            $sales->refreshCostSales($product, $request->metode_pengiriman);

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
        $salesDetail = DB::table('sales_details')
            ->join('products', 'sales_details.product_id', '=', 'products.id_product')
            ->where('sales_details.sales_id', $id)
            ->select('sales_details.*', 'products.name as product_name')
            ->get();
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
        $salesDetail = DB::table('sales_details')
            ->join('products', 'sales_details.product_id', '=', 'products.id_product')
            ->where('sales_details.sales_id', $id)
            ->select('sales_details.*', 'products.name as product_name')
            ->get();
        $gudang = Warehouse::all();
        $stokProd = DB::table('product_has_warehouses as phw')
            ->join('warehouses as w', 'phw.warehouse_id', '=', 'w.id_warehouse')
            ->join('products as p', 'phw.product_id', '=', 'p.id_product')
            ->select('phw.*', 'w.name', 'p.name as product_name')
            ->get();
        if (!$sales || $salesDetail->isEmpty()) {
            return redirect()->back()->with('error', 'Sales order not found');
        }

        $terkirim = DB::table('delivery_note_has_products as dnp')
            ->join('delivery_note as dn', 'dnp.delivery_note_id', '=', 'dn.id')
            ->where('dn.sales_id', $id)
            ->select(
                'dn.sales_id',
                'dnp.product_id',
                DB::raw('SUM(dnp.quantity) as total_terkirim')
            )
            ->groupBy('dn.sales_id', 'dnp.product_id')
            ->get();
        // dd($terkirim);

        return view('sales.kirim', compact('sales', 'salesDetail', 'gudang', 'stokProd', 'terkirim'));
    }

    public function getDiskon(Request $request)
    {
        $id = $request->id;
        $diskonMusim = DB::table('season_discount as sd')
            ->join('season_discount_has_categories as sdc', 'sd.id', '=', 'sdc.season_discount_id')
            ->join('categories as c', 'sdc.category_id', '=', 'c.id_category')
            ->select(
                'sd.id as season_discount_id',
                'sd.name as season_discount_name',
                'sd.start_date',
                'sd.end_date',
                'sdc.category_id',
                'sdc.season_value',
                'c.name as category_name'
            )
            ->where('sd.detail_configuration_id', $id)
            ->where('sd.end_date', '>=', now())
            ->whereNull('sdc.deleted_at')
            ->get();
        // Mengelompokkan hasil query agar memiliki format nested array
        $groupedData = $diskonMusim->groupBy('season_discount_id')->map(function ($items) {
            $firstItem = $items->first(); // Ambil data utama (season discount)
            return [
                'id' => $firstItem->season_discount_id,
                'name' => $firstItem->season_discount_name,
                'start_date' => $firstItem->start_date,
                'end_date' => $firstItem->end_date,
                'categories' => $items->map(function ($item) {
                    return [
                        'category_id' => $item->category_id,
                        'category_name' => $item->category_name,
                        'season_value' => $item->season_value,
                    ];
                })->values(), // Reset indeks array
            ];
        })->values(); // Reset indeks array utama
        $category = Category::all();
        return response()->json(array(
            'status' => 'oke',
            'msg' => view('sales.getDiskon', ['diskonMusim' => $groupedData], ['category' => $category])->render()
        ), 200);
    }

    public function hapusKategoriDiskon(Request $request)
    {
        DB::table('season_discount_has_categories')
            ->where('category_id', $request->category_id)
            ->where('season_discount_id', $request->discount_id)
            ->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus!']);
    }

    public function laporanKirimProd()
    {
        $data = DB::table('delivery_note as dn')
            ->join('delivery_note_has_products as dnp', 'dn.id', '=', 'dnp.delivery_note_id')
            ->join('sales_orders as so', 'so.id_sales', '=', 'dn.sales_id')
            ->join('products as p', 'p.id_product', '=', 'dnp.product_id')
            ->leftJoin('sales_details as sd', function ($join) {
                $join->on('sd.sales_id', '=', 'dn.sales_id')
                    ->on('sd.product_id', '=', 'p.id_product'); // Hanya menghubungkan ke produk terkait
            })
            ->where('dn.type', 'pengiriman')
            ->select(
                'dn.id as delivery_id',
                'dn.date as tanggal_kirim',
                'p.name as prod_name',
                DB::raw('SUM(dnp.quantity) as jumlah_kirim'), // Menjumlahkan jumlah pengiriman
                'so.sales_invoice as invoice',
                DB::raw('COALESCE(SUM(sd.total_quantity), 0) as jumlah_beli') // Menjumlahkan jumlah beli dengan nilai default 0
            )
            ->groupBy('dn.id', 'dn.date', 'p.name', 'so.sales_invoice') // Kelompokkan untuk mencegah duplikasi
            ->get()
            ->groupBy('delivery_id');

        // dd($data);
        return view('sales.laporanKirimProd', compact('data'));
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
