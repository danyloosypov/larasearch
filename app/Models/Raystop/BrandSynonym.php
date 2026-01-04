<?php

namespace App\Models\Raystop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandSynonym extends Model
{
    use HasFactory;

    protected $table = 'brand_synonyms';

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'id_brands');
    }
}
