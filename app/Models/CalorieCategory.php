<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalorieCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * カテゴリに関連するカロリーレコードを取得
     */
    public function calories(): HasMany
    {
        return $this->hasMany(Calorie::class, 'tgtcategory', 'id');
    }

    /**
     * カテゴリの定数定義
     */
    public const BREAKFAST = 100;
    public const SNACKS = 101;
    public const MAIN_DISH = 102;
    public const STAPLE_FOOD = 103;
    public const ALCOHOL = 104;
    public const SOUP = 105;
    public const EXERCISE = 106;
    public const OTHER_FOOD = 107;
    public const OKONOMIYAKI = 108;
    public const EGG_DISH = 109;
    public const NOODLES = 110;
    public const SIDE_DISH = 111;
    public const APPETIZER = 112;

    /**
     * カテゴリ名の配列を取得
     */
    public static function getCategoryNames(): array
    {
        return [
            self::BREAKFAST => '朝食・パン類',
            self::SNACKS => 'お菓子類',
            self::MAIN_DISH => '主菜',
            self::STAPLE_FOOD => '主食',
            self::ALCOHOL => 'アルコール',
            self::SOUP => 'スープ類',
            self::EXERCISE => '運動',
            self::OTHER_FOOD => 'その他食品',
            self::OKONOMIYAKI => 'お好み焼き',
            self::EGG_DISH => '卵料理',
            self::NOODLES => '麺類',
            self::SIDE_DISH => 'おかず',
            self::APPETIZER => 'おつまみ',
        ];
    }

    /**
     * カテゴリの説明の配列を取得
     */
    public static function getCategoryDescriptions(): array
    {
        return [
            self::BREAKFAST => '朝食やパン類の摂取カロリー',
            self::SNACKS => 'スナック、お菓子類の摂取カロリー',
            self::MAIN_DISH => 'かつ、チキン等のメイン料理',
            self::STAPLE_FOOD => 'ご飯、麦ごはん等の主食',
            self::ALCOHOL => 'アルコール類の摂取カロリー',
            self::SOUP => 'スープ、汁物類',
            self::EXERCISE => '運動による消費カロリー',
            self::OTHER_FOOD => 'その他の食品カロリー',
            self::OKONOMIYAKI => 'お好み焼き類',
            self::EGG_DISH => '卵を使用した料理',
            self::NOODLES => 'ラーメン、うどん等の麺類',
            self::SIDE_DISH => '副菜、おかず類',
            self::APPETIZER => 'アルコールのおつまみ類',
        ];
    }
} 