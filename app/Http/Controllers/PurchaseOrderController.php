<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\PurchaseOrder;
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
            ->get();

        $payProd = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 7)
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

        return redirect()->route('purchase.configuration')->with('status', 'Configurations updated successfully!');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $invoiceNumber = $this->generateInvoiceNumber();
        $supplier = Supplier::all();

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

        $warehouse = Warehouse::all();

        //mendapatkan konfigurasi harga beli 
        $purchasePriceMethod = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 5)
            ->first();

        //mendapatkan konfiguasi penerimaan barang
        $receiveProdMethod = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 6)
            ->first();

        return view('purchase.create', [
            'invoiceNumber' => $invoiceNumber,
            'supplier' => $supplier,
            'product' => $product,
            'paymentMethod' => $paymentMethod,
            'warehouse' => $warehouse,
            'purchasePriceMethod' => $purchasePriceMethod,
            'receiveProdMethod' => $receiveProdMethod
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
            ->where('configuration_id', 7)
            ->first();

        $purchase = new PurchaseOrder();
        $invoiceNumber = $this->generateInvoiceNumber();
        $purchase->purchase_invoice = $invoiceNumber;
        $purchase->date = $request->get(key: 'date');
        $purchase->expected_arrival = $request->get('expected_arrival');
        $purchase->total_purchase = $request->get('total_price');
        if ($payProd->id_detail_configuration == 16) {
            $purchase->payment_method = $request->get('payment_method') ?? 5;
        }
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

            if ($cogsMethod == 'Average') {
                $productData = DB::table('products')->where('id_product', $product['id'])->first();

                $oldStock = $productData->total_stock;
                $oldCost = $productData->cost;
                $oldPrice = $productData->price;
                $totalOldCost = $oldCost * $oldStock;

                $newStock = $product['quantity'];
                $totalNewCost = $product['amount'];
                $newCost = $totalNewCost / $newStock;

                $totalAllCost = $totalOldCost + $totalNewCost;
                $totalAllStock = $oldStock + $newStock;
                $averageCost = $totalAllCost / $totalAllStock;
                $ratioPrice = $oldPrice / $oldCost;

                $newPrice = $averageCost * $ratioPrice;

                DB::table('products')
                    ->where('id_product', $product['id'])
                    ->update([
                        'price' => $newPrice,
                        'cost' => $averageCost,
                        'total_stock' => $totalAllStock,
                    ]);

            } else if ($cogsMethod == 'FIFO') {
                $price = $product['amount'] / $product['quantity']; // Harga per unit
                DB::table('product_fifo')->insert([
                    'product_id' => $product['id'],
                    'stock' => $product['quantity'],
                    'purchase_date' => $request->get('date'),
                    'price' => $price,
                ]);

                // Update total stok di tabel products
                DB::table('products')
                    ->where('id_product', $product['id'])
                    ->increment('total_stock', $product['quantity']);
            }

            DB::table('product_moving')->insert([
                'move_stock' => $product['quantity'],
                'product_id' => $product['id'],
                'warehouse_id_in' => $request->get('id_warehouse'),
                'purchase_id' => $purchase->id_purchase,
            ]);

            DB::table('product_has_warehouses')
                ->where('product_id', $product['id'])
                ->increment('stock', $product['quantity']);
        }
        return redirect()->route('purchase.index')->with('status', 'Data successfully added');
    }


    public function showData()
    {
        $data = DB::table('product_moving')
            ->join('products', 'product_moving.product_id', '=', 'products.id_product')
            ->join('purchase_orders', 'product_moving.purchase_id', '=', 'purchase_orders.id_purchase')
            ->select(
                'purchase_orders.purchase_invoice as purchase_invoice',
                'products.name as product_name',
                'product_moving.move_stock as stock'
            )
            ->get();
        return view('purchase.datapurchase', ['data' => $data]);
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


        return redirect()->route('purchase.index')->with('status', 'Successfully made a payment!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        //
    }
}
