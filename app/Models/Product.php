<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description'
    ];

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class, 'product_id', 'id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'product_id', 'id');
    }

    public function mainStock(): HasOne
    {
        return $this->hasOne(Stock::class)->whereNull('product_variation_id');
    }
}
