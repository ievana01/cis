<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailConfiguration extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_detail_configuration';
    public $timestamps = false;
    public function configuration()
    {
        return $this->belongsTo(Configuration::class, 'configuration_id', 'id_configuration');
    }
}
