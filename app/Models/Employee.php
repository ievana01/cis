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
}
