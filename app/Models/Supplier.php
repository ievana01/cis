<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $primaryKey = 'id_supplier';
    public $timestamps = false;
    protected $attributes = [
        'active_status' => 1, // Default aktif
    ];
}
