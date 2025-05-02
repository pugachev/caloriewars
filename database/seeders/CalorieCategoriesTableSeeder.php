<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CalorieCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => 100,
                'name' => '朝食・パン類',
                'description' => '朝食やパン類の摂取カロリー',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 101,
                'name' => 'お菓子類',
                'description' => 'スナック、お菓子類の摂取カロリー',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 102,
                'name' => '主菜',
                'description' => 'かつ、チキン等のメイン料理',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 103,
                'name' => '主食',
                'description' => 'ご飯、麦ごはん等の主食',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 104,
                'name' => 'アルコール',
                'description' => 'アルコール類の摂取カロリー',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 105,
                'name' => 'スープ類',
                'description' => 'スープ、汁物類',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 106,
                'name' => '運動',
                'description' => '運動による消費カロリー',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 107,
                'name' => 'その他食品',
                'description' => 'その他の食品カロリー',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 108,
                'name' => 'お好み焼き',
                'description' => 'お好み焼き類',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 109,
                'name' => '卵料理',
                'description' => '卵を使用した料理',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 110,
                'name' => '麺類',
                'description' => 'ラーメン、うどん等の麺類',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 111,
                'name' => 'おかず',
                'description' => '副菜、おかず類',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 112,
                'name' => 'おつまみ',
                'description' => 'アルコールのおつまみ類',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('calorie_categories')->insert($categories);
    }
} 