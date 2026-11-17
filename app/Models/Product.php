<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Product extends Model
{
    public const SALES_UNITS = ['ks', 'm', 'm²', 'kg', 'l', 'bal.'];

    /** @use HasFactory<ProductFactory> */
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'minimum_order_quantity',
        'sales_unit',
        'order_increment',
        'category',
        'category_label',
        'price',
        'currency',
        'availability',
        'short_description',
        'description',
        'image_path',
        'is_featured',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'minimum_order_quantity' => 'integer',
            'order_increment' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get the data that should be indexed for catalog search.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return $this->only([
            'name',
            'slug',
            'category',
            'category_label',
            'short_description',
            'description',
            'availability',
            'price',
            'is_featured',
        ]);
    }

    /**
     * Use slug route binding for product detail URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function parameterValues(): HasMany
    {
        return $this->hasMany(ProductParameterValue::class);
    }
}
