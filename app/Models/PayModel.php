<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayModel extends Model
{
    use HasFactory;

    public function cekPay($paymentMethod)
    {
        return DetailConfiguration::where('configuration_id', 1)
            ->where('status_active', 1)
            ->where('id_detail_configuration', $paymentMethod)
            ->exists();
    }
}
