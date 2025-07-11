<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_configuration';
    public $timestamps = false;
    public function details()
    {
        return $this->hasMany(DetailConfiguration::class, 'configuration_id');
    }
}
