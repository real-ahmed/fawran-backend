<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreDescription extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'store_id';

    public $incrementing = false;

    protected $fillable = [
        'store_id',
        'description',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
