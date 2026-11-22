<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePopupBannerRequest;
use App\Http\Requests\Api\UpdatePopupBannerRequest;
use App\Http\Resources\PopupBannerResource;
use App\Http\Resources\PublicPopupBannerResource;
use App\Services\PopupBannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class PopupBannerController extends Controller
{
    public function __construct(
        private readonly PopupBannerService $popupBannerService,
    ) {}

    public function active(): JsonResponse
    {
        $items = $this->popupBannerService->activeList();

        return response()->json([
            'data' => PublicPopupBannerResource::collection($items)->resolve(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $items = $this->popupBannerService->paginate(
            ['search' => $request->query('search')],
            (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => PopupBannerResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(StorePopupBannerRequest $request): JsonResponse
    {
        $image = $request->file('image');
        if (! $image instanceof UploadedFile) {
            return response()->json(['message' => 'Gambar popup wajib diunggah.'], 422);
        }

        $banner = $this->popupBannerService->create(
            $request->safe()->except('image'),
            $image,
        );

        return response()->json([
            'message' => 'Popup banner berhasil ditambahkan.',
            'data' => new PopupBannerResource($banner),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $banner = $this->popupBannerService->find($id);

        if (! $banner) {
            return response()->json(['message' => 'Popup banner tidak ditemukan.'], 404);
        }

        return response()->json(['data' => new PopupBannerResource($banner)]);
    }

    public function update(UpdatePopupBannerRequest $request, int $id): JsonResponse
    {
        $banner = $this->popupBannerService->find($id);

        if (! $banner) {
            return response()->json(['message' => 'Popup banner tidak ditemukan.'], 404);
        }

        $image = $request->file('image');

        $banner = $this->popupBannerService->update(
            $banner,
            $request->safe()->except('image'),
            $image instanceof UploadedFile ? $image : null,
        );

        return response()->json([
            'message' => 'Popup banner berhasil diperbarui.',
            'data' => new PopupBannerResource($banner),
        ]);
    }

    public function updateStatus(int $id): JsonResponse
    {
        $banner = $this->popupBannerService->find($id);

        if (! $banner) {
            return response()->json(['message' => 'Popup banner tidak ditemukan.'], 404);
        }

        $banner = $this->popupBannerService->toggleActive($banner);

        return response()->json([
            'message' => $banner->is_active
                ? 'Popup banner diaktifkan.'
                : 'Popup banner dinonaktifkan.',
            'data' => new PopupBannerResource($banner),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $banner = $this->popupBannerService->find($id);

        if (! $banner) {
            return response()->json(['message' => 'Popup banner tidak ditemukan.'], 404);
        }

        $this->popupBannerService->delete($banner);

        return response()->json(['message' => 'Popup banner berhasil dihapus.']);
    }
}
