<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';

    protected $fillable = [
        'number_id',
        'group_name',//название группы
        'bottom',//дно
        'handle_1',//рукав1
        'handle_2',//рукав2
        'luvers',//люверс
        'cutting_cord_2',//резка шнура 2шт
        'sidewall',//вставка боковины
        'boking_gluing',//склейка боковины
        'hole',//дырка
//        '',
//        '',
//        '',
//        '',
    ];

    public static $fields = [
        'number_id' => '№ Формы',
        'group_name' => 'Размер',
        'bottom' => 'Дно',
        'handle_1' => 'Рукав 1',//рукав1
        'handle_2' => 'Рукав 2',//рукав2
        'luvers' => 'Люверс',//люверс
        'cutting_cord_2' => 'Резка шнура 2шт',//резка шнура 2шт
        'sidewall' => 'Вставка боковины',//вставка боковины
        'boking_gluing' => 'Склейка боковины',//склейка боковины
        'hole' => 'Дырка',//дырка
    ];
}
