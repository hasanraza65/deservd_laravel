<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BoxOption\BoxOptionRequest;
use App\Http\Resources\BoxOptionResource;
use App\Models\BoxOption;
use Illuminate\Http\JsonResponse;

class BoxOptionController extends Controller
{
    use ApiResponses;

    public function index(): JsonResponse
    {
        $options = BoxOption::query()->orderBy('sort_order')->get();

        return $this->success(BoxOptionResource::collection($options));
    }

    public function store(BoxOptionRequest $request): JsonResponse
    {
        $option = BoxOption::create($request->validated());

        return $this->success(new BoxOptionResource($option->fresh()), 'Box option created successfully.', 201);
    }

    public function update(BoxOptionRequest $request, BoxOption $boxOption): JsonResponse
    {
        $boxOption->update($request->validated());

        return $this->success(new BoxOptionResource($boxOption->fresh()), 'Box option updated successfully.');
    }

    public function destroy(BoxOption $boxOption): JsonResponse
    {
        $boxOption->delete();

        return $this->success(null, 'Box option deleted successfully.');
    }
}
