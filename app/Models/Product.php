<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
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
}
