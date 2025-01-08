<?php
use App\Http\Middleware\LogGeolocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostCategoryMappingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/posts/latest', function () {
    
})->middleware(LogGeolocation::class);

//Public routes 
Route::get('categories', [CategoryController::class, 'index']); // Get all categories
Route::get('/post-category-mappings', [PostCategoryMappingController::class, 'index']);
Route::get('posts', [PostController::class, 'index']);
Route::get('posts/ipinfo', [PostController::class, 'getRegionFromIp']);
Route::get('/posts/latest',[PostController::class,'getLatestPosts']);
Route::get('/posts/topCategories',[PostController::class,'getTopCategories']);
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::get('posts/topPosts', [PostController::class, 'getTopPostsBasedOnViews']);
Route::get('posts/postsByCategory/{categoryName}', [PostController::class, 'getPostsByCategory']);
Route::get('/post-category-mappings/postCategories/{postId}', [PostCategoryMappingController::class, 'getPostCatgeories']);
Route::get('posts/{post}', [PostController::class, 'show']);
Route::get('post/slug/{slug}', [PostController::class, 'getPostBySlug']);
Route::get('/post/downloadFile/{filename}', [PostController::class, 'downloadFile']);
//Routes accessible only after successful authentication
Route::middleware('auth:sanctum')->group(function(){
    Route::post('posts/modify', [PostController::class, 'modifyPost']);
    Route::post('posts/delete', [PostController::class, 'deletePost']);
    Route::get('posts/create', [PostController::class, 'create']);
    Route::post('posts', [PostController::class, 'store']);
    Route::post('posts/storeIpRequestData', [PostController::class, 'storeIpRequest']);
    Route::post('saveImage', [PostController::class, 'storeImage']);
    Route::post('savePostTranslation', [PostController::class, 'storeTranslatedPost']);
    Route::post('categories', [CategoryController::class, 'store']); // Create a new category
    Route::post('categories/delete', [CategoryController::class, 'deleteCategory']); 
    Route::post('categories/save', [CategoryController::class, 'newCategory']); 
    Route::post('categories/update', [CategoryController::class, 'update']); 

    Route::put('categories/{id}', [CategoryController::class, 'update']); // Update a category
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']); // Delete a category
    
    Route::post('/post-category-mappings', [PostCategoryMappingController::class, 'store']);

    Route::put('/post-category-mappings/{postId}/{categoryId}', [PostCategoryMappingController::class, 'update']);
    Route::delete('/post-category-mappings/{postId}/{categoryId}', [PostCategoryMappingController::class, 'destroy']);  
    
    Route::get('/user',[AuthController::class,'user']);
});


