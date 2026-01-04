<?php

namespace App\Models\Raystop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;

    protected $table = 'types_pl';

    protected $fillable = [
        'title',
        'slug',
    ];
}
