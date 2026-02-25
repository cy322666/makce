<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
//            $table->string('number', 32)->unique();
//            $table->decimal('total_price', 12, 2)->nullable();
//            $table->enum('status', ['new', 'processing', 'shipped', 'delivered', 'cancelled'])->default('new');
//            $table->string('currency');
//            $table->decimal('shipping_price')->nullable();
//            $table->string('shipping_method')->nullable();
//            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            //бумага
            $table->string('paper_circulation')->nullable(); //тираж
            $table->integer('paper_size')->nullable();     //Размер
            $table->integer('paper_pack_work')->nullable();     //Пакетов в работу
            $table->decimal('paper_sale_page')->nullable();     //стоимость листа
            $table->integer('paper_count_page')->nullable(); //колво изделий с лис
            $table->decimal('paper_result')->nullable();    ////итог на бумагу
            //колво а2

            // - печать
            $table->integer('print_pages_circulation')->nullable(); //Листов на печать тиража
            $table->integer('print_prebuild')->nullable();          //Листов на приладку
            $table->integer('print_count_color')->nullable();       //Кол-во цветов печати
            $table->decimal('print_sale_plates')->nullable();       ///Стоимость пластин


            $table->integer('print_cutting_paper')->nullable(); //Резка бумаги
            $table->integer('print_cutting_count')->nullable(); //количество
            $table->integer('print_sale_cutting')->nullable(); //Сумма за резку
            $table->integer('print_result')->nullable(); //Сумма за резку
            $table->decimal('print_sale')->nullable();//С/Итого за печать

            $table->integer('panton_weight')->nullable();       //Пантонов кг
            $table->decimal('panton_sale_1_weight')->nullable();//сумма за 1 кг пантона
            $table->decimal('panton_result')->nullable();       //Итого за пантоны

            // - Ламинация
            $table->integer('lamination_pages')->nullable();//Листов на ламинацию
            $table->integer('lamination_work')->nullable(); //Работа ламинация
            $table->decimal('lamination_result_work')->nullable();///Итого работы

            $table->integer('prebuild_count')->nullable();  //Количество приладок
            $table->decimal('prebuild_sale')->nullable();   //стоимость приладки
            $table->decimal('prebuild_result')->nullable(); ///Итого за приладки

            // - пленка
            $table->integer('membrane_meter_page')->nullable();//метров пленки на лист
            $table->decimal('membrane_meter_sale')->nullable();//Стоимость за метр
            $table->decimal('membrane_count_meter_circulation')->nullable();//кол-во метров на тираж
            $table->decimal('membrane_result')->nullable();     ///сумма по пленке
            $table->decimal('lamination_result')->nullable();   ///Итого за ламинацию

            // - Вырубка
            $table->integer('felling_count_kick')->nullable();  //Кол-во ударов на вырубке
            $table->decimal('felling_sale_kick')->nullable();   //Стоимость за удар
            $table->decimal('felling_result_kick')->nullable(); ///Итого ударов на стоимость????

            $table->integer('felling_prebuild_count')->nullable();      //Кол-во приладок
            $table->integer('felling_sale_prebuild')->nullable();       //Стоимость приладки
            $table->integer('felling_result_sale_prebuild')->nullable();///Итого за приладдки

            $table->integer('felling_count_meter_canal')->nullable();//кол-во метров каналов
            $table->decimal('felling_sale_1_meter')->nullable();     //стоимость за метр
            $table->integer('felling_canal')->nullable();            ///итого каналов на
            $table->decimal('felling_result')->nullable();           ///Итого за вырубку

            //-Сборка
            $table->decimal('assembly_count_pack_gluing')->nullable();  //Итого пакетов на склейку
            $table->decimal('assembly_cutting_cord')->nullable();       //резка шнура руб
            $table->decimal('assembly_sleeve_1')->nullable();           //рукав 1
            $table->decimal('assembly_bottom')->nullable();             //дно
            $table->decimal('assembly_glace_tape')->nullable();         //вклейка ленты
            $table->decimal('assembly_zip_lock')->nullable();           //упаковка в зип лок
            $table->decimal('assembly_meter_1')->nullable();            //лента метров на 1шт
            $table->decimal('assembly_sale_meter')->nullable();         //стоимость за метр
            $table->decimal('assembly_result_glue')->nullable();        ///Итого кна клей
            $table->decimal('assembly_pack_glue')->nullable();          //клей на пакет
            $table->decimal('assembly_result_work')->nullable();        ///Итого За работу
            $table->decimal('assembly_result_circulation')->nullable(); ///Итго за изготовление тиража
            $table->decimal('assembly_zip_lock_pack')->nullable();      //зип лок на пакет
            $table->integer('assembly_count_zip_lock')->nullable();     ///Итого зип локов
            $table->decimal('assembly_additionally_packaging')->nullable();//Дополнительно с упаковуой

            // - Упаковка финал
            $table->integer('pack_count_box')->nullable();      //Кол-во пакетов в коробку
            $table->integer('pack_box_circulation')->nullable();//Коробок на тираж
            $table->decimal('pack_sale_box')->nullable();       //стоимость коробки

            $table->decimal('result_package_circulation')->nullable();  ///Итого за упаковку тиража
            $table->decimal('result_materials')->nullable();    ///Итого на все материалы
            $table->decimal('result_works')->nullable();        ///Итого на все работы
            $table->decimal('result_circulation')->nullable();  ///Итого за тираж
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
