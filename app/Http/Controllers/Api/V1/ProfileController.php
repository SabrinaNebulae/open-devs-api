<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\ProfileIndexRequest;
use App\Http\Requests\Api\V1\ProfileUpdateRequest;
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
        $profiles = Profile::query()->with(['user', 'skills']);

        if (!empty($params['search'])) {
            $profiles = $profiles->whereHas('skills', function ($query) use ($params) {
                $query->where('name', 'like', '%' . $params['search'] . '%');
            })->orWhereHas('user', function ($query) use ($params) {
                    $query->where('name', 'like', '%' . $params['search'] . '%');
            });
        }

        if (!empty($params['skills'])) {
            $profiles = $profiles->whereHas('skills', function ($query) use ($params) {
                $query->whereIn('skills.id', $params['skills']);
            });
        }

        if (!empty($params['order_by'])) {
            $profiles = $profiles->orderBy($params['order_by'], $params['direction'] ?? 'desc');
        }

        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 12;
        $totalCount = $profiles->count();
        $profiles = $profiles->paginate($perPage);

        if ($profiles->isEmpty()) {
            return response()->json(["message" => "No profiles found"], 200);
        }
        return response()->json([
            'total_items' => $totalCount,
            'per_page' => $perPage,
            'items' => ProfileResource::collection($profiles),
            'next_page_url' => $profiles->nextPageUrl(),
            'current_page' => $profiles->currentPage(),
            'last_page' => $profiles->lastPage(),

        ], 200);
    }

    /**
     * @throws AuthenticationException
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            throw new AuthenticationException('User not authenticated');
        }

        $profile = Profile::query()->findOrFail($id);
        return response()->json(ProfileResource::make($profile), 200);
    }

    /**
     * @throws AuthenticationException
     */
    public function update(ProfileUpdateRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            throw new AuthenticationException('User not authenticated');
        }

        $profile = Profile::query()->findOrFail($id);

        $params = $request->validated();

        if (!empty($params['skills'])) {
            $profile->skills()->sync($params['skills']);
        }

        unset($params['skills']);

        $profile->fill($params)->save();

        return response()->json(ProfileResource::make($profile), 200);
    }
}
