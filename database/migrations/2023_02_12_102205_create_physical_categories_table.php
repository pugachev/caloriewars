<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 運動量・体重のカテゴリマスタ(歩行時間・歩数・確定体重・ステッパーなど)。
     * SQLダンプ復元後に migrate しても壊れないよう hasTable でガードする。
     */
    public function up(): void
    {
        if (Schema::hasTable('physical_categories')) {
            return;
        }

        Schema::create('physical_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('physical_cateid');
            $table->text('physical_catename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_categories');
    }
};
