<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $fillable = [
        'type',
        'number',
        'size_1',
        'size_2',
        'size_3',
        'size_blank',
        'count_1',
        'count_blank',
        'size_paper',
        'paper_format',
        'package',
        'membrane',
    ];

    public function getGroupName()
    {
        return match ($this->type) {
            'A1' => 'A1',
            'A2', 'A3' => 'A3,A2',
            'A4', 'A5' => 'A4,A5',
            default => ''
        };
    }
}
