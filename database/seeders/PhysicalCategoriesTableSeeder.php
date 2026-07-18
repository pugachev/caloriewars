<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhysicalCategoriesTableSeeder extends Seeder
{
    /**
     * 運動量・体重カテゴリのマスタデータ(本番と同一)。
     * 何度実行しても壊れないよう physical_cateid をキーに upsert する。
     *
     * ※205(ステッパー)を入力すると 200/201/202/204 が自動計算される
     *   (CalorieController::store_physical_info 参照)
     */
    public function run(): void
    {
        $categories = [
            ['physical_cateid' => 200, 'physical_catename' => '歩行時間'],
            ['physical_cateid' => 201, 'physical_catename' => '歩数'],
            ['physical_cateid' => 202, 'physical_catename' => '歩行距離'],
            ['physical_cateid' => 203, 'physical_catename' => '確定体重'],
            ['physical_cateid' => 204, 'physical_catename' => '確定熱量'],
            ['physical_cateid' => 205, 'physical_catename' => 'ステッパー'],
        ];

        foreach ($categories as $category) {
            DB::table('physical_categories')->updateOrInsert(
                ['physical_cateid' => $category['physical_cateid']],
                ['physical_catename' => $category['physical_catename']]
            );
        }
    }
}
