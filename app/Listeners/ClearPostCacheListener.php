<?php

namespace App\Listeners;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
class ClearPostCacheListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        //// Log the cache clearing to verify it's triggered
        Log::info('PostCreated event triggered. Clearing cache for latest posts.'); 
        // Clear the cached latest posts whenever a post is created
        Cache::forget('latest_posts');
    }
}
