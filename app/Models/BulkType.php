<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulkType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'bulk_unit_id',
        'units_per_bulk',
        'description',
    ];

    public function bulkUnit(): BelongsTo
    {
        return $this->belongsTo(BulkUnit::class);
    }
}
