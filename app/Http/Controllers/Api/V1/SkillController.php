<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SkillIndexRequest;
use App\Http\Requests\Api\V1\SkillStoreRequest;
use App\Http\Resources\Api\Skill\SkillResource;
use App\Models\Skill;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    /**
     * Display all skills
     * @throws AuthenticationException
     */
    public function index(SkillIndexRequest $request) {
        $user = Auth::user();
        if (! $user) {
            throw new AuthenticationException('User not authenticated');
        }

        $params = $request->validated();
        $skills = Skill::query();

        if ( !empty($params['search']))  {
            $skills = $skills->where('name', 'like', '%' . $params['search'] . '%')
                ->orWhere('description', 'like', '%' . $params['search'] . '%');
        }

        if (!empty($params['order_by'])) {
            $skills = $skills->orderBy($params['order_by'], $params['direction'] ?? 'desc');
        }
        $skills = $skills->get();

        return SkillResource::collection($skills);
    }

    /**
     * @throws AuthenticationException
     */
    public function store(SkillStoreRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            throw new AuthenticationException('User not authenticated');
        }
        $params = $request->validated();
        $skill = new Skill();
        $skill->name = $params['name'];
        $skill->description = $params['description'] ?? '';
        $skill->save();

        return response()->json(new SkillResource($skill), 201);
    }
}
