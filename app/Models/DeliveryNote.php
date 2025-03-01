<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    use HasFactory;
    protected $table = 'delivery_note';
    public $timestamps = false;
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
