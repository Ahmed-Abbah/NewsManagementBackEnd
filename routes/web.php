<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostCategoryMappingController;
Route::resource('posts', PostController::class);


Route::get('categories', [CategoryController::class, 'index']); // Get all categories
Route::post('categories', [CategoryController::class, 'store']); // Create a new category
Route::put('categories/{id}', [CategoryController::class, 'update']); // Update a category
Route::delete('categories/{id}', [CategoryController::class, 'destroy']); // Delete a category


Route::get('/post-category-mappings', [PostCategoryMappingController::class, 'index']);
Route::post('/post-category-mappings', [PostCategoryMappingController::class, 'store']);
Route::get('/post-category-mappings/{postId}/{categoryId}', [PostCategoryMappingController::class, 'show']);
Route::put('/post-category-mappings/{postId}/{categoryId}', [PostCategoryMappingController::class, 'update']);
Route::delete('/post-category-mappings/{postId}/{categoryId}', [PostCategoryMappingController::class, 'destroy']);
