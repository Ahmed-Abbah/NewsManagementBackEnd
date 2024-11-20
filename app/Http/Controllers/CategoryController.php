<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

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
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Update the category
        $category->update([
            'name' => $validated['name'],
        ]);

        return response()->json($category); // Return the updated category
    }

    // Delete a category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
