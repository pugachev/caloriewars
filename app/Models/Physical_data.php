<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Physical_data extends Model
{
    use HasFactory;

    protected $fillable = [
        'tgt_physical_date',
        'tgt_physical_category',
        'tgt_physical_item',
        'tgt_physical_data',
    ];

    // public function categories() {
    //     return $this->hasMany('App\Models\Categorie');
    // }
}
