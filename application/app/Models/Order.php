<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'orders';

    /**
     * @var list<string>
     */
    protected $fillable = [
        //это база
        'type_paper', //картон
        'count_colors',
        //опции
        //Тип печати (офсет)
        //Постпечатная обработка (теснение,… и тд)
        //Люверс

        //ламинация
        //Матовая, глянцевая, софтач, металлизированная, нет)


        'paper_pack_work', //пакетов в работу
        'paper_size', //размер
        'paper_circulation', //тираж
        'paper_sale_page',    //стоимость листа
        'paper_count_page', //колво изделий с лис
        'paper_result',////итог на бумагу
        'print_pages_circulation',//Листов на печать тиража
        'print_prebuild',//Листов на приладку
        'print_count_color',//Кол-во цветов печати
        'print_sale_plates',///Стоимость пластин
        'print_cutting_paper', //Резка бумаги
        'print_cutting_count', //количество
        'print_sale_cutting', //Сумма за резку
        'print_sale', ///Стоимость печати
        'panton_weight',//Пантонов кг
        'panton_sale_1_weight',//сумма за 1 кг пантона
        'panton_result',//Итого за пантоны
        'print_result',//Итого за печать

        //это опции
        'lamination_pages',//Листов на ламинацию
        'lamination_work',//Работа ламинация
        'lamination_result_work',///Итого работы
        'prebuild_count',//Количество приладок
        'prebuild_sale',//стоимость приладки
        'prebuild_result',///Итого за приладки
        'membrane_meter_page',//метров пленки на лист
        'membrane_meter_sale',//Стоимость за метр
        'membrane_count_meter_circulation',//кол-во метров на тираж
        'membrane_result',///сумма по пленке
        'lamination_result',///Итого за ламинацию

        'felling_count_kick',//Кол-во ударов на вырубке
        'felling_sale_kick',//Стоимость за удар
        'felling_result_kick',///Итого ударов на стоимость????
        'felling_prebuild_count',//Кол-во приладок
        'felling_sale_prebuild',//Стоимость приладки
        'felling_result_sale_prebuild',///Итого за приладдки
        'felling_count_meter_canal',//кол-во метров каналов
        'felling_sale_1_meter',//стоимость за метр
        'felling_canal',///итого каналов на
        'felling_result',///Итого за вырубку

        'assembly_count_pack_gluing',//Итого пакетов на скле
        'assembly_cutting_cord',//резка шнура руб
        'assembly_sleeve_1',//рукав 1
        'assembly_bottom',//дно
        'assembly_glace_tape',//вклейка ленты
        'assembly_zip_lock',//упаковка в зип лок
        'assembly_meter_1',//лента метров на 1шт
        'assembly_sale_meter',//стоимость за метр
        'assembly_result_glue',///Итого кна клей
        'assembly_pack_glue',//клей на пакет
        'assembly_result_work',///Итого За работу
        'assembly_result_circulation',///Итго за изготовление тиража
        'assembly_zip_lock_pack',//зип лок на пакет
        'assembly_count_zip_lock',///Итого зип локов
        'assembly_additionally_packaging',//Дополнительно с упаковуой

        'pack_count_box',//Кол-во пакетов в коробку
        'pack_box_circulation',//Коробок на тираж
        'pack_sale_box',//стоимость коробки

        'result_circulation',///Итого за упаковку тиража
        'result_materials',///Итого на все материалы
        'result_works',///Итого на все работы
        'result_circulation',///Итого за тираж
    ];

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }
}
