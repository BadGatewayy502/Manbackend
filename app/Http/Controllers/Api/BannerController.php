<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReorderBannersRequest;
use App\Http\Requests\Api\StoreBannerRequest;
use App\Http\Requests\Api\UpdateBannerStatusRequest;
use App\Http\Resources\BannerResource;
use App\Http\Resources\PublicBannerResource;
use App\Services\BannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

class BannerController extends Controller
{
    public function __construct(
        private readonly BannerService $bannerService,
    ) {}

    public function active(): JsonResponse
    {
        $items = $this->bannerService->activeList();

        return response()->json([
            'data' => PublicBannerResource::collection($items)->resolve(),
        ]);
    }

    public function index(): JsonResponse
    {
        $items = $this->bannerService->list();

        return response()->json([
            'data' => BannerResource::collection($items)->resolve(),
        ]);
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $files = $this->uploadedFiles($request);

        if ($files === []) {
            return response()->json(['message' => 'Foto banner wajib diunggah.'], 422);
        }

        $isActive = $request->has('is_active')
            ? (bool) $request->boolean('is_active')
            : true;

        $created = $this->bannerService->createMany($files, $isActive);

        return response()->json([
            'message' => 'Banner berhasil diunggah.',
            'data' => BannerResource::collection($created)->resolve(),
        ], 201);
    }

    public function updateStatus(UpdateBannerStatusRequest $request, int $id): JsonResponse
    {
        $banner = $this->bannerService->find($id);

        if (! $banner) {
            return response()->json(['message' => 'Banner tidak ditemukan.'], 404);
        }

        $banner = $request->exists('is_active')
            ? $this->bannerService->setActive($banner, $request->boolean('is_active'))
            : $this->bannerService->toggleActive($banner);

        return response()->json([
            'message' => $banner->is_active
                ? 'Banner diaktifkan.'
                : 'Banner dinonaktifkan.',
            'data' => new BannerResource($banner),
        ]);
    }

    public function reorder(ReorderBannersRequest $request): JsonResponse
    {
        $items = $this->bannerService->reorder($request->validated('ids'));

        return response()->json([
            'message' => 'Urutan banner berhasil disimpan.',
            'data' => BannerResource::collection($items)->resolve(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $banner = $this->bannerService->find($id);

        if (! $banner) {
            return response()->json(['message' => 'Banner tidak ditemukan.'], 404);
        }

        $this->bannerService->delete($banner);

        return response()->json(['message' => 'Banner berhasil dihapus.']);
    }

    /**
     * @return list<UploadedFile>
     */
    private function uploadedFiles(StoreBannerRequest $request): array
    {
        $files = [];

        $images = $request->file('images');
        if (is_array($images)) {
            foreach ($images as $image) {
                if ($image instanceof UploadedFile) {
                    $files[] = $image;
                }
            }
        }

        $single = $request->file('image');
        if ($single instanceof UploadedFile) {
            $files[] = $single;
        }

        return $files;
    }
}
