<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_employee';

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'address',
        'user_id',
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'employee_id', 'id_employee');
    }
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'employee_id', 'id_employee');
    }
}
