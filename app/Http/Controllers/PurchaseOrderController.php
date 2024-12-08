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
        $purchase = PurchaseOrder::all();
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
                in_array($config->id_detail_configuration, $configurations[$config->configuration_id])
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
        $payment = PaymentMethod::all();
        $warehouse = Warehouse::all();
        return view('purchase.create', compact('invoiceNumber', 'supplier', 'product', 'payment', 'warehouse'));
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

    public function addProduct(Request $request)
    {
        $product = Product::find($request->get('product_id'));
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $amount = $product->price * $request->get('quantity');
        return response()->json([
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $request->quantity,
            'amount' => $amount,
        ]);
    }
    public function calculateTotal(Request $request)
    {
        $totalAmount = array_sum(array_column($request->products, 'amount'));
        $shippingCost = $request->shipping_cost;
        $discount = $request->discount;
        $taxes = $totalAmount * 0.1;

        $total = $totalAmount + $shippingCost - $discount + $taxes;

        return response()->json([
            'total_amount' => $totalAmount,
            'taxes' => $taxes,
            'total' => $total,
        ]);
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
        $purchase->total_purchase = $request->get('total_price');
        $purchase->payment_method_id = $request->get('payment_method_id') ?? 1;
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
        }
        return redirect()->route('purchase.index')->with('status', 'Data Berhasil Disimpan');
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
