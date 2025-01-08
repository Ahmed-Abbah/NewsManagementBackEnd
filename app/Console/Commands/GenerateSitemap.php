<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Post;
class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    
     protected $signature = 'sitemap:generate';
     protected $description = 'Generate the sitemap for the website';
 
     public function handle()
     {
         // Create a new sitemap
         $sitemap = Sitemap::create();
 
         // Fetch all posts and add them to the sitemap
         $posts = Post::where('post_status', 'publish')
         ->where('post_type', 'post')->
         get();  
         // Adjust based on your post model and logic
         foreach ($posts as $post) {
             $sitemap->add(Url::create("/post/{$post->post_name}")
                 ->setLastModificationDate(Carbon::parse($post->post_date)));
         }
 
         // Save the sitemap in the public/assets folder (or wherever your assets are stored)
         $sitemap->writeToFile(public_path('../public_html/assets/sitemap.xml'));
 
         $this->info('Sitemap generated successfully!');
     }
}
