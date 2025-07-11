<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $primaryKey = 'id_customer';
    public $timestamps = false;
    protected $fillable = ['name', 'phone_number', 'email', 'address'];

    protected $attributes = [
        'active_status' => 1, // Default aktif
    ];
}
