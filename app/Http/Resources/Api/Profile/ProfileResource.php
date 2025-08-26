<?php

namespace App\Http\Resources\Api\Profile;

use App\Http\Resources\Api\Skill\SkillResource;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Profile
 */
class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user->name,
            'user_id' => $this->user_id,
            'bio' => $this->bio,
            'github_url' => $this->github_url,
            'linkedin_url' => $this->linkedin_url,
            'website_url' => $this->website_url,
            'created_at' => $this->created_at,
            'skills' => !empty($this->skills) ? SkillResource::collection($this->skills) : null,
        ];
    }
}
