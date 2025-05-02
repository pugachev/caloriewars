<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calorie extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tgtdate',
        'tgttimezone',
        'tgtcategory',
        'tgtitem',
        'tgtcalorie',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tgtdate' => 'datetime',
        'tgttimezone' => 'integer',
        'tgtcategory' => 'integer',
        'tgtcalorie' => 'integer',
    ];

    // public function categories() {
    //     return $this->hasMany('App\Models\Categorie');
    // }

    /**
     * カロリーに関連するカテゴリを取得
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CalorieCategory::class, 'tgtcategory', 'id');
    }

    /**
     * 運動カロリーかどうかを判定
     */
    public function isExercise(): bool
    {
        return $this->tgtcategory === 106; // 運動カテゴリID
    }

    /**
     * 正味のカロリー値を取得（運動の場合は負の値）
     */
    public function getNetCalorieAttribute(): int
    {
        return $this->isExercise() ? -$this->tgtcalorie : $this->tgtcalorie;
    }
}
