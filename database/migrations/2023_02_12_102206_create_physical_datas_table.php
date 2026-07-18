<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 運動量・体重の実績データ。
     * SQLダンプ復元後に migrate しても壊れないよう hasTable でガードする。
     */
    public function up(): void
    {
        if (Schema::hasTable('physical_datas')) {
            return;
        }

        Schema::create('physical_datas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tgt_physical_date');
            $table->integer('tgt_physical_category')->comment('カテゴリ');
            $table->text('tgt_physical_item');
            $table->float('tgt_physical_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_datas');
    }
};
