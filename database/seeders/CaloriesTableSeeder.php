<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CaloriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $calories = [
            [
                'id' => 230,
                'tgtdate' => '2023-02-19 00:00:00',
                'tgttimezone' => 0,
                'tgtcategory' => 100,
                'tgtitem' => 'ハムチーズ',
                'tgtcalorie' => 290,
                'created_at' => '2023-02-19 19:59:42',
                'updated_at' => '2023-02-19 19:59:42'
            ],
            [
                'id' => 231,
                'tgtdate' => '2023-02-19 00:00:00',
                'tgttimezone' => 0,
                'tgtcategory' => 101,
                'tgtitem' => 'カレー煎餅',
                'tgtcalorie' => 497,
                'created_at' => '2023-02-19 20:00:04',
                'updated_at' => '2023-02-19 20:00:04'
            ],
            [
                'id' => 232,
                'tgtdate' => '2023-02-19 00:00:00',
                'tgttimezone' => 1,
                'tgtcategory' => 102,
                'tgtitem' => 'チキンかつ',
                'tgtcalorie' => 300,
                'created_at' => '2023-02-19 20:00:34',
                'updated_at' => '2023-02-19 20:00:34'
            ],
            // ... 以下同様のデータが続きます
        ];

        // チャンクに分けてデータを挿入
        foreach (array_chunk($calories, 100) as $chunk) {
            DB::table('calories')->insert($chunk);
        }
    }
} 