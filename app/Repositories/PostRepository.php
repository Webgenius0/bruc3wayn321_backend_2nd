<?php

namespace App\Repositories;

use App\Models\Post;

class PostRepository
{
    public function create(array $data)
    {
        return Post::create($data);
    }

    public function findById($id)
    {
        return Post::find($id);
    }

    public function getLatest($limit = 20)
    {
        return Post::latest()->paginate($limit);
    }
}
