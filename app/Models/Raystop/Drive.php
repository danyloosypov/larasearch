<?php

namespace App\Models\Raystop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drive extends Model
{
    use HasFactory;

    protected $table = 'drives_pl';

    protected $fillable = [
        'title',
        'slug',
    ];
}
