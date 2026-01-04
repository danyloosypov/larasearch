<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Product extends Model
{
    use Searchable, SoftDeletes;

    // Table name (optional if it matches Laravel conventions)
    protected $table = 'products_test';

    public $timestamps = false;

    // Primary key is a string (hash)
    protected $primaryKey = 'uniq_id';
    public $incrementing = false; // because it's a hash, not auto-increment
    protected $keyType = 'string';

    // Fillable fields (optional, useful for mass assignment)
    protected $fillable = [
        'uniq_id',
        'crawl_timestamp',
        'product_url',
        'product_name',
        'product_category_tree',
        'pid',
        'retail_price',
        'discounted_price',
        'image',
        'is_FK_Advantage_product',
        'description',
        'product_rating',
        'overall_rating',
        'brand',
        'is_active',
        'product_specifications',
    ];

    protected $casts = [
        'retail_price' => 'float',
        'discounted_price' => 'float',
        'is_FK_Advantage_product' => 'boolean',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function getCrawlTimestampAttribute($value)
    {
        if (!$value) {
            return null;
        }

        try {
            // Fix "+0000" → "+00:00"
            $value = preg_replace('/([+-]\d{2})(\d{2})$/', '$1:$2', $value);

            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }


    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Transform the model's data for Typesense/Scout
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->uniq_id,
            'name' => $this->product_name ?? '',
            'brand' => optional($this->brand)->name ?? '',
            'category' => $this->product_category_tree ?? '',
            'price' => (float)($this->discounted_price ?? '0'),
            'description' => trim(
                preg_replace('/\s+/', ' ', strip_tags($this->description ?? ''))
            ),
            'rating' => $this->product_rating,
            'url' => $this->product_url ?? '',
            'image' => $this->image ?? '',
            'is_active' => (bool) $this->is_active,
            'is_fk_advantage' => (bool) $this->is_FK_Advantage_product,
        ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('brand');
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_active && is_null($this->deleted_at);
    }

}
