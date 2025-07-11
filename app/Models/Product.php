<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $primaryKey = 'id_product';
    public $timestamps = false;
    protected $fillable = ['name', 'description', 'total_stock', 'cost', 'profit', 'unit', 'min_total_stock', 'category_id', 'warehouse_id', 'file_images'];

    protected $attributes = [
        'status_active' => 1, // Default aktif saat produk baru dibuat
    ];
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id_category');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_categories_id', 'id_sub_category');
    }
    // public function supplier()
    // {
    //     return $this->belongsTo(Supplier::class, 'supplier_id', 'id_supplier');
    // }
    public function images()
    {
        return $this->hasMany(ImageProduct::class, 'product_id'); // 'product_id' adalah kolom yang menghubungkan dengan produk
    }
    public function productMoving()
    {
        return $this->hasMany(ProductMoving::class, 'product_id');
    }
}
