<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Category;
use DB;
class CategoryController extends Controller
{
    // Get all categories
    public function index()
    {
        $categories = Category::all(); // Fetch all categories from the database
        return response()->json($categories); // Return as JSON
    }

    // Create a new category
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Create the category
        $category = Category::create([
            'name' => $validated['name'],
        ]);

        return response()->json($category, 201); // Return the created category
    }

    // Update an existing category
    public function update(Request $request)
{   
    // Validate the request
    $validated = $request->validate([
        'id' => 'required|integer',
        'name' => 'required|string|max:255',
    ]);

    try {
        // Use a raw SQL query to update the category
        DB::update('UPDATE www_terms SET name = ? WHERE term_id = ?', [
            $validated['name'],
            $validated['id'],
        ]);
        DB::update('UPDATE post_category_mapping SET CategoryName = ? WHERE CategoryId = ?', [
            $validated['name'],
            $validated['id'],
        ]);

        PostController::refreshCache();
        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => [
                'id' => $validated['id'],
                'name' => $validated['name'],
            ]
        ], 200);
    } catch (\Exception $e) {
        // Handle exceptions
        return response()->json([
            'message' => 'Failed to update the category.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    // Delete a category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function deleteCategory(Request $request)
{
    // Validate the request to ensure an ID is provided
    $validated = $request->validate([
        'id' => 'required',
    ]);

    // Use a raw SQL query to delete the category
    try {
        DB::delete('DELETE FROM www_terms WHERE term_id = ?', [$validated['id']]);
        DB::delete('DELETE FROM post_category_mapping WHERE CategoryId = ?', [$validated['id']]);
        PostController::refreshCache();
        return response()->json([
            'message' => 'Category deleted successfully.'
        ], 200);
    } catch (\Exception $e) {
        Log::error($e->getMessage());
        // Handle exceptions
        return response()->json([
            'message' => 'Failed to delete the category.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function newCategory(Request $request)
{
    // Validate the request to ensure the 'name' field is provided
    $validated = $request->validate([
        'name' => 'required|string|max:255',
    ]);

    try {
        // Insert the new category using a raw SQL query
        DB::insert('INSERT INTO www_terms (name, slug, term_group) VALUES (?, ?, ?)', [
            $validated['name'], 
            $validated['name'], // Automatically generate a slug
            0 // Default term_group
        ]);

        // Get the last inserted ID (assuming term_id is the auto-increment ID)
        $categoryId = DB::getPdo()->lastInsertId();

        // Return the newly created category in the response
        return response()->json([
            'term_id' => $categoryId,
            'name' => $validated['name'],
            'slug' => $validated['name'], // Slug or whatever you prefer-
            'term_group' => 0, // Term group (could be dynamic if needed)
            'message' => 'Category saved successfully.'
        ], 200);

    } catch (\Exception $e) {
        Log::error($e->getMessage());

        return response()->json([
            'message' => 'Failed to save the category.',
            'error' => $e->getMessage()
        ], 500);
    }
}






}
