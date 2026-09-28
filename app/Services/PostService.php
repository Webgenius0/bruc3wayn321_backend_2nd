<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(
        protected PostRepository $repository
    ) {}

    public function create(array $data)
    {

        $data['user_id'] = auth()->id();
        $data['is_draft'] = $data['is_draft'] ? '1' : '0';

        $data['is_published'] = $data['is_draft'] ? '0' : '1';


        $baseSlug = Str::slug(Str::words($data['content'] ?? 'post', 10, ''));

        $slug = $baseSlug;
        $count = 1;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count;
            $count++;
        }

        $data['slug'] = $slug;

        if (!empty($data['images'])) {

            foreach ($data['images'] as $image) {
                $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('post/image', $fileName, 'public');

                $images[] = 'storage/' . $path;
            }
            $data['images'] = $images;
        }

        if (!empty($data['audios'])) {
            foreach ($data['audios'] as $audio) {
                $fileName = Str::uuid() . '.' . $audio->getClientOriginalExtension();
                $path = $audio->storeAs('post/audio', $fileName, 'public');
                $audios[] = 'storage/' . $path;
            }
            $data['audios'] = $audios;
        }

        if (!empty($data['videos'])) {
            foreach ($data['videos'] as $video) {
                $fileName = Str::uuid() . '.' . $video->getClientOriginalExtension();
                $path = $video->storeAs('post/video', $fileName, 'public');
                $videos[] = 'storage/' . $path;
            }
            $data['videos'] = $videos;
        }

        return $this->repository->create($data);
    }

    public function getLatestPosts($limit = 20)
    {
        return $this->repository->getLatest($limit);
    }


    public function update(Post $post, array $data): Post
    {
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        if (!empty($data['content']) && $data['content'] !== $post->content) {
            $data['slug'] = Helper::generateUniqueSlug(
                $data['content'],
                $post->id
            );
        }

        if (!empty($data['images'])) {
            $images = [];

            foreach ($data['images'] as $image) {
                $fileName = uniqid() . '.' . $image->getClientOriginalExtension();

                $path = $image->storeAs(
                    'post/image',
                    $fileName,
                    'public'
                );

                $images[] = 'storage/' . $path;
            }

            $data['images'] = $images;
        }

        if (!empty($data['audios'])) {
            $audios = [];

            foreach ($data['audios'] as $audio) {
                $fileName = uniqid() . '.' . $audio->getClientOriginalExtension();

                $path = $audio->storeAs(
                    'post/audio',
                    $fileName,
                    'public'
                );

                $audios[] = 'storage/' . $path;
            }

            $data['audios'] = $audios;
        }

        if (!empty($data['videos'])) {
            $videos = [];

            foreach ($data['videos'] as $video) {
                $fileName = uniqid() . '.' . $video->getClientOriginalExtension();

                $path = $video->storeAs(
                    'post/video',
                    $fileName,
                    'public'
                );

                $videos[] = 'storage/' . $path;
            }

            $data['videos'] = $videos;
        }

        $post->update($data);

        return $post->fresh(['user']);
    }


    public function toggleLike(Post $post): array
    {
        $userId = auth()->id();

        $like = $post->likes()
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();

            return [
                'liked' => false,
                'likes_count' => $post->likes()->count(),
            ];
        }

        $post->likes()->create([
            'user_id' => $userId,
        ]);

        // dd($post->user_id, $userId);

        if ($post->user_id !== $userId) {
            try {

                $post->user->notify(new \App\Notifications\PostLiked($post, auth()->user()));
            } catch (\Throwable $e) {
                \Log::error("Failed to notify user: " . $e->getMessage());
            }
        }

        return [
            'liked' => true,
            'likes_count' => $post->likes()->count(),
        ];
    }

    public function addView(Post $post): array
    {
        $userId = auth()->id();

        $exists = $post->views()
            ->where('user_id', $userId)
            ->exists();

        if (!$exists) {
            $post->views()->create([
                'user_id' => $userId,
                'ip_address' => request()->ip(),
            ]);
        }

        return [
            'views_count' => $post->views()->count(),
        ];
    }
}
