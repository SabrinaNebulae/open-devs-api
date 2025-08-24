<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\ProfileIndexRequest;
use App\Http\Resources\Api\Profile\ProfileResource;
use App\Models\Profile;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseApiController
{
    /**
     * Display and filter all Profiles
     * @throws AuthenticationException
     */
    public function index(ProfileIndexRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            throw new AuthenticationException('User not authenticated');
        }

        $params = $request->validated();
        $profiles = Profile::query()->with('skills');

        if ($params['skills']) {
            $profiles = $profiles->whereHas('skills', function ($query) use ($params) {
                $query->whereIn('skills.id', $params['skills']);
            });
        }

        if ($request->filled('order_by')) {
            $profiles = $profiles->orderBy($params['order_by'], $params['direction']);
        }

        $profiles = !empty($params['per_page']) ? $profiles->paginate($params['per_page']) : $profiles->get();

        if ($profiles->isEmpty()) {
            return response()->json(["message" => "No profiles found"], 200);
        }
        return response()->json(ProfileResource::collection($profiles), 200);
    }
}
