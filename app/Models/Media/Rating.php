<?php

namespace App\Models\Media;

use App\Models\Model;
use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'order_id',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function comment(): HasOne
    {
        return $this->hasOne(RatingComment::class);
    }
}
