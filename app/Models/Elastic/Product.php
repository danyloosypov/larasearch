<?php

namespace App\Models\Elastic;

use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JeroenG\Explorer\Application\Explored;
use JeroenG\Explorer\Application\BePrepared;
use Laravel\Scout\Searchable;

class Product extends Model implements Explored, BePrepared
{
    use Searchable, SoftDeletes;

    /* -----------------------------------------------------------------
     | Database
     |-----------------------------------------------------------------*/
    protected $table = 'products_test';

    public $timestamps = false;

    protected $primaryKey = 'uniq_id';
    public $incrementing = false;
    protected $keyType = 'string';

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
        'retail_price'              => 'float',
        'discounted_price'          => 'float',
        'is_FK_Advantage_product'   => 'boolean',
        'is_active'                 => 'boolean',
        'deleted_at'                => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    /* -----------------------------------------------------------------
     | Relationships
     |-----------------------------------------------------------------*/
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /* -----------------------------------------------------------------
     | Accessors
     |-----------------------------------------------------------------*/
    public function getCrawlTimestampAttribute($value)
    {
        if (!$value) {
            return null;
        }

        try {
            $value = preg_replace('/([+-]\d{2})(\d{2})$/', '$1:$2', $value);
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /* -----------------------------------------------------------------
     | Explorer Mapping
     |-----------------------------------------------------------------*/
    public function mappableAs(): array
    {
        return [
            'id'          => 'keyword',
            'name'        => 'text',
            'brand'       => 'text',
            'category'    => 'text',
            'description' => 'text',
            'specs'       => 'text',
            'price'       => 'double',
            'rating'      => 'text',
            'is_active'   => 'boolean',
        ];
    }

    /* -----------------------------------------------------------------
     | Scout Indexing
     |-----------------------------------------------------------------*/
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->uniq_id,

            'name' => $this->product_name ?? '',
            'test_name' => $this->product_name . ' 228' ?? '',
            'brand' => optional($this->brand)->name ?? '',

            'category' => $this->product_category_tree ?? '',

            'description' => trim(
                preg_replace('/\s+/', ' ', strip_tags($this->description ?? ''))
            ),

            'specs' => trim(
                preg_replace('/\s+/', ' ', strip_tags($this->product_specifications ?? ''))
            ),

            'price' => (float) ($this->discounted_price ?? 0),

            'rating' => (string) $this->product_rating,

            'is_active' => (bool) $this->is_active,
        ];
    }

    public function prepare(array $searchable): array
    {
        $searchable['name'] = $searchable['name'] . ' 111';

        return $searchable;
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('brand');
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_active && $this->deleted_at === null;
    }

    public static function usesSoftDelete(): bool
    {
        return false;
    }

    public function searchableAs(): string
    {
        return 'products_test';
    }
}
