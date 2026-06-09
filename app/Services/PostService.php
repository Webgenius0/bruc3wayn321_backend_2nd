<?php

namespace App\Services;

use App\Repositories\PostRepository;

class PostService
{
    public function __construct(
        protected PostRepository $repository
    ) {}

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function getLatestPosts($limit = 20)
    {
        return $this->repository->getLatest($limit);
    }
}
