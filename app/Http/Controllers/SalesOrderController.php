<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
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
        // $sales = SalesOrder::all();
       

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

        // Ambil semua konfigurasi terkait menu_id = 1
        $allConfigurations = DB::table('detail_configurations')
            ->join('configurations', 'detail_configurations.configuration_id', '=', 'configurations.id_configuration')
            ->where('configurations.menu_id', 1)
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
        $product = Product::all();
        // $payment = PaymentMethod::all();
        $paymentMethod = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 3)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.name')
            ->get();
        $discount = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 4)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.name')
            ->get();
        // dd($paymentMethod);
        return view('sales.create', compact(
            'invoiceNumber',
            'sales',
            'customer',
            'product',
            'payment',
            'paymentMethod',
            'discount'
        ));
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


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sales = new SalesOrder();
        $invoiceNumber = $this->generateInvoiceNumber();

        $sales->sales_invoice = $invoiceNumber;
        $sales->date = $request->get(key: 'date');
        $sales->total_price = $request->get('total_price');
        $sales->customer_id = $request->get('id_customer');
        $sales->payment_method = $request->get('payment_method');
        $sales->shipping_cost = $request->get('hShippingCost') ?? 0;
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
            $productModel = Product::find($product['id']); // Cari produk berdasarkan ID
            if ($productModel) {
                if ($productModel->total_stock < $product['quantity']) {
                    // Jika stok tidak mencukupi, lemparkan error
                    return redirect()->back()->with('error', "Stok produk {$productModel->name} tidak mencukupi!");
                }

                $productModel->total_stock -= $product['quantity']; // Kurangi stok
                $productModel->save(); // Simpan perubahan
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
