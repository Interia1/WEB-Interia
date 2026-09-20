<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogCategory extends Model
{
    protected $fillable = ['slug', 'name'];

    public function parameters(): HasMany
    {
        return $this->hasMany(CatalogParameter::class)->orderBy('position')->orderBy('id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category', 'slug');
    }
}
