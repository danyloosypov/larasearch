<?php

namespace App\Models\Raystop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Engine extends Model
{
    use HasFactory;

    protected $table = 'engines_pl';

    protected $fillable = [
        'title',
        'slug',
    ];
}
