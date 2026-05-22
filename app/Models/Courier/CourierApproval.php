<?php

namespace App\Models\Courier;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierApproval extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'courier_id';

    public $incrementing = false;

    protected $fillable = [
        'courier_id',
        'admin_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
