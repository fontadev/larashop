<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_value',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isValid()
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function calculateDiscount($subtotal)
    {
        if ($subtotal < $this->min_value) {
            return 0;
        }

        if ($this->type === 'percentage') {
            return $subtotal * ($this->value / 100);
        }

        return min($this->value, $subtotal);
    }
}
