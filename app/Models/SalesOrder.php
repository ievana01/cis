<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $primaryKey = 'id_sales';
    public $timestamps = false;
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id_customer');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id_employee');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(DetailConfiguration::class, 'payment_method', 'id_detail_configuration');
    }

    public function salesDetails()
    {
        return $this->hasMany(SalesDetail::class, 'sales_id');
    }

    public function productMoving()
    {
        return $this->hasMany(ProductMoving::class, 'sales_id');
    }

    public function productHasWarehouse()
    {
        return $this->hasMany(ProductHasWarehouse::class, 'product_id');
    }

    public function refreshCostSales($cogsMethod, $product, $warehouse, $sales)
    {
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
                    // DB::table('product_fifo')
                    //     ->where('product_id', $stock->product_id)
                    //     ->where('id_product_fifo', $stock->id_product_fifo) // Pastikan hanya mengupdate stok yang tepat
                    //     ->decrement('stock', $toDecrement);

                    DB::table('product_fifo')
                        ->where('id_product_fifo', $stock->id_product_fifo) // Pastikan hanya mengupdate stok yang sesuai
                        ->increment('sold', $toDecrement);
                        
                    DB::table('products')
                        ->where('id_product', $product['id'])
                        ->decrement('total_stock', $toDecrement);

                    // Simpan pergerakan stok di product_moving
                    DB::table('product_moving')->insert([
                        'product_id' => $product['id'],
                        'move_stock' => $toDecrement,
                        'date' => $sales->date,
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
                'date' => $sales->date,
                'warehouse_id_in' => $warehouse->warehouse_id,
                'sales_id' => $sales->id_sales,
            ]);

            DB::table('product_has_warehouses')
                ->where('product_id', $product['id'])
                ->decrement('stock', $product['quantity']);
        }
    }
}
