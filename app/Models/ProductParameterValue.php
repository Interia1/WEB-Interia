<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductParameterValue extends Model
{
    public $timestamps = false;

    protected $fillable = ['catalog_parameter_id', 'value', 'numeric_value'];

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(CatalogParameter::class, 'catalog_parameter_id');
    }
}
