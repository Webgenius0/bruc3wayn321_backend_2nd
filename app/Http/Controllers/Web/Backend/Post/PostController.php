<?php

namespace App\Http\Controllers\Web\Backend\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    /**
     * Display a listing of the posts.
     * Supports filtering by publisher name and publish date range.
     */
    public function index(Request $request)
    {
        $query = Post::with([
            'user',
            "likes",
            "comments",
            "views",
        ]);

        if ($request->filled('publisher')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('first_name', 'like', '%' . $request->publisher . '%')
                        ->orWhere('last_name', 'like', '%' . $request->publisher . '%');
                });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $posts = $query->latest()
            ->paginate($request->items ?? 15)
            ->through(function ($post) {
                $post->created_at_formatted = $post->created_at->format('d M Y, h:i A');
                $post->total_likes = $post->likes->count();
                $post->total_comments = $post->comments->count();
                $post->total_views = $post->views->count();
                $post->image = $post->images[0] ?? '';
                return $post;
            })
            ->withQueryString();


        return Inertia::render('backend/posts/index', [
            'posts' => $posts,
            'filters' => $request->only(['publisher', 'date_from', 'date_to']),
        ]);
    }

    /** Show the form for creating a new post. */
    public function create()
    {
        return Inertia::render('backend/posts/create');
    }

    /** Store a newly created post in storage. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'slug' => ['required', 'unique:posts,slug'],
            'content' => ['required'],
            'location' => ['nullable'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            // File arrays
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:2048'],
            'audios' => ['nullable', 'array'],
            'audios.*' => ['file', 'mimetypes:audio/mpeg,audio/mp3,audio/wav'],
            'videos' => ['nullable', 'array'],
            'videos.*' => ['file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo'],
            'is_draft' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
        ]);

        // Handle file uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('posts/images', 'public');
            }
            $validated['images'] = $imagePaths;
        }
        if ($request->hasFile('audios')) {
            $audioPaths = [];
            foreach ($request->file('audios') as $audio) {
                $audioPaths[] = $audio->store('posts/audios', 'public');
            }
            $validated['audios'] = $audioPaths;
        }
        if ($request->hasFile('videos')) {
            $videoPaths = [];
            foreach ($request->file('videos') as $video) {
                $videoPaths[] = $video->store('posts/videos', 'public');
            }
            $validated['videos'] = $videoPaths;
        }

        Post::create($validated);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully');
    }

    /** Display the specified post. */
    public function show(Post $post)
    {
        $post->load([
            "likes",
            "comments",
            "views",
            "user",
        ]);

        $post['likes_count'] = $post->likes()->count();
        $post['comments_count'] = $post->comments()->count();
        $post['views_count'] = $post->views()->count();

        return Inertia::render('backend/posts/show', ['post' => $post]);
    }

    /** Show the form for editing the specified post. */
    public function edit(Post $post)
    {
        $post->load([
            "likes",
            "comments",
            "views",
            "user",
        ]);

        $users = User::orderby('first_name', 'asc')->get();

        return Inertia::render('backend/posts/edit', [
            'post' => $post,
            'users' => $users,
        ]);
    }

    /** Update the specified post in storage. */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'slug' => ['required', 'unique:posts,slug,' . $post->id],
            'content' => ['required'],
            'location' => ['nullable'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],

            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['string'],
            'existing_audios' => ['nullable', 'array'],
            'existing_audios.*' => ['string'],
            'existing_videos' => ['nullable', 'array'],
            'existing_videos.*' => ['string'],

            // File arrays
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:2048'],
            'audios' => ['nullable', 'array'],
            'audios.*' => ['file', 'mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/mp4,audio/x-m4a,video/mp4'],
            'videos' => ['nullable', 'array'],
            'videos.*' => ['file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm'],
            'is_draft' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
        ]);

        $finalImages = $request->input('existing_images', []);
        $finalAudios = $request->input('existing_audios', []);
        $finalVideos = $request->input('existing_videos', []);

        // Handle file uploads for update (append new files)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $finalImages[] = 'storage/' . $image->store('posts/images', 'public');
            }
        }
        if ($request->hasFile('audios')) {
            foreach ($request->file('audios') as $audio) {
                $finalAudios[] = 'storage/' . $audio->store('posts/audios', 'public');
            }
        }
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $finalVideos[] = 'storage/' . $video->store('posts/videos', 'public');
            }
        }

        $validated['images'] = $finalImages;
        $validated['audios'] = $finalAudios;
        $validated['videos'] = $finalVideos;

        $post->update($validated);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully');
    }

    /** Remove the specified post from storage. */
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully');
    }
}
