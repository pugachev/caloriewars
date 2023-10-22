<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'tgtdate',
        'tgttimezone',
        'tgtcategory',
        'tgtitem',
        'tgtcalorie',
    ];

    // public function categories() {
    //     return $this->hasMany('App\Models\Categorie');
    // }
}
