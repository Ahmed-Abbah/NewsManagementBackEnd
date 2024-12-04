<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostCategoryMapping;

class PostCategoryMappingController extends Controller
{
    // Fetch all mappings
    public function index()
    {
        $mappings = PostCategoryMapping::all();
        return response()->json($mappings);
    }

    // Create a new mapping
    public function store(Request $request)
    {
        $request->validate([
            'PostTitle' => 'nullable|string|max:255',
            'PostId' => 'required|integer',
            'CategoryName' => 'nullable|string|max:255',
            'CategoryId' => 'required|integer',
        ]);

        $mapping = PostCategoryMapping::create($request->all());
        return response()->json($mapping, 201);
    }

    // Get a specific mapping
    public function show($postId, $categoryId)
    {
        $mapping = PostCategoryMapping::where('PostId', $postId)
            ->where('CategoryId', $categoryId)
            ->first();

        if (!$mapping) {
            return response()->json(['error' => 'Mapping not found'], 404);
        }

        return response()->json($mapping);
    }

    public function getPostCatgeories($postId)
    {
        $mapping = PostCategoryMapping::where('PostId', $postId)

            ->get();

        if (!$mapping) {
            return [];
        }

        return $mapping;
    }

    // Update a mapping
    public function update(Request $request, $postId, $categoryId)
    {
        $mapping = PostCategoryMapping::where('PostId', $postId)
            ->where('CategoryId', $categoryId)
            ->first();

        if (!$mapping) {
            return response()->json(['error' => 'Mapping not found'], 404);
        }

        $request->validate([
            'PostTitle' => 'nullable|string|max:255',
            'CategoryName' => 'nullable|string|max:255',
        ]);

        $mapping->update($request->all());
        return response()->json($mapping);
    }

    // Delete a mapping
    public function destroy($postId, $categoryId)
    {
        $mapping = PostCategoryMapping::where('PostId', $postId)
            ->where('CategoryId', $categoryId)
            ->first();

        if (!$mapping) {
            return response()->json(['error' => 'Mapping not found'], 404);
        }

        $mapping->delete();
        return response()->json(['message' => 'Mapping deleted successfully']);
    }
}
