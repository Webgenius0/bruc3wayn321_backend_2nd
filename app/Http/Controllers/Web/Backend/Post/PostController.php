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
        $query = Post::with('user');

        if ($request->filled('publisher')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->publisher}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $posts = $query->latest()->paginate(15)->withQueryString();

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

        // Handle file uploads for update (append new files)
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('posts/images', 'public');
            }
            // Merge with existing images if any
            $validated['images'] = array_merge($post->images ?? [], $imagePaths);
        }
        if ($request->hasFile('audios')) {
            $audioPaths = [];
            foreach ($request->file('audios') as $audio) {
                $audioPaths[] = $audio->store('posts/audios', 'public');
            }
            $validated['audios'] = array_merge($post->audios ?? [], $audioPaths);
        }
        if ($request->hasFile('videos')) {
            $videoPaths = [];
            foreach ($request->file('videos') as $video) {
                $videoPaths[] = $video->store('posts/videos', 'public');
            }
            $validated['videos'] = array_merge($post->videos ?? [], $videoPaths);
        }

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
