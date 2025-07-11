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

    public function refreshCostSales($product, $metode_pengiriman)
    {
        $cogsChoose = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 5)
            ->first();
        $cogsMethod = $cogsChoose->name;
        if ($metode_pengiriman == 'diambil') {
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

                        // Kurangi jumlah yang akan diproses
                        $quantity -= $toDecrement;
                    }
                }
            } else if ($cogsMethod == 'Average') {
                DB::table('products')
                    ->where('id_product', $product['id'])
                    ->decrement('total_stock', $product['quantity']);
            }
            // kurangi stok di gudang --- jika produk disimpan lebih dari 1 gudang maka gudang yg memiliki stok terkecil akan berkurang
            $quantity = $product['quantity'];
            $warehouses = DB::table('product_has_warehouses')
                ->where('product_id', $product['id'])
                ->where('stock', '>', 0)
                ->orderBy('stock', 'asc') // Urutkan berdasarkan stok terkecil dulu
                ->get();

            foreach ($warehouses as $warehouse) {
                if ($quantity <= 0) {
                    break;
                }

                $reduceStock = min($quantity, $warehouse->stock);

                DB::table('product_has_warehouses')
                    ->where('product_id', $product['id'])
                    ->where('warehouse_id', $warehouse->warehouse_id)
                    ->decrement('stock', $reduceStock);

                $quantity -= $reduceStock;
            }
        } else if ($metode_pengiriman == 'dikirim') {
            DB::table('products')
                ->where('id_product', $product['id'])
                ->increment('in_order', $product['quantity']);
        }

    }

}
