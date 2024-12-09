<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostCategoryMapping;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
        // Fetch posts where post_mime_type contains "image"
        $posts = Post::where('post_mime_type', 'like', '%image%')
                    ->paginate(10);
        
        return response()->json($posts);
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
        Log::info('Received Post data:', $request->all());

    // Define required fields with user-friendly names
    $rules = [
        'post_title' => 'Post Title',
        'post_content' => 'Post Content',
        'post_status' => 'Post Status',
        'post_type' => 'Post Type',
        'post_excerpt' => 'Post Excerpt',
        'post_name' => 'Post Slug',
        'guid' => 'Image',
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


    $postExcerpt = $request->input('post_excerpt') . 'post_image';
    $postSlug = $request->input('post_name') . '_post_image';
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
            'post_content_filtered' => $request->input('post_title'),
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
            'post_content_filtered' => "MarocLeaksV2",
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

        

        DB::insert("INSERT INTO post_category_mapping(PostTitle,PostId,CategoryName,CategoryId) values (:PostTitle,:PostId,:CategoryName,:CategoryId)",
        [
            'PostTitle' => $fullPost->post_title,
            'PostId' => $postId,
            'CategoryName' => $fullCatgeory->name,
            'CategoryId' => $catId
        ]
    );
    }
    
    // Return a response with the post ID
    return response()->json([
        'message' => 'Post created successfully',
        'data' => [
            'post_id' => $postId,
            'image_id'=>$fullPost->image_id
        ],
    ], 201);

    }catch(Exception $e){
        return response()->json([
            'message' => 'Internal Error',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function storeTranslatedPost(Request $request)
{
    try {
        Log::info('Received Translated Post data:', $request->all());

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

        $postExcerpt = $request->input('post_excerpt');
        $postSlug = $request->input('post_name') ;
        // Insert the post into the database
        DB::insert(
            "INSERT INTO www_posts (post_title, post_content, post_excerpt,post_name, post_status, post_type, image_id, to_ping, post_content_filtered, ping_status, pinged, guid, post_date, post_date_gmt) 
            VALUES (:post_title, :post_content, :post_excerpt,:post_name, :post_status, :post_type, :image_id, :to_ping, :post_content_filtered, :ping_status, :pinged, :guid, NOW(), NOW())",
            [
                'post_title' => $request->input('post_title'),
                'post_content' => $request->input('post_content'),
                'post_excerpt' => $postExcerpt,
                'post_name' => $postSlug,
                'post_status' => $request->input('post_status') ?: 'draft',
                'post_type' => 'post',
                'image_id' => $request->input('image_id'),
                'to_ping' => 'www.maroc-leaks.com',
                'post_content_filtered' => 'MarocLeaksV2',
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

public function getPostBySlug($slug)
{
    // Increment the view_count for the specific post
    DB::table('www_posts')
        ->where('post_name', $slug)
        ->increment('view_count');
    
    // Fetch the post along with its image (if any)
    $post = DB::table('www_posts')
              ->where('post_name', $slug)
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

    public function getLatestPosts()
    {
        // Fetch the latest 10 posts with valid post_title and post_type 'post'
        $posts = Post::whereNotNull('post_title') // Ensure post_title is not null
                     ->where('post_title', '!=', '') // Ensure post_title is not empty
                     ->where('post_type', 'post')
                     ->where('post_status', 'publish') // Filter for post_type = 'post'
                     ->orderBy('post_date', 'desc') // Order by post_date (most recent first)
                     ->orderBy('view_count','desc')
                     ->take(20) // Limit to the 10 latest posts
                     ->get();
    
        foreach ($posts as $post) {
            // Fetch the attachment for the post
            $attachment = Post::where('id', $post->image_id)->first();
            $post->guid = $attachment->guid ?? null; // Attach guid if available
    
            // Fetch categories for the post
            $categories = PostCategoryMapping::where('PostId', $post->ID)->get();
            $post->categories = $categories->isNotEmpty() ? $categories : []; // Attach categories or default to empty array
        }
    
        return response()->json($posts);
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


}
