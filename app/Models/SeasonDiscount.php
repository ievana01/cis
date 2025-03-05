<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeasonDiscount extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'season_discount';
    
}
