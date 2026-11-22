<?php

namespace App\Services;

use App\Models\ProfilePage;
use App\Models\ProfilePagePhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfilePageService
{
    /** Halaman profil yang boleh diatur admin. */
    public const SLUGS = ['sejarah', 'visi-misi'];

    /** Untuk admin: selalu mengembalikan halaman (kosong jika belum pernah diisi). */
    public function forAdmin(string $slug): ProfilePage
    {
        $page = ProfilePage::with('photos')->where('slug', $slug)->first();

        if (! $page) {
            $page = new ProfilePage(['slug' => $slug, 'is_active' => true]);
            $page->setRelation('photos', collect());
        }

        return $page;
    }

    /** Untuk publik: hanya halaman yang sudah diisi dan aktif. */
    public function publicBySlug(string $slug): ?ProfilePage
    {
        return ProfilePage::with('photos')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $photos  null = jangan ubah daftar foto
     *                                                         (baris baru wajib memuat 'image' => UploadedFile)
     */
    public function save(string $slug, array $data, ?array $photos): ProfilePage
    {
        return DB::transaction(function () use ($slug, $data, $photos) {
            $page = ProfilePage::firstOrNew(['slug' => $slug]);
            $page->fill($data)->save();

            if ($photos !== null) {
                $this->syncPhotos($page, $photos);
            }

            return $page->load('photos');
        });
    }

    /**
     * Foto ber-id diperbarui keterangannya (dan fotonya bila diganti),
     * foto baru (tanpa id) dibuat, foto yang tidak dikirim lagi dihapus.
     */
    private function syncPhotos(ProfilePage $page, array $photos): void
    {
        $existing = $page->photos()->get()->keyBy('id');
        $keepIds = [];
        $order = 0;

        foreach (array_values($photos) as $row) {
            $file = $row['image'] ?? null;
            $model = isset($row['id']) ? $existing->get((int) $row['id']) : null;

            if ($model) {
                $payload = ['caption' => $row['caption'] ?? null, 'sort_order' => ++$order];
                if ($file instanceof UploadedFile) {
                    $this->deleteFile($model->image);
                    $payload['image'] = $file->store('profile-pages/'.$page->slug, 'public');
                }
                $model->update($payload);
                $keepIds[] = $model->id;
            } elseif ($file instanceof UploadedFile) {
                $created = $page->photos()->create([
                    'image' => $file->store('profile-pages/'.$page->slug, 'public'),
                    'caption' => $row['caption'] ?? null,
                    'sort_order' => ++$order,
                ]);
                $keepIds[] = $created->id;
            }
        }

        $page->photos()
            ->whereNotIn('id', $keepIds)
            ->get()
            ->each(function (ProfilePagePhoto $photo): void {
                $this->deleteFile($photo->image);
                $photo->delete();
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
