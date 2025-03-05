<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $primaryKey = 'id_purchase';

    public $timestamps = false;
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id_supplier');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id_employee');
    }
    public function paymentMethod()
    {
        return $this->belongsTo(DetailConfiguration::class, 'payment_method', 'id_detail_configuration');
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id');
    }

    public function refreshCost($product, $date, $metode_pengiriman, $warehouse_id)
    {
        $cogsChoose = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 5)
            ->first();
        $cogsMethod = $cogsChoose->name;

        if ($metode_pengiriman == 'diambil') {
            if ($cogsMethod == 'Average') {
                $productData = DB::table('products')->where('id_product', $product['id'])->first();
                $profit = $productData->profit / 100;

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
                // $ratioPrice = $oldPrice / $oldCost;

                $newPrice = ($averageCost * $profit) + $averageCost;

                DB::table('products')
                    ->where('id_product', $product['id'])
                    ->update([
                        'price' => $newPrice,
                        'cost' => $averageCost,
                        'total_stock' => $totalAllStock,
                    ]);

            } else if ($cogsMethod == 'FIFO') {
                $productData = DB::table('products')->where('id_product', $product['id'])->first();
                $profit = $productData->profit / 100;
                $cost = $product['amount'] / $product['quantity'];
                $price = ($cost * $profit) + $cost;
                $totalStock = $productData->total_stock + $product['quantity'];

                DB::table('product_fifo')->insert([
                    'product_id' => $product['id'],
                    'initial_stock' => $product['quantity'],
                    'purchase_date' => $date,
                    'cost' => $cost,
                    'price' => $price,
                    'sold' => 0
                ]);

                DB::table('products')
                    ->where('id_product', $product['id'])
                    ->update([
                        'total_stock' => $totalStock,
                        'cost' => $cost,
                        'price' => $price,
                    ]);
            }
            DB::table('product_has_warehouses')
                ->where('product_id', $product['id'])
                ->where('warehouse_id', $warehouse_id)
                ->increment('stock', $product['quantity']);

        } else if ($metode_pengiriman == 'dikirim') {
            // if ($cogsMethod == 'FIFO') {
            DB::table('products')
                ->where('id_product', $product['id'])
                ->increment('on_order', $product['quantity']);
            //     DB::table('product_fifo')
            //         ->where('product_id', $product['id'])
            //         ->increment('on_order', $product['quantity']);
            // } else if ($cogsMethod == 'Average') {
            //     DB::table('products')
            //         ->where('id_product', $product['id'])
            //         ->increment('on_order', $product['quantity']);
            // }
        }
    }
}
