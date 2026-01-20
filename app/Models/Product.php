<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'selling_price',
        'bulk_enabled',
        'bulk_type_id',
        'status',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }
}
