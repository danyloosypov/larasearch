<?php

namespace App\Models\Parfumer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasFactory;

    protected $table = 'attributes_uk';

    public function attributeValues() {
        return $this->hasMany(AttributeValue::class, 'id_attributes');
    }
}
