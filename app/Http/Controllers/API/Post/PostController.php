<?php

namespace App\Http\Controllers\API\Post;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
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

    /**
     * Get posts from a specific user
     */
    public function userPosts($id): JsonResponse
    {
        $user = User::findOrFail($id);

        $isFollowing = auth()->user()->isFollowing($user);

        // dd($isFollowing);

        $posts = Post::where('user_id', $user->id)
            ->with([
                'user',
                'likes',
            ])
            ->withCount([
                'likes',
                'comments',
                'views',
            ])
            ->where('visibility', true)
            ->latest()
            ->get();

        return $this->successResponse(
            $user->first_name . ' Posts',
            PostResource::collection($posts)
        );
    }

    /**
     * Get posts from users that current user follows
     */
    public function followersPosts(): JsonResponse
    {
        $currentUser = auth()->user();
        $followingUserIds = $currentUser->following()->pluck('users.id');

        $posts = Post::whereIn('user_id', $followingUserIds)
            ->with([
                'user',
                'likes',
            ])
            ->withCount([
                'likes',
                'comments',
                'views',
            ])
            ->where('visibility', true)
            ->latest()
            ->get();

        return $this->successResponse(
            'Posts from followers',
            PostResource::collection($posts)
        );
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
            ->where('visibility', true)
            ->get();

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
            ->where('visibility', true)
            ->get();

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
        $user = $request->user();

        // Check post creation limit
        if (!$user->canPerformAction('posts')) {
            return $this->errorResponse(
                'You have reached your community post limit. Please upgrade to a paid subscription for unlimited posts.',
                429
            );
        }

        $post = $this->service->create($request->validated());

        // Increment post count
        $user->incrementUsage('posts');

        return $this->successResponse('Post created successfully', new PostResource($post));
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

        $post->views()->firstOrCreate(
            [
                'post_id' => $post->id,
                'user_id' => auth()->id(),
            ],
            [
                'ip_address' => request()->ip(),
            ]
        );

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

    /**
     * Publish post
     */
    public function postVisibility(Post $post): JsonResponse
    {
        $post->update([
            'visibility' => !$post->visibility,
        ]);

        return response()->json([
            'success' => true,
            'message' => $post->visibility
                ? 'Post published successfully.'
                : 'Post hidden successfully.',
            'data' => $post->fresh()
        ]);
    }


    public function myPosts(Request $request): JsonResponse
    {
        $posts = Post::where('user_id', auth()->id())
            ->with([
                'user',
                'likes',
            ])
            ->withCount([
                'likes',
                'comments',
                'views',
            ])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->successResponse(
            'My posts retrieved successfully.',
            PostResource::collection($posts)
        );
    }
}
