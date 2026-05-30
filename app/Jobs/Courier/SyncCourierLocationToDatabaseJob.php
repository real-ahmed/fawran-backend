<?php

namespace App\Jobs\Courier;

use App\Models\Courier\CourierLocation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCourierLocationToDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $courierId,
        public float $latitude,
        public float $longitude
    ) {}

    public function handle(): void
    {
        CourierLocation::updateOrCreate(
            ['courier_id' => $this->courierId],
            [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'located_at' => now(),
            ]
        );
    }
}
