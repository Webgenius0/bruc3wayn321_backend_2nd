<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $files = [];

        foreach (($this->images ?? []) as $image) {
            $files[] = [
                'type' => 'image',
                'url'  =>  is_string($image)
                    ? asset($image)
                    : $image,
            ];
        }

        foreach (($this->videos ?? []) as $video) {
            $files[] = [
                'type' => 'video',
                'url'  => is_string($video)
                    ? asset($video)
                    : $video,
            ];
        }

        foreach (($this->audios ?? []) as $audio) {
            $files[] = [
                'type' => 'audio',
                'url'  => is_string($audio)
                    ? asset($audio)
                    : $audio,
            ];
        }
        // return parent::toArray($request);
        return [

            'id' => $this->id ?? 0,
            'slug' => $this->slug ?? '',
            'content' => $this->content ?? '',
            'publisher' => $this->user ? [
                "id"            => $this->user->id,
                "first_name"    => $this->user->first_name,
                "last_name"     => $this->user->last_name,
                "name"           => $this->user->first_name . " " . $this->user->last_name,
                "user_name"     => $this->user->user_name,
                "avatar"        => $this->user->avatar,
                "is_following"  => auth()->check() ? auth()->user()->isFollowing($this->user) : false,
                "followers_count" => $this->user->getFollowersCount(),
            ] : '',
            'location' => $this->location ?? '',
            'files' => $files,
            'images' => collect($this->images ?? [])
                ->map(function ($image) {
                    return is_string($image)
                        ? asset($image)
                        : $image;
                })
                ->values()
                ->toArray(),
            'audios' => collect($this->audios ?? [])
                ->map(function ($audio) {
                    return is_string($audio)
                        ? asset($audio)
                        : $audio;
                })
                ->values()
                ->toArray(),
            'videos' => collect($this->videos ?? [])
                ->map(fn($video) => asset($video))
                ->values()
                ->toArray(),
            'visibility' => $this->visibility,
            'is_draft' => $this->is_draft,
            'is_published' => $this->is_published,
            'likes_count' => $this->likes_count ?? 0,
            'comments_count' => $this->comments_count ?? 0,
            'views_count' => $this->views_count ?? 0,

            'is_liked' => auth()->check()
                ? $this->likes->contains('user_id', auth()->id())
                : false,
            'created_at' => Carbon::parse($this->created_at)->format('M d, Y'),
        ];
    }
}
