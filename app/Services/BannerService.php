<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerService
{
    private const DISK = 'public';

    private const DIRECTORY = 'banners';

    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function list(): Collection
    {
        return Banner::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function activeList(): Collection
    {
        return Banner::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function find(int $id): ?Banner
    {
        return Banner::find($id);
    }

    public function create(UploadedFile $image, bool $isActive = true): Banner
    {
        $maxOrder = (int) Banner::query()->max('sort_order');

        return Banner::create([
            'image' => $this->storeImage($image),
            'sort_order' => $maxOrder + 1,
            'is_active' => $isActive,
        ]);
    }

    /**
     * @param  list<UploadedFile>  $images
     * @return Collection<int, Banner>
     */
    public function createMany(array $images, bool $isActive = true): Collection
    {
        $created = collect();

        DB::transaction(function () use ($images, $isActive, $created): void {
            foreach ($images as $image) {
                if ($image instanceof UploadedFile) {
                    $created->push($this->create($image, $isActive));
                }
            }
        });

        return $created;
    }

    public function setActive(Banner $banner, bool $isActive): Banner
    {
        $banner->update(['is_active' => $isActive]);

        return $banner->fresh();
    }

    public function toggleActive(Banner $banner): Banner
    {
        return $this->setActive($banner, ! $banner->is_active);
    }

    /**
     * @param  list<int>  $ids
     */
    public function reorder(array $ids): Collection
    {
        DB::transaction(function () use ($ids): void {
            foreach (array_values($ids) as $index => $id) {
                Banner::query()
                    ->where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return $this->list();
    }

    public function delete(Banner $banner): bool
    {
        $path = $banner->image;
        $deleted = (bool) $banner->delete();

        if ($deleted) {
            $this->deleteImageFile($path);
        }

        return $deleted;
    }

    private function storeImage(UploadedFile $image): string
    {
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $extension = 'jpg';
        }

        $filename = Str::uuid()->toString().'.'.$extension;

        return $image->storeAs(self::DIRECTORY, $filename, self::DISK);
    }

    private function deleteImageFile(?string $path): void
    {
        if (! $path || str_contains($path, '..')) {
            return;
        }

        if (! str_starts_with($path, self::DIRECTORY.'/')) {
            return;
        }

        Storage::disk(self::DISK)->delete($path);
    }
}
