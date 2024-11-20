<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::all();
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
        $validated = $request->validate([
            'post_author' => 'required|integer',
            'post_title' => 'required|string|max:255',
            'post_content' => 'required|string',
            'post_status' => 'required|string|max:20',
            'post_type' => 'required|string|max:20',
        ]);

        $post = Post::create($validated);
        return response()->json($post, 201);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = Post::findOrFail($id);
        return response()->json($post);
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


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }

}
