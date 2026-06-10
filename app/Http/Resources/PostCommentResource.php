<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,

            'user' => [
                'id' => $this->user?->id,
                'first_name' => $this->user?->first_name,
                'last_name' => $this->user?->last_name,
                'avatar' => $this->user?->avatar,
            ],
            'comment' => $this->comment,
            'created_at' => $this->created_at
                ->diffForHumans(),
        ];
    }
}
