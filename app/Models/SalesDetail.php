<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDetail extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'sales_id',
        'product_id',
        'quantity',
        'price',
        'amount',
    ];
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_id', 'id_sales');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }
}
