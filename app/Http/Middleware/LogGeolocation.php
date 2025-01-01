<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
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
            //dd($geoData);
            // Log the geolocation data in the IpInfo table asynchronously
            try{
                IpInfo::create([
                    'status' => 'success',
                    'country' => $geoData['country'] ?? null,
                    'countryCode' => $geoData['country_code'] ?? null,
                    'region' => $geoData['region'] ?? null,
                    'regionName' => $geoData['region'] ?? null,
                    'city' => $geoData['city'] ?? null,
                    'isp' => $geoData['isp'] ?? 'N/A',
                    'lat' => $geoData['latitude'] ?? "0",
                    'lon' => $geoData['longitude'] ?? "0",
                    'org' => $geoData['organization_name'] ?? 'N/A',
                    'query' => $ipAddress,
                    'timezone' => $geoData['timezone'] ?? 'N/A',
                    'zip' => $geoData['zip'] ?? 'N/A',
                ]);
            }catch(Exception $e){
                Log::error("Error while saving visitor data to database : $e");
            }
            
        } else {
            // Handle the failure (optional)
            Log::error("Failed to fetch geolocation data for IP: $ipAddress");
        }
    }
    
}


