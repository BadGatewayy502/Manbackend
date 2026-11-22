<?php

namespace App\Services;

use App\Models\PopupBanner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PopupBannerService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PopupBanner::query()
            ->orderBy('display_order')
            ->orderBy('id');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->paginate($perPage);
    }

    public function activeList(): Collection
    {
        return PopupBanner::query()
            ->currentlyActive()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();
    }

    public function find(int $id): ?PopupBanner
    {
        return PopupBanner::find($id);
    }

    public function create(array $data, UploadedFile $image): PopupBanner
    {
        $data['image'] = $image->store('popup-banners', 'public');
        return PopupBanner::create($data);
    }

    public function update(PopupBanner $banner, array $data, ?UploadedFile $image = null): PopupBanner
    {
        if ($image) {
            $this->deleteImageFile($banner->image);
            $data['image'] = $image->store('popup-banners', 'public');
        }

        $banner->update($data);

        return $banner->fresh();
    }

    public function toggleActive(PopupBanner $banner): PopupBanner
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return $banner->fresh();
    }

    public function delete(PopupBanner $banner): bool
    {
        $this->deleteImageFile($banner->image);
        return (bool) $banner->delete();
    }

    private function deleteImageFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
