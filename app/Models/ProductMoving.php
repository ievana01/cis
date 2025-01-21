<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMoving extends Model
{
    use HasFactory;
    protected $table = 'product_moving';
    protected $primaryKey = 'id_product_move';
    public $timestamps = false;
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }
    public function warehouseIn()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id_in', 'id_warehouse');
    }
    public function warehouseOut()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id_out', 'id_warehouse');
    }
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_id', 'id_sales');
    }
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_id', 'id_purchase');
    }
}
