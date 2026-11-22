<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfilePageResource;
use App\Services\ProfilePageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfilePageController extends Controller
{
    public function __construct(
        private readonly ProfilePageService $service,
    ) {}

    public function show(string $slug): JsonResponse
    {
        return response()->json([
            'data' => (new ProfilePageResource($this->service->forAdmin($slug)))->resolve(),
        ]);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        foreach (['is_active', 'photos_present'] as $key) {
            if ($request->has($key)) {
                $request->merge([
                    $key => filter_var($request->input($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                ]);
            }
        }

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'photos_present' => ['nullable', 'boolean'],
            'photos' => ['nullable', 'array', 'max:30'],
            'photos.*.id' => ['nullable', 'integer'],
            'photos.*.caption' => ['nullable', 'string', 'max:255'],
            'photos.*.image' => ['nullable', 'image', 'max:5120'],
        ]);

        $photos = $request->boolean('photos_present')
            ? array_values($data['photos'] ?? [])
            : null;

        $page = $this->service->save(
            $slug,
            collect($data)->only(['title', 'subtitle', 'content', 'mission', 'is_active'])->all(),
            $photos,
        );

        return response()->json([
            'message' => 'Halaman berhasil disimpan.',
            'data' => (new ProfilePageResource($page))->resolve(),
        ]);
    }
}
