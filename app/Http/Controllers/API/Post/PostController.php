<?php

namespace App\Http\Controllers\API\Post;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    /**
     * Display a listing of the latest posts.
     */
    public function latest(): JsonResponse
    {
        $posts = Post::latest()->get();
        return response()->json([
            'data' => $posts,
            'meta' => ['type' => 'latest']
        ]);
    }

    /**
     * Display a listing of trending posts.
     */
    public function trending(): JsonResponse
    {
        $posts = Post::trending()->get();
        return response()->json([
            'data' => $posts,
            'meta' => ['type' => 'trending']
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'slug' => 'required|unique:posts,slug',
            'content' => 'nullable|string',
            'location' => 'nullable|string',
            'images' => 'nullable|array',
            'audios' => 'nullable|array',
            'videos' => 'nullable|array',
            'is_draft' => 'sometimes|boolean',
            'is_published' => 'sometimes|boolean',
        ]);
        $post = Post::create($validated);
        return response()->json(['data' => $post], 201);
    }

    /**
     * Display the specified post (by slug).
     */
    public function show(Post $post): JsonResponse
    {
        return response()->json(['data' => $post]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'sometimes|unique:posts,slug,' . $post->id,
            'content' => 'nullable|string',
            'location' => 'nullable|string',
            'images' => 'nullable|array',
            'audios' => 'nullable|array',
            'videos' => 'nullable|array',
            'is_draft' => 'sometimes|boolean',
            'is_published' => 'sometimes|boolean',
        ]);
        $post->update($validated);
        return response()->json(['data' => $post]);
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post): JsonResponse
    {
        $post->delete();
        return response()->json(null, 204);
    }

    /**
     * Mark a post as draft.
     */
    public function draft(Post $post): JsonResponse
    {
        $post->update(['is_draft' => true]);
        return response()->json(['data' => $post]);
    }

    /**
     * Publish a post.
     */
    public function publish(Post $post): JsonResponse
    {
        $post->update(['is_published' => true, 'is_draft' => false]);
        return response()->json(['data' => $post]);
    }
}
