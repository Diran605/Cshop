<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'category_id',
        'cost_price',
        'min_selling_price',
        'selling_price',
        'bulk_enabled',
        'bulk_type_id',
        'status',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bulkType(): BelongsTo
    {
        return $this->belongsTo(BulkType::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }
}
