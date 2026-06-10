<?php

namespace App\Http\Controllers\API\Post;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use ApiResponse;
    protected PostService $service;

    public function __construct(PostService $service)
    {
        $this->service = $service;
    }
    public function latest()
    {
        $posts = Post::with([
            'user',
            'likes',
        ])
            ->withCount([
                'likes',
                'comments',
                'views',
            ])->latest()
            ->paginate(10);

        return $this->successResponse(
            'Latest Posts',
            PostResource::collection($posts)
        );
    }

    /**
     * Trending posts
     */
    public function trending(): JsonResponse
    {
        $posts = Post::with([
            'user',
            'likes',
        ])
            ->withCount([
                'likes',
                'comments',
                'views',
            ])->trending()
            ->paginate(10);

        return $this->successResponse(
            'Trending Posts.',
            PostResource::collection($posts)->resolve()
        );
    }
    /**
     * Create post
     */
    public function store(PostStoreRequest $request)
    {
        $post = $this->service->create($request->validated());

        return $this->successResponse('Post create successfully', new PostResource($post));
    }

    /**
     * Show post
     */
    public function show(Post $post): JsonResponse
    {
        $postDetails = Post::where('id', $post->id)
            ->with([
                'user',
                'likes',
            ])
            ->withCount([
                'likes',
                'comments',
                'views',
            ])
            ->first();

        return $this->successResponse('Post details', new PostResource($postDetails));
    }

    /**
     * Update post
     */
    public function update(PostUpdateRequest $request, Post $post): JsonResponse
    {
        $post = $this->service->update(
            $post,
            $request->validated()
        );

        return $this->successResponse(
            'Post updated successfully.',
            new PostResource($post)
        );
    }

    /**
     * Delete post
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->service->delete($post);

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully.'
        ]);
    }

    /**
     * Save as draft
     */
    public function draft(Post $post): JsonResponse
    {
        $post->update([
            'is_draft' => true,
            'is_published' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post saved as draft.',
            'data' => $post
        ]);
    }

    /**
     * Publish post
     */
    public function publish(Post $post): JsonResponse
    {
        $post->update([
            'is_published' => true,
            'is_draft' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post published successfully.',
            'data' => $post
        ]);
    }
}
