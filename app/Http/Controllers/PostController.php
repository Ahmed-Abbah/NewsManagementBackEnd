<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategoryMapping;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Cache;
use App\Models\IpInfo;
use DB;
use Illuminate\Support\Facades\Http;
class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $posts = Post::paginate(10); 
    //     return response()->json($posts);


    
    // }
    public function index()
    {    
        // If no cache, fetch posts from database and cache the result
        $posts = Post::where("post_type", "post")
            ->orderBy("post_date", "desc")
            ->whereNotNull('post_title') // Ensure post_title is not null
            ->where('post_title', '!=', '') // Ensure post_title is not empty
            ->where('post_type', 'post')
            ->where('post_status', 'publish') // Filter for post_type = 'post'
            ->get();
        return response()->json($posts);
    }



    public function modifyPost(Request $request)
    {
        try{
            
        // Validate the incoming data
        $validatedData = $request->validate([
            'ID' => 'required|integer',   // Ensure it's an integer
            'title' => 'required|string',
            'slug' => 'required|string',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
        ]);
    
        // Prepare the SQL query to update the post
        $updateQuery = "
            UPDATE www_posts
            SET post_title = ?, post_name = ?, post_content = ?, post_excerpt = ?
            WHERE id = ?
        ";
    
        // Execute the raw SQL query using the DB facade
        $updated = DB::update($updateQuery, [
            $validatedData['title'],
            $validatedData['slug'],
            $validatedData['content'],
            $validatedData['excerpt'] ?? null, // If 'excerpt' is null, we pass null
            $validatedData['ID'],
        ]);
    
        // Check if any rows were updated
        if ($updated) {
            // Return success message
            return response()->json(['message' => 'Post updated successfully'], 200);
        } else {
            // Return error if no rows were updated (post not found or no changes)
            return response()->json(['error' => 'Post not found or no changes made'], 404);
        }
    }
        catch(Exception $e){
            Log::error("Error while saving post Modification : $e");
        }
    }

    public function deletePost(Request $request)
{
    try {
        // Validate the incoming data
        $validatedData = $request->validate([
            'ID' => 'required|integer', // Ensure 'id' is required and an integer
        ]);

        // Prepare the SQL query to delete the post
        $deleteQuery = "
            DELETE FROM www_posts
            WHERE id = ?
        ";

        // Execute the raw SQL query using the DB facade
        $deleted = DB::delete($deleteQuery, [
            $validatedData['ID'],
        ]);

        // Check if any rows were deleted
        if ($deleted) {
            // Return success message
            return response()->json(['message' => 'Post deleted successfully'], 200);
        } else {
            // Return error if no rows were deleted (post not found)
            return response()->json(['error' => 'Post not found'], 404);
        }
    } catch (Exception $e) {
        // Log the error with detailed context
        Log::error("Error while deleting post: {$e->getMessage()}");

        // Return a generic error response
        return response()->json(['error' => 'An error occurred while deleting the post'], 500);
    }
}


    




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */


     public function store(Request $request)
     {
         // Log the received data
         Log::info('Received data:', $request->all());
     
         // Validate the incoming request
         $validated = $request->validate([
             'post_title' => 'required|string|max:255',
             'post_content' => 'required|string',
             'post_status' => 'required|string|max:20',
             'post_type' => 'required|string|max:20',
             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation with 'image' field
         ]);


     
         // Extract email from JWT (if the user is authenticated)
         try {
             $user = auth('sanctum')->user()->email;
             $post_author = $user; // Extract the email from the authenticated user
         } catch (Exception $e) {
             // Handle error (if user is not authenticated or token is invalid)
             return response()->json(['error' => 'Unauthorized'], 401);
         }
     
         $imagePath = null;
     
         // Handle image upload (if there's an image)
         if ($request->hasFile('guid')) {
             $file = $request->file('guid');
             $filePath = $file->store('public/uploads');  // Store file in the 'public/uploads' directory
             $fileUrl = Storage::url($filePath);
             // Get the current date and time for the path
             $currentDate = Carbon::now();
             $year = $currentDate->year;
             $month = $currentDate->month;
             $timestamp = $currentDate->format('YmdHis'); // Current date and time with seconds (e.g. 20231121123015)
     
             // Create a custom path for storing the image
             $imagePath = "uploads/{$year}/{$month}/{$timestamp}_{$request->file('image')->getClientOriginalName()}";
     
             // Store the image in the 'public' disk (ensure the public folder is linked)
             $path = $request->file('image')->storeAs('public/' . $imagePath);
         }
     
        //  Create the main post with the validated data (with or without the image)
         $post = Post::create([
             'post_author' => $post_author,  // Set post_author as the email of the authenticated user
             'post_title' => $validated['post_title'],
             'post_content' => $validated['post_content'],
             'post_status' => $validated['post_status'],
             'post_type' => $validated['post_type'],
             'guid' => $imagePath, // Store the image path here if it exists
         ]);
     
         // Return the created post as a response
         return response()->json($post, 201);
     }


     public function storeImage(Request $request)
{     
    try{
        //Log::info('Received Post data:', $request->all());

    // Define required fields with user-friendly names
    $rules = [
        'post_title' => 'Post Title',
        'post_content' => 'Post Content',
        'post_status' => 'Post Status',
        'post_type' => 'Post Type',
        'post_excerpt' => 'Post Excerpt',
        'post_name' => 'Post Slug',
        'guid' => 'Image',
        'categories' => 'Categories',
    ];

    // Collect user-friendly error messages for missing fields
    $missingMessages = [];
    foreach ($rules as $field => $friendlyName) {
        if (!$request->has($field) ) {
            $missingMessages[] = "The field '$friendlyName' is required.";
        }
    }

    // If any fields are missing, return an error response
    if (!empty($missingMessages)) {
        return response()->json([
            'error' => 'Validation failed',
            'messages' => $missingMessages,
        ], 422);
    }
 
    $imageUrl = null;
    
    
    // Handle the image upload if it exists
    if ($request->hasFile('guid')) {
        // Get the original file extension
        $extension = $request->file('guid')->getClientOriginalExtension();
        
        // Generate a custom name (for example, using timestamp and a unique identifier)
        $fileName = $request->input('post_title') . '.' . $extension;
        
        // Store the file in the 'uploads/yyyy/mm' folder with the custom name
        $path = $request->file('guid')->storeAs('uploads/' . date('Y/m'), $fileName, 'public');
        
        // Generate the full URL to the stored file
        $imageUrl = url('storage/' . $path);
    }else{
        return response()->json([
            'error' => 'Validation failed',
            'messages' => 'post should have an image',
        ], 422);
    }

    if($request->input('lng')){
        $lng = $request->input('lng');
    }else{
        Log::info('No Post Language detected');
    }


    $postExcerpt = $request->input('post_excerpt') ;
    $postSlug = $request->input('post_name') ;
    // Insert the post Image as post record where the post_type takes "attachmnt" data into the database
    DB::insert(
        "INSERT INTO www_posts (post_title, post_content, post_excerpt,post_name,post_status, post_type, to_ping, post_content_filtered, ping_status, pinged, guid, post_date, post_date_gmt) 
        VALUES (:post_title, :post_content, :post_excerpt,:post_name, :post_status, :post_type, :to_ping, :post_content_filtered, :ping_status, :pinged, :guid, NOW(), NOW())",
        [
            'post_title' => $request->input('post_title'),
            'post_content' => $request->input('post_content'),
            'post_excerpt' => $postExcerpt,
            'post_name' => $postSlug,
            'post_status' => $request->input('post_status'),
            'post_type' => "attachment",
            'to_ping' => "www.maroc-leaks.com",
            'post_content_filtered' => $lng,
            'pinged' => "www.maroc-leaks.com",
            'ping_status' => "open",
            'guid' => $imageUrl,
        ]
    );

    // Retrieve the last inserted post(Image od the actual post) ID to put in the post image
    $postId = DB::getPdo()->lastInsertId();

    // Insert the post Image as post record where the post_type takes "attachmnt" data into the database
    DB::insert(
        "INSERT INTO www_posts (post_title, post_content, post_excerpt,post_name, post_status, post_type,image_id, to_ping, post_content_filtered, ping_status, pinged, guid, post_date, post_date_gmt) 
        VALUES (:post_title, :post_content, :post_excerpt,:post_name, :post_status, :post_type,:image_id, :to_ping, :post_content_filtered, :ping_status, :pinged, :guid, NOW(), NOW())",
        [
            'post_title' => $request->input('post_title'),
            'post_content' => $request->input('post_content'),
            'post_excerpt' => $request->input('post_excerpt'),
            'post_name' => $request->input('post_name'),
            'post_status' => $request->input('post_status'),
            'post_type' => "post",
            'to_ping' => "www.maroc-leaks.com",
            'post_content_filtered' => $lng,
            'pinged' => "www.maroc-leaks.com",
            'ping_status' => "open",
            'guid' => $imageUrl,
            'image_id'=> $postId
        ]
    );

    // Retrieve the last inserted post ID to put in the post image
    $postId = DB::getPdo()->lastInsertId();
    $fullPost = DB::table('www_posts')
              ->where('id', $postId)
              ->first();

    foreach($request->input('categories') as $catId){

        $fullCatgeory= DB::table('www_terms')
              ->where('term_id', $catId)
              ->first();

        

        DB::insert("INSERT INTO Post_Category_Mapping(PostTitle,PostId,CategoryName,CategoryId) values (:PostTitle,:PostId,:CategoryName,:CategoryId)",
        [
            'PostTitle' => $fullPost->post_title,
            'PostId' => $postId,
            'CategoryName' => $fullCatgeory->name,
            'CategoryId' => $catId
        ]
    );
    }

    

    $this->refreshCache();
    
    // Return a response with the post ID
    return response()->json([
        'message' => 'Post created successfully',
        'data' => [
            'post_id' => $postId,
            'image_id'=>$fullPost->image_id
        ],
    ], 201);

    }catch(Exception $e){
        Log::error($e->getMessage());
        return response()->json([
            'message' => 'Internal Error',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function storeTranslatedPost(Request $request)
{   


    try {
        //Log::info('Received Translated Post data:', $request->all());

        // Define required fields with user-friendly names
        $rules = [
            'post_title' => 'Post Title',
            'post_content' => 'Post Content',
            'post_name' => 'Post Slug',
            'post_excerpt' => 'Post Excerpt',
            'image_id' => 'Image',
            
        ];

        // Collect user-friendly error messages for missing fields
        $missingMessages = [];
        foreach ($rules as $field => $friendlyName) {
            if (!$request->has($field) || empty($request->input($field))) {
                $missingMessages[] = "The field '$friendlyName' is required.";
            }
        }

        // If any fields are missing, return a validation error response
        if (!empty($missingMessages)) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $missingMessages,
            ], 422);
        }

        if($request->input('lng')){
            $lng = $request->input('lng');
        }else{
            Log::info('No Post Language detected');
            $lng="No lng detected";
        }

        $postExcerpt = $request->input('post_excerpt');
        $postSlug = $request->input('post_name') ;
        // Insert the post into the database
        DB::insert(
            "INSERT INTO www_posts (post_parent,post_title, post_content, post_excerpt,post_name, post_status, post_type, image_id, to_ping, post_content_filtered, ping_status, pinged, guid, post_date, post_date_gmt) 
            VALUES (:post_parent,:post_title, :post_content, :post_excerpt,:post_name, :post_status, :post_type, :image_id, :to_ping, :post_content_filtered, :ping_status, :pinged, :guid, NOW(), NOW())",
            [
                'post_parent' => $request->input('post_parent'),
                'post_title' => $request->input('post_title'),
                'post_content' => $request->input('post_content'),
                'post_excerpt' => $postExcerpt,
                'post_name' => $postSlug,
                'post_status' => $request->input('post_status') ?: 'draft',
                'post_type' => 'post',
                'image_id' => $request->input('image_id'),
                'to_ping' => 'www.maroc-leaks.com',
                'post_content_filtered' => $lng,
                'ping_status' => 'open',
                'pinged' => 'www.maroc-leaks.com',
                'guid' => '', // Replace with appropriate GUID if needed
            ]
        );

        

        // Retrieve the last inserted post ID
        $postId = DB::getPdo()->lastInsertId();

        // Retrieve the full post record
        $fullPost = DB::table('www_posts')->where('id', $postId)->first();

        // Validate if categories exist in the request
        if ($request->has('categories') && is_array($request->input('categories'))) {
            foreach ($request->input('categories') as $catId) {
                // Retrieve the full category details
                $fullCategory = DB::table('www_terms')->where('term_id', $catId)->first();

                if ($fullCategory) {
                    // Insert the category mapping
                    DB::insert(
                        "INSERT INTO post_category_mapping (PostTitle, PostId, CategoryName, CategoryId) 
                        VALUES (:PostTitle, :PostId, :CategoryName, :CategoryId)",
                        [
                            'PostTitle' => $fullPost->post_title,
                            'PostId' => $postId,
                            'CategoryName' => $fullCategory->name,
                            'CategoryId' => $catId,
                        ]
                    );
                }
            }
        }
        $this->refreshCache();
        // Return a success response with the post ID
        return response()->json([
            'message' => 'Post Translation created successfully',
            'data' => [
                'post_id' => $postId,
            ],
        ], 201);

    } catch (Exception $e) {
        // Log the error and return an internal server error response
        Log::error('Error while storing translated post:', ['exception' => $e]);
        return response()->json([
            'message' => 'Internal Error',
            'error' => $e->getMessage(),
        ], 500);
    }
}


     




    /**
     * Display the specified resource.
     */
    public function show($id)
{
    // Increment the view_count for the specific post
    DB::table('www_posts')
        ->where('id', $id)
        ->increment('view_count');
    
    // Fetch the post along with its image (if any)
    $post = DB::table('www_posts')
              ->where('id', $id)
              ->first();

    // Check if the post has an image_id and fetch the image details
    if ($post && $post->image_id) {
        $attachment = DB::table('www_posts')
                        ->where('id', $post->image_id)
                        ->first();

        // Store the image path (GUID) in the post's guid field
        $post->guid = $attachment->guid ?? null;
    } else {
        $post->guid = null; // If there's no image, set guid to null
    }

    $categories = PostCategoryMapping::where('PostId', $post->ID)->get();
    
            // Log each query and the results
            Log::info("Fetching categories for PostId: {$post->ID}");
            Log::info("Categories found: " . json_encode($categories));
            $post->categories = $categories;

    // Return the post as a JSON response with the updated data
    return response()->json($post);
}

// public function getPostBySlug($slug)
// {
    
    
//     // Fetch the post along with its image (if any)
//     $post = DB::table('www_posts')
//               ->where('post_name', $slug)
//               ->first();
//     $view_count = $post -> view_count;
//     $view_count++;
//     DB::update("update www_post set view_count = view_count + 1 where post_name=:post_name",[
//         'post_name'=>$slug
//     ]);


//     // Check if the post has an image_id and fetch the image details
//     if ($post && $post->image_id) {
//         $attachment = DB::table('www_posts')
//                         ->where('id', $post->image_id)
//                         ->first();

//         // Store the image path (GUID) in the post's guid field
//         $post->guid = $attachment->guid ?? null;
//     } else {
//         $post->guid = null; // If there's no image, set guid to null
//     }

//     $categories = PostCategoryMapping::where('PostId', $post->ID)->get();
    
//             // Log each query and the results
//             Log::info("Fetching categories for PostId: {$post->ID}");
//             Log::info("Categories found: " . json_encode($categories));
//             $post->categories = $categories;

//     // Return the post as a JSON response with the updated data
//     return response()->json($post);
// }





    // public function getTopPostsBasedOnViews()
    // {
    //     // Retrieve the top 12 posts with post_type 'post' based on the view count, and in case of tie, order by post_date
    //     $posts = Post::select('www_posts.*', DB::raw('COUNT(post_views.post_id) as view_count'))
    //                 ->leftJoin('post_views', 'www_posts.id', '=', 'post_views.post_id')
    //                 ->where('www_posts.post_type','=', 'post') // Add condition to filter by post_type 'post'
    //                 ->where('www_posts.post_mime_type', 'like', '%image%') // Filter for post_type = 'post'
    //                 ->groupBy('www_posts.id') // Group by post ID to get the view count per post
    //                 ->orderByDesc('view_count') // Order by view count descending
    //                 ->orderByDesc('www_posts.post_date') // If tied, order by post date descending
    //                 ->limit(12) // Limit the result to the top 12 posts
    //                 ->get();
        
    //     return $posts;
    // }




    
        public function getTopPostsBasedOnViews()
    {
        $posts = Post::where('post_mime_type', 'like', '%image%')
            ->orderByDesc('post_date')
            ->limit(10)
            ->get();
    
        foreach ($posts as $post) {
            $categories = PostCategoryMapping::where('PostId', $post->ID)->get();
    
            // Log each query and the results
            Log::info("Fetching categories for PostId: {$post->ID}");
            Log::info("Categories found: " . json_encode($categories));
    
            $post->categoriesFound = $categories;
        }
    
        return response()->json($posts);
    }

    


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    

    public function storeIpRequest(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'status' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'countryCode' => 'required|string|max:10',
            'region' => 'required|string|max:10',
            'regionName' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'isp' => 'required|string|max:100',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'org' => 'nullable|string|max:255',
            'query' => 'required|string|max:50',
            'timezone' => 'required|string|max:100',
            'zip' => 'nullable|string|max:20',
        ]);

        // Create a new IpInfo record
        $ipInfo = IpInfo::create([
            'status' => $validated['status'],
            'country' => $validated['country'],
            'countryCode' => $validated['countryCode'],
            'region' => $validated['region'],
            'regionName' => $validated['regionName'],
            'city' => $validated['city'],
            'isp' => $validated['isp'],
            'lat' => $validated['lat'],
            'lon' => $validated['lon'],
            'org' => $validated['org'] ?? '',
            'query' => $validated['query'],
            'timezone' => $validated['timezone'],
            'zip' => $validated['zip'] ?? '',
        ]);

        // Return the created record (optional)
        return response()->json($ipInfo, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $validated = $request->validate([
            'post_author' => 'sometimes|required|integer',
            'post_title' => 'sometimes|required|string|max:255',
            'post_content' => 'sometimes|required|string',
            'post_status' => 'sometimes|required|string|max:20',
            'post_type' => 'sometimes|required|string|max:20',
        ]);

        $post->update($validated);
        return response()->json($post);
    }

    public function getTopCategories()
    {
    // Execute the query to get the top 20 categories with the most posts
    $categories = DB::select(
        'SELECT CategoryName, COUNT(PostId) AS post_count 
         FROM post_category_mapping
         GROUP BY CategoryName
         ORDER BY post_count DESC
         LIMIT 10'
    );
    
    return $categories;
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }

    // public function getLatestPosts()
    // {
    //     // Fetch the latest 10 posts with valid post_title and post_type 'post'
    //     $posts = Post::whereNotNull('post_title') // Ensure post_title is not null
    //                  ->where('post_title', '!=', '') // Ensure post_title is not empty
    //                  ->where('post_type', 'post')
    //                  ->where('post_status', 'publish') // Filter for post_type = 'post'
    //                  ->orderBy('post_date', 'desc') // Order by post_date (most recent first)
    //                  ->orderBy('view_count','desc')
    //                  ->take(20) // Limit to the 10 latest posts
    //                  ->get();
    
    //     foreach ($posts as $post) {
    //         // Fetch the attachment for the post
    //         $attachment = Post::where('id', $post->image_id)->first();
    //         $post->guid = $attachment->guid ?? null; // Attach guid if available
    
    //         // Fetch categories for the post
    //         $categories = PostCategoryMapping::where('PostId', $post->ID)->get();
    //         $post->categories = $categories->isNotEmpty() ? $categories : []; // Attach categories or default to empty array
    //     }
    
    //     return response()->json($posts);
    // }





    public function getPostBySlug($slug)
{
    // Fetch the post by slug and ensure the post is published
    $post = DB::selectOne('
        SELECT * 
        FROM www_posts 
        WHERE post_name = ? 
        AND post_status = "publish"
    ', [$slug]);

    // Handle case when the post is not found
    if (!$post) {
        return response()->json(['error' => 'Post not found'], 404);
    }

    // Fetch the attachment for the post image
    $attachment = DB::selectOne('
        SELECT * 
        FROM www_posts 
        WHERE ID = ?
    ', [$post->image_id]);

    $post->guid = $attachment ? $attachment->guid : null; // Attach guid if available

    // Fetch categories associated with the post
    $categories = DB::select('
        SELECT * 
        FROM post_category_mappings 
        WHERE PostId = ?
    ', [$post->ID]);

    $post->categories = $categories;

    // Increment view count using a raw query
    DB::update('
        UPDATE posts 
        SET view_count = view_count + 1 
        WHERE ID = ?
    ', [$post->ID]);

    // Return the post along with its categories
    return response()->json($post);
}

    


    public static function refreshCache()
    {
        $cacheKey = 'latest_posts';

    // Check if cache exists
    $cachedPosts = Cache::forget($cacheKey);
    Cache::forget("all_posts");
    //Log::info('Cache before fetching:', ['cached_posts' => $cachedPosts]);
        // Check if cached data is available
        $posts = Cache::remember('latest_posts', now()->addMinutes(60*24*7), function () {
            // Fetch the latest 20 posts with valid post_title and post_type 'post'
            $posts = Post::whereNotNull('post_title') // Ensure post_title is not null
                         ->where('post_title', '!=', '') // Ensure post_title is not empty
                         ->where('post_type', 'post')
                         ->where('post_status', 'publish') // Filter for post_type = 'post'
                         ->orderBy('post_date', 'desc') // Order by post_date (most recent first)
                         ->orderBy('view_count', 'desc') // Order by view_count (most viewed first)
                         ->take(30) // Limit to the 10 latest posts
                         ->get();
    
            // Get the Post IDs to query the categories for those posts
            $postIds = $posts->pluck('ID');
    
            // Fetch categories related to the posts in one query
            $categoryMappings = PostCategoryMapping::whereIn('PostId', $postIds)
                                                   ->get();
    
            // Group categories by PostId
            $categoriesMap = $categoryMappings->groupBy('PostId');
    
            // Attach categories and guid to each post
            foreach ($posts as $post) {
                // Fetch the attachment for the post image
                $attachment = Post::where('ID', $post->image_id)->first();
                $post->guid = $attachment ? $attachment->guid : null; // Attach guid if available
    
                // Get categories for the current post using the categoriesMap
                $post->categories = $categoriesMap->get($post->ID, collect())->values(); // Use collect() to return an empty collection if no categories are found
            }
    
            return $posts;
        });
    }
    

    
    
    public function getPostsByCategory($categoryName)
    {
        // Retrieve the Post IDs for the given category name
        $postIds = PostCategoryMapping::where('CategoryName', $categoryName)
                                      ->pluck('PostId'); // Only retrieve Post IDs
    
        // Retrieve the full posts from the Posts table using the Post IDs and order by post_date
        $posts = Post::whereIn('ID', $postIds)
                     ->orderBy('post_date', 'desc') // Order by post_date in descending order
                     ->get();
    
        // Return the posts as a JSON response
        return response()->json($posts);
    }
    

    
    

    public function incrementViewCount($postId)
{
    // Insert a new record in post_views to track the view
    DB::table('post_views')->insert([
        'post_id' => $postId,
        'viewed_at' => now(),
    ]);

    // Optionally, update the view count in the posts table (if necessary)
    DB::table('posts')
        ->where('ID', $postId)
        ->increment('view_count');
}

public function getLatestPosts()
    {   
        
        $cacheKey = 'latest_posts';

    // Check if cache exists
    $cachedPosts = Cache::get($cacheKey);
    //Log::info('Cache before fetching:', ['cached_posts' => $cachedPosts]);
        // Check if cached data is available
        $posts = Cache::remember('latest_posts', now()->addMinutes(60*24*7), function () {
            // Fetch the latest 20 posts with valid post_title and post_type 'post'
            $posts = Post::whereNotNull('post_title') // Ensure post_title is not null
                         ->where('post_title', '!=', '') // Ensure post_title is not empty
                         ->where('post_type', 'post')
                         ->where('post_status', 'publish') // Filter for post_type = 'post'
                         ->orderBy('post_date', 'desc') // Order by post_date (most recent first)
                         ->orderBy('view_count', 'desc') // Order by view_count (most viewed first)
                         ->take(30) // Limit to the 10 latest posts
                         ->get();
    
            // Get the Post IDs to query the categories for those posts
            $postIds = $posts->pluck('ID');
    
            // Fetch categories related to the posts in one query
            $categoryMappings = PostCategoryMapping::whereIn('PostId', $postIds)
                                                   ->get();
    
            // Group categories by PostId
            $categoriesMap = $categoryMappings->groupBy('PostId');
    
            // Attach categories and guid to each post
            foreach ($posts as $post) {
                // Fetch the attachment for the post image
                $attachment = Post::where('ID', $post->image_id)->first();
                $post->guid = $attachment ? $attachment->guid : null; // Attach guid if available
    
                // Get categories for the current post using the categoriesMap
                $post->categories = $categoriesMap->get($post->ID, collect())->values(); // Use collect() to return an empty collection if no categories are found
            }
    
            return $posts;
        });
    
        // Return the posts along with their categories
        return response()->json($posts);
    }

}
