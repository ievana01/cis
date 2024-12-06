<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductHasWarehouse extends Model
{
    use HasFactory;
    public $timestamps = false;
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id_warehouse');
    }
}
