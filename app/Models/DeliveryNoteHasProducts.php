<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteHasProducts extends Model
{
    use HasFactory;
    public $timestamps = false;
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class, 'delivery_note_id', 'id');
    }
}
