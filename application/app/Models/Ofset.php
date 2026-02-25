<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ofset extends Model
{
    protected $fillable = [
        'colors',
        'sale_preparation',
        'sale_print',
        'sale_print_mel_paper',
        'circulation_100',
        'circulation_300',
        'circulation_500',
        'circulation_1000',
        'circulation_2000',
        'circulation_3000',
        'circulation_5000',
        'circulation_10000',
        'circulation_15000',
        'circulation_20000',
        'circulation_50000',
        'circulation_100000',
        'circulation_500000',
        'circulation_1000000',
    ];
}
