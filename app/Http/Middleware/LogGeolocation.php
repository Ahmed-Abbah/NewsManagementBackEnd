<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\IpInfo;
use Illuminate\Support\Facades\Queue;

class LogGeolocation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the real client IP address
        $ipAddress = $request->ip();
        
        // Dispatch an asynchronous job to handle the geolocation logging

            $this->logGeolocationData($ipAddress);


        return $next($request);  // Proceed with the normal request cycle
    }

    /**
     * Function to log the geolocation data.
     *
     * @param string $ipAddress
     */
    private function logGeolocationData(string $ipAddress)
    {
        // Call a geolocation API with the client's IP address
        $response = Http::get("https://get.geojs.io/v1/ip/geo/{$ipAddress}.json");
    
        if ($response->successful()) {
            $geoData = $response->json();
    
            // Log the geolocation data in the IpInfo table asynchronously
            IpInfo::create([
                    'status' => 'success',
                    'country' => $geoData['country'],
                    'countryCode' => $geoData['country_code'],
                    'region' => $geoData['region'],
                    'regionName' => $geoData['region'],
                    'city' => $geoData['city'],
                    'isp' => $geoData['isp'] ?? 'N/A',
                    'lat' => $geoData['latitude'] ?? null,
                    'lon' => $geoData['longitude'] ?? null,
                    'org' => $geoData['organization_name'] ?? 'N/A',
                    'query' => $ipAddress,
                    'timezone' => $geoData['timezone'] ?? 'N/A',
                    'zip' => $geoData['zip'] ?? 'N/A',
                ]);
            
        } else {
            // Handle the failure (optional)
            Log::error("Failed to fetch geolocation data for IP: $ipAddress");
        }
    }
    
}


