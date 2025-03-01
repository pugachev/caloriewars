<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'cateid',
        'catename',
    ];

    // public function calorie() {
    //     return $this->belongsTo('App\Models\Calorie');
    // }
}
