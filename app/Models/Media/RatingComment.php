<?php

namespace App\Models\Media;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingComment extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'rating_id';

    public $incrementing = false;

    protected $fillable = [
        'rating_id',
        'comment',
    ];

    public function rating(): BelongsTo
    {
        return $this->belongsTo(Rating::class);
    }
}
