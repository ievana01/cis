<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'price',
        'amount',
    ];
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_id', 'id_purchase');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }
}
