<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use DB;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $purchase = PurchaseOrder::all();
        $purchase = DB::table('purchase_orders')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id_supplier')
            ->join('detail_configurations', 'purchase_orders.payment_method', '=', 'detail_configurations.id_detail_configuration')
            ->select(
                'purchase_orders.purchase_invoice',
                'purchase_orders.date',
                'purchase_orders.total_purchase',
                'suppliers.name as supplier_name',
                'detail_configurations.name as payment_method_name'
            )
            ->get();
        return view('purchase.index', ["purchase" => $purchase]);
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
        // dd($configurations);

        // Ambil semua konfigurasi terkait menu_id = 2
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
        $product = Product::all();
        $paymentMethod = DB::table('configurations')
            ->join('detail_configurations', 'configurations.id_configuration', '=', 'detail_configurations.configuration_id')
            ->where('configurations.id_configuration', 3)
            ->where('detail_configurations.status_active', 1)
            ->select('detail_configurations.id_detail_configuration', 'detail_configurations.name')
            ->get();
        $warehouse = Warehouse::all();
        return view('purchase.create', compact('invoiceNumber', 'supplier', 'product', 'paymentMethod', 'warehouse'));
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

        $purchase = new PurchaseOrder();
        $invoiceNumber = $this->generateInvoiceNumber();
        $purchase->purchase_invoice = $invoiceNumber;
        $purchase->date = $request->get(key: 'date');
        $purchase->total_purchase = $request->get('total_price');
        $purchase->payment_method = $request->get('payment_method') ?? 5;
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

            DB::table('product_moving')->insert([
                'move_stock' => $product['quantity'],
                'product_id' => $product['id'],
                'warehouse_id_in' => $request->get('id_warehouse'),
                'purchase_id' => $purchase->id_purchase,
            ]);

            if ($cogsMethod == 'Average') {
                $productData = DB::table('products')->where('id_product', $product['id'])->first();
                $oldStock = $productData->total_stock; //data stok lama
                $oldPrice = $productData->price; //data harga jual lama
                $oldCost = $productData->cost; //data harga beli lama

                // Hitung harga rata-rata baru
                $newStock = $product['quantity'];
                $newPrice = $product['totalAmount'] / $newStock; //data harga beli per unit baru 

                $totalCost = ($oldStock * $oldPrice) + ($newStock * $newPrice); //Total biaya produk lama + biaya produk baru

                $newAveragePrice = $totalCost / ($oldStock + $newStock); //Harga rata-rata baru berdasarkan stok total

                DB::table('products')
                    ->where('id_product', $product['id'])
                    ->update([
                        'price' => $newAveragePrice,
                        'cost' => $newAveragePrice,
                        'total_stock' => $oldStock + $newStock,
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

            DB::table('product_has_warehouses')
                ->where('product_id', $product['id'])
                ->increment('stock', $product['quantity']);
        }
        return redirect()->route('purchase.index')->with('status', 'Data successfully added');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        //
    }
}
