<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * 摂取熱量カテゴリのマスタデータ(本番と同一)。
     * 何度実行しても壊れないよう cateid をキーに upsert する。
     */
    public function run(): void
    {
        $categories = [
            ['cateid' => 100, 'catename' => 'パン'],
            ['cateid' => 101, 'catename' => 'お菓子'],
            ['cateid' => 102, 'catename' => 'フライ'],
            ['cateid' => 103, 'catename' => 'お米'],
            ['cateid' => 104, 'catename' => 'アルコール'],
            ['cateid' => 105, 'catename' => 'スープ'],
            ['cateid' => 106, 'catename' => '運動'],
            ['cateid' => 107, 'catename' => 'お肉'],
            ['cateid' => 108, 'catename' => '粉もの'],
            ['cateid' => 109, 'catename' => '卵系'],
            ['cateid' => 110, 'catename' => '麺類'],
            ['cateid' => 111, 'catename' => '魚介類'],
            ['cateid' => 112, 'catename' => '乳製品'],
            ['cateid' => 113, 'catename' => '大豆'],
            ['cateid' => 114, 'catename' => 'サラダ'],
            ['cateid' => 115, 'catename' => '果物'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['cateid' => $category['cateid']],
                ['catename' => $category['catename']]
            );
        }
    }
}
