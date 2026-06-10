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
        $post->likes()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);

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

        $post->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

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
