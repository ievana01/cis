<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductMoving;
use App\Models\SalesDetail;
use App\Models\SalesOrder;
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
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id_customer')
            ->join('detail_configurations', 'sales_orders.payment_method', '=', 'detail_configurations.id_detail_configuration')
            ->select(
                'sales_orders.sales_invoice',
                'sales_orders.date',
                'sales_orders.total_price',
                'customers.name as customer_name',
                'detail_configurations.name as payment_method_name'
            )
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
        $allConfigurations = DB::table('detail_configurations')
            ->join('configurations', 'detail_configurations.configuration_id', '=', 'configurations.id_configuration')
            ->where('configurations.menu_id', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.configuration_id')
            ->get();
        foreach ($allConfigurations as $config) {
            $isActive = 0; 
            if (isset($configurations[$config->configuration_id]) && is_array($configurations[$config->configuration_id])) {
                if (in_array($config->id_detail_configuration, $configurations[$config->configuration_id])) {
                    $isActive = 1;
                }
            }
            else if (isset($configurations[$config->configuration_id]) && $configurations[$config->configuration_id] == $config->id_detail_configuration) {
                $isActive = 1;
            }
            DB::table('detail_configurations')
                ->where('id_detail_configuration', $config->id_detail_configuration)
                ->update(['status_active' => $isActive]);
        }
        return redirect()->route('sales.configuration')->with('status', 'Configurations updated successfully!');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $invoiceNumber = $this->generateInvoiceNumber();
        $sales = SalesOrder::all();
        $customer = Customer::all();

        $cogsChoose = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 1)
            ->first();
        $cogsMethod = $cogsChoose->name;
        if ($cogsMethod == "FIFO") {
            $product = DB::table('products as p')
                ->join('product_fifo as pf', 'p.id_product', '=', 'pf.product_id')
                ->select('p.id_product as id_product', 'p.name as name', 'pf.price as price', 'p.cost as cost', DB::raw('SUM(pf.stock) as total_stock'))
                ->groupBy('p.id_product', 'p.name', 'p.total_stock', 'pf.price', 'p.cost')
                ->get();
        } else {
            $product = Product::all();
        }

        $paymentMethod = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 3)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.name')
            ->get();

        $taxRate = 0;
        $tax = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 2)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.name')
            ->first();

        if ($tax) {
            $taxRate = floatval(str_replace('%', '', $tax->name)) / 100; // Menghasilkan 0.11 untuk 11%
        }

        $discount = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 4)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.name')
            ->get();

        return view('sales.create', [
            "invoiceNumber" => $invoiceNumber,
            "sales" => $sales,
            "customer" => $customer,
            "product" => $product,
            "paymentMethod" => $paymentMethod,
            "taxRate" => $taxRate,
            "discount" => $discount
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
        $data = DB::table('product_moving')
            ->join('products', 'product_moving.product_id', '=', 'products.id_product')
            ->join('sales_orders', 'product_moving.sales_id', '=', 'sales_orders.id_sales')
            ->select(
                'sales_orders.sales_invoice as sales_invoice',
                'products.name as product_name',
                'product_moving.move_stock as stock'
            )
            ->get();
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

        $sales = new SalesOrder();
        $invoiceNumber = $this->generateInvoiceNumber();
        $sales->sales_invoice = $invoiceNumber;
        $sales->date = $request->get(key: 'date');
        $sales->total_price = $request->get('total_price');
        $sales->customer_id = $request->get('id_customer');
        $sales->payment_method = $request->get('payment_method');
        $sales->shipping_cost = $request->get(key: 'hShippingCost') ?? 0;
        $sales->discount = $request->get('hDiscount') ?? 0;
        $sales->employee_id = $request->get('employee_id') ?? 1;
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
                    $toDecrement = min($quantity, $stock->stock);

                    // Jika stok habis atau ada stok yang cukup untuk dikurangi
                    if ($toDecrement > 0) {
                        // Mengurangi stok di product_fifo
                        DB::table('product_fifo')
                            ->where('product_id', $stock->product_id)
                            ->where('id_product_fifo', $stock->id_product_fifo) // Pastikan hanya mengupdate stok yang tepat
                            ->decrement('stock', $toDecrement);

                        DB::table('products')
                            ->where('id_product', $product['id'])
                            ->decrement('total_stock', $toDecrement);

                        // Simpan pergerakan stok di product_moving
                        DB::table('product_moving')->insert([
                            'product_id' => $product['id'],
                            'move_stock' => $toDecrement,
                            'warehouse_id_in' => $warehouse->warehouse_id,
                            'sales_id' => $sales->id_sales
                        ]);

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

                DB::table('product_moving')->insert([
                    'product_id' => $product['id'],
                    'move_stock' => $product['quantity'],
                    'warehouse_id_in' => $warehouse->warehouse_id,
                    'sales_id' => $sales->id_sales,
                ]);

                DB::table('product_has_warehouses')
                    ->where('product_id', $product['id'])
                    ->decrement('stock', $product['quantity']);
            }
        }

        return redirect()->route('sales.index')->with('status', 'Data Berhasil Disimpan');
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
