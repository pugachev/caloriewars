<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaloriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * 本番スキーマ(ikefuku40_caloriewars)に合わせた定義。
     * SQLダンプ復元後に migrate しても壊れないよう hasTable でガードする。
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('calories')) {
            return;
        }

        Schema::create('calories', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tgtdate');
            $table->integer('tgttimezone');
            $table->integer('tgtcategory')->comment('カテゴリ');
            $table->string('tgtitem')->nullable();
            $table->integer('tgtcalorie');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calories');
    }
}
