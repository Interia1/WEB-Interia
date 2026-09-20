<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogParameter extends Model
{
    protected $fillable = ['name', 'type', 'unit', 'options', 'required', 'filterable', 'position'];

    protected function casts(): array
    {
        return ['options' => 'array', 'required' => 'boolean', 'filterable' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CatalogCategory::class, 'catalog_category_id');
    }
}
