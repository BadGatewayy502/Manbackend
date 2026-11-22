<?php

namespace App\Services;

use App\Models\FeaturedProgram;
use App\Models\FeaturedProgramItem;
use App\Support\SlugGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FeaturedProgramService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = FeaturedProgram::query()
            ->withCount('items')
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', $search)->orWhere('tagline', 'like', $search);
            });
        }

        return $query->paginate($perPage);
    }

    public function activeList(): Collection
    {
        return FeaturedProgram::query()
            ->withCount('items')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    public function activeBySlug(string $slug): ?FeaturedProgram
    {
        return FeaturedProgram::query()
            ->with('items')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function find(int $id): ?FeaturedProgram
    {
        return FeaturedProgram::withCount('items')->with('items')->find($id);
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $items  null = jangan ubah daftar informasi
     *                                                         (tiap baris boleh memuat 'image' => UploadedFile)
     */
    public function create(array $data, ?UploadedFile $cover, ?array $items): FeaturedProgram
    {
        return DB::transaction(function () use ($data, $cover, $items) {
            $data['slug'] = SlugGenerator::unique($data['title'], new FeaturedProgram);

            if ($cover) {
                $data['cover_image'] = $cover->store('featured-programs', 'public');
            }

            $program = FeaturedProgram::create($data);
            $this->syncItems($program, $items ?? []);

            return $program->load('items')->loadCount('items');
        });
    }

    public function update(
        FeaturedProgram $program,
        array $data,
        ?UploadedFile $cover,
        bool $removeCover,
        ?array $items,
    ): FeaturedProgram {
        return DB::transaction(function () use ($program, $data, $cover, $removeCover, $items) {
            if (! empty($data['title']) && $data['title'] !== $program->title) {
                $data['slug'] = SlugGenerator::unique($data['title'], new FeaturedProgram, 'slug', $program->id);
            }

            if ($cover) {
                $this->deleteFile($program->cover_image);
                $data['cover_image'] = $cover->store('featured-programs', 'public');
            } elseif ($removeCover) {
                $this->deleteFile($program->cover_image);
                $data['cover_image'] = null;
            }

            $program->update($data);

            if ($items !== null) {
                $this->syncItems($program, $items);
            }

            return $program->fresh('items')->loadCount('items');
        });
    }

    public function delete(FeaturedProgram $program): bool
    {
        return DB::transaction(function () use ($program) {
            $this->deleteFile($program->cover_image);
            foreach ($program->items as $item) {
                $this->deleteFile($item->image);
            }

            return (bool) $program->delete();
        });
    }

    /**
     * Samakan daftar informasi dengan yang dikirim admin:
     * item ber-id diperbarui, tanpa id dibuat baru, yang tidak dikirim dihapus.
     */
    private function syncItems(FeaturedProgram $program, array $items): void
    {
        $existing = $program->items()->get()->keyBy('id');
        $keepIds = [];
        $order = 0;

        foreach (array_values($items) as $row) {
            $order++;
            $file = $row['image'] ?? null;
            $payload = [
                'title' => $row['title'] ?? null,
                'content' => $row['content'] ?? null,
                'sort_order' => $order,
            ];

            $model = isset($row['id']) ? $existing->get((int) $row['id']) : null;

            if ($model) {
                if ($file instanceof UploadedFile) {
                    $this->deleteFile($model->image);
                    $payload['image'] = $file->store('featured-programs/items', 'public');
                } elseif (! empty($row['remove_image'])) {
                    $this->deleteFile($model->image);
                    $payload['image'] = null;
                }
                $model->update($payload);
            } else {
                if ($file instanceof UploadedFile) {
                    $payload['image'] = $file->store('featured-programs/items', 'public');
                }
                $model = $program->items()->create($payload);
            }

            $keepIds[] = $model->id;
        }

        $program->items()
            ->whereNotIn('id', $keepIds)
            ->get()
            ->each(function (FeaturedProgramItem $item): void {
                $this->deleteFile($item->image);
                $item->delete();
            });
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            // dihapus setelah transaksi database benar-benar tersimpan
            DB::afterCommit(fn () => Storage::disk('public')->delete($path));
        }
    }
}
