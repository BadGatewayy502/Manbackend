<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AchievementResource;
use App\Http\Resources\FaqResource;
use App\Http\Resources\FeaturedProgramResource;
use App\Http\Resources\InnovationProgramResource;
use App\Http\Resources\GalleryResource;
use App\Http\Resources\NewsResource;
use App\Http\Resources\ProfilePageResource;
use App\Http\Resources\PublicBannerResource;
use App\Http\Resources\PublicPopupBannerResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\TestimonialResource;
use App\Services\PublicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function __construct(
        private readonly PublicService $publicService,
    ) {}

    public function home(): JsonResponse
    {
        $data = $this->publicService->home();

        return response()->json([
            'settings' => $data['settings'],
            'news' => NewsResource::collection($data['news'])->resolve(),
            'services' => ServiceResource::collection($data['services'])->resolve(),
            'achievements' => AchievementResource::collection($data['achievements'])->resolve(),
            'faqs' => FaqResource::collection($data['faqs'])->resolve(),
            'testimonials' => TestimonialResource::collection($data['testimonials'])->resolve(),
            'galleries' => GalleryResource::collection($data['galleries'])->resolve(),
            'popup_banners' => PublicPopupBannerResource::collection($data['popup_banners'])->resolve(),
            'hero_banners' => PublicBannerResource::collection($data['hero_banners'])->resolve(),
            'stats' => $data['stats'],
        ]);
    }

    public function galleries(): JsonResponse
    {
        return response()->json([
            'data' => GalleryResource::collection($this->publicService->galleries())->resolve(),
        ]);
    }

    public function news(Request $request): JsonResponse
    {
        $news = $this->publicService->newsList((int) $request->query('per_page', 12));

        return response()->json([
            'data' => NewsResource::collection($news->items())->resolve(),
            'meta' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
            ],
        ]);
    }

    public function profilePage(string $slug): JsonResponse
    {
        $page = $this->publicService->profilePage($slug);

        return response()->json([
            'data' => $page ? (new ProfilePageResource($page))->resolve() : null,
        ]);
    }

    public function featuredPrograms(): JsonResponse
    {
        return response()->json([
            'data' => FeaturedProgramResource::collection($this->publicService->featuredPrograms())->resolve(),
        ]);
    }

    public function featuredProgramShow(string $slug): JsonResponse
    {
        $program = $this->publicService->featuredProgramBySlug($slug);

        if (! $program) {
            return response()->json(['message' => 'Program unggulan tidak ditemukan.'], 404);
        }

        return response()->json(['data' => (new FeaturedProgramResource($program))->resolve()]);
    }

    public function innovationPrograms(): JsonResponse
    {
        return response()->json([
            'data' => InnovationProgramResource::collection($this->publicService->innovationPrograms())->resolve(),
        ]);
    }

    public function innovationProgramShow(string $slug): JsonResponse
    {
        $program = $this->publicService->innovationProgramBySlug($slug);

        if (! $program) {
            return response()->json(['message' => 'Program inovasi tidak ditemukan.'], 404);
        }

        return response()->json(['data' => (new InnovationProgramResource($program))->resolve()]);
    }

    public function newsShow(string $slug): JsonResponse
    {
        $news = $this->publicService->newsBySlug($slug);

        if (! $news) {
            return response()->json(['message' => 'Berita tidak ditemukan.'], 404);
        }

        return response()->json(['data' => new NewsResource($news)]);
    }
}
