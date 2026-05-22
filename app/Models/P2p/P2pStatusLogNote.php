<?php

namespace App\Models\P2p;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2pStatusLogNote extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'p2p_status_log_id';

    public $incrementing = false;

    protected $fillable = [
        'p2p_status_log_id',
        'note',
    ];

    public function statusLog(): BelongsTo
    {
        return $this->belongsTo(P2pStatusLog::class, 'p2p_status_log_id');
    }
}
