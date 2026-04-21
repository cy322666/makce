<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LacPrice extends Model
{
    protected $table = 'price_lacs';

    protected $fillable = [
        'process_type',
        'lacquer_type',
        'format',
        'min_run',
        'max_run',
        'price',
    ];
}
