<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostCategoryMappingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



//Public routes 
Route::get('categories', [CategoryController::class, 'index']); // Get all categories
Route::get('/post-category-mappings', [PostCategoryMappingController::class, 'index']);
Route::get('posts', [PostController::class, 'index']);
Route::get('/posts/latest',[PostController::class,'getLatestPosts']);
Route::get('/posts/topCategories',[PostController::class,'getTopCategories']);
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::get('posts/topPosts', [PostController::class, 'getTopPostsBasedOnViews']);
Route::get('posts/postsByCategory/{categoryName}', [PostController::class, 'getPostsByCategory']);
Route::get('/post-category-mappings/postCategories/{postId}', [PostCategoryMappingController::class, 'getPostCatgeories']);
Route::get('posts/{post}', [PostController::class, 'show']);
//Routes accessible only after successful authentication
Route::middleware('auth:sanctum')->group(function(){
    Route::get('posts/create', [PostController::class, 'create']);
    Route::post('posts', [PostController::class, 'store']);
    Route::post('saveImage', [PostController::class, 'storeImage']);
    Route::post('categories', [CategoryController::class, 'store']); // Create a new category
    Route::put('categories/{id}', [CategoryController::class, 'update']); // Update a category
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']); // Delete a category
    
    Route::post('/post-category-mappings', [PostCategoryMappingController::class, 'store']);

    Route::put('/post-category-mappings/{postId}/{categoryId}', [PostCategoryMappingController::class, 'update']);
    Route::delete('/post-category-mappings/{postId}/{categoryId}', [PostCategoryMappingController::class, 'destroy']);  
    
    Route::get('/user',[AuthController::class,'user']);
});


