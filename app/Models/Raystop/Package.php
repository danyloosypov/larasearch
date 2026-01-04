<?php

namespace App\Models\Raystop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $table = 'packages';

    protected $fillable = [
        'title',
        'id_categories',
        'price',
        'short_desc',
        'scheme',
        'image',
        'scheme_configurator',
        'gallery',
        'view_360',
    ];

    protected $attributes = [
        'price'					=> 0,
        'short_desc'			=> '',
        'scheme'				=> '',
        'image'					=> '',
        'scheme_configurator'	=> '',
        'gallery'               =>  '[]',
        'view_360'              => '',
    ];
}
