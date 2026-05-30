<?php

namespace App\Services\Geo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    /**
     * Calculate the total driving distance (in kilometers) for a given route.
     *
     * @param  array  $origin  ['lat' => float, 'lng' => float]
     * @param  array  $destination  ['lat' => float, 'lng' => float]
     * @param  array  $waypoints  Array of ['lat' => float, 'lng' => float]
     * @return float Distance in KM
     */
    public function calculateRouteDistance(array $origin, array $destination, array $waypoints = []): float
    {
        $apiKey = config('services.google_maps.api_key');

        if (empty($apiKey)) {
            Log::warning('Google Maps API key is not configured.');

            return 0.0;
        }

        $originStr = "{$origin['lat']},{$origin['lng']}";
        $destinationStr = "{$destination['lat']},{$destination['lng']}";

        $waypointsParam = '';
        if (! empty($waypoints)) {
            $waypointStrs = array_map(fn ($wp) => "{$wp['lat']},{$wp['lng']}", $waypoints);
            // Prefix with optimize:true to let Google Maps find the most efficient path between vendors
            $waypointsParam = 'optimize:true|'.implode('|', $waypointStrs);
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', array_filter([
            'origin' => $originStr,
            'destination' => $destinationStr,
            'waypoints' => $waypointsParam ?: null,
            'key' => $apiKey,
            'mode' => 'driving',
        ]));

        if ($response->failed()) {
            Log::error('Google Maps Directions API request failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 0.0;
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'OK' || empty($data['routes'])) {
            Log::warning('Google Maps Directions API returned non-OK status or empty routes.', [
                'status' => $data['status'] ?? 'UNKNOWN',
            ]);

            return 0.0;
        }

        $totalDistanceMeters = 0;
        $legs = $data['routes'][0]['legs'] ?? [];

        foreach ($legs as $leg) {
            $totalDistanceMeters += ($leg['distance']['value'] ?? 0);
        }

        return round($totalDistanceMeters / 1000, 2); // Convert to KM
    }
}
