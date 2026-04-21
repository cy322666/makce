<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaperPrice extends Model
{
    protected $table = 'paper_prices';

    protected $fillable = [
        'match_key',
        'title',
        'group_name',
        'sheet_format',
        'base_price',
        'markup_percent',
        'sale_price',
        'note',
    ];
}
