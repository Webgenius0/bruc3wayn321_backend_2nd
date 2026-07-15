<?php

namespace App\Http\Controllers\API\Post;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostCommentResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LikeCommentApiController extends Controller
{
    use ApiResponse;


    public function __construct(
        protected PostService $PostService
    ) {}


    public function like(Post $post)
    {
        $like = $post->likes()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        if ($like->wasRecentlyCreated && $post->user_id !== auth()->id()) {
            try {
                $post->user->notify(new \App\Notifications\PostLiked($post, auth()->user()));
            } catch (\Throwable $e) {
                \Log::error("Failed to notify user on like: " . $e->getMessage());
            }
        }

        return $this->successResponse('Post liked.');
    }

    public function comment(
        Request $request,
        Post $post
    ) {
        $validate = Validator::make($request->all(), [
            'comment' => 'required'
        ]);

        if ($validate->fails()) {
            return $this->errorResponse($validate->errors()->first(), 422);
        }

        $comment = $post->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        if ($post->user_id !== auth()->id()) {
            try {
                $post->user->notify(new \App\Notifications\PostCommented($post, auth()->user(), $comment->comment));
            } catch (\Throwable $e) {
                \Log::error("Failed to notify user on comment: " . $e->getMessage());
            }
        }

        return $this->successResponse(
            'Comment added.'
        );
    }

    public function show(Post $post)
    {
        $post->views()->create([
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ]);

        return new PostResource(
            $post->loadCount([
                'likes',
                'comments',
                'views',
            ])
        );
    }
    public function toggleLike(Post $post)
    {
        $result = $this->PostService->toggleLike($post);

        return $this->successResponse(
            $result['liked']
                ? 'Post liked successfully.'
                : 'Post unliked successfully.',
            $result
        );
    }

    public function commentList(Post $post)
    {
        $comments = $post->comments()
            ->with('user')
            ->latest()
            ->paginate(20);

        return $this->successResponse(
            'Post comments.',
            PostCommentResource::collection($comments)
        );
    }

    public function view(Post $post)
    {
        $data = $this->PostService->addView($post);

        return $this->successResponse(
            'View counted.',
            $data
        );
    }
}
