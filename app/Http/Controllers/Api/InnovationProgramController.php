<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InnovationProgramResource;
use App\Services\InnovationProgramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InnovationProgramController extends Controller
{
    public function __construct(
        private readonly InnovationProgramService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->service->paginate(
            ['search' => $request->query('search')],
            (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => InnovationProgramResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->normalizeInput($request);
        $data = $request->validate($this->rules(false));

        $program = $this->service->create(
            $this->programData($data),
            $request->file('cover'),
            $this->itemsFrom($request, $data),
        );

        return response()->json([
            'message' => 'Program inovasi berhasil ditambahkan.',
            'data' => new InnovationProgramResource($program),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $program = $this->service->find($id);

        if (! $program) {
            return response()->json(['message' => 'Program inovasi tidak ditemukan.'], 404);
        }

        return response()->json(['data' => new InnovationProgramResource($program)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $program = $this->service->find($id);

        if (! $program) {
            return response()->json(['message' => 'Program inovasi tidak ditemukan.'], 404);
        }

        $this->normalizeInput($request);
        $data = $request->validate($this->rules(true));

        $program = $this->service->update(
            $program,
            $this->programData($data),
            $request->file('cover'),
            $request->boolean('remove_cover'),
            $this->itemsFrom($request, $data),
        );

        return response()->json([
            'message' => 'Program inovasi berhasil diperbarui.',
            'data' => new InnovationProgramResource($program),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $program = $this->service->find($id);

        if (! $program) {
            return response()->json(['message' => 'Program inovasi tidak ditemukan.'], 404);
        }

        $this->service->delete($program);

        return response()->json(['message' => 'Program inovasi berhasil dihapus.']);
    }

    private function rules(bool $updating): array
    {
        $sometimes = $updating ? ['sometimes'] : [];

        return [
            'title' => [...$sometimes, 'required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'cover' => ['nullable', 'image', 'max:5120'],
            'remove_cover' => ['nullable', 'boolean'],
            'items_present' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array', 'max:30'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.content' => ['nullable', 'string'],
            'items.*.remove_image' => ['nullable', 'boolean'],
            'items.*.image' => ['nullable', 'image', 'max:5120'],
        ];
    }

    private function programData(array $data): array
    {
        $out = collect($data)->only(['title', 'tagline', 'description', 'sort_order', 'is_active'])->all();

        if (array_key_exists('sort_order', $out) && $out['sort_order'] === null) {
            $out['sort_order'] = 0;
        }

        return $out;
    }

    /** null = jangan ubah informasi; array (boleh kosong) = samakan dengan kiriman. */
    private function itemsFrom(Request $request, array $data): ?array
    {
        if (! $request->boolean('items_present')) {
            return null;
        }

        return array_values($data['items'] ?? []);
    }

    private function normalizeInput(Request $request): void
    {
        foreach (['is_active', 'remove_cover', 'items_present'] as $key) {
            if ($request->has($key)) {
                $request->merge([
                    $key => filter_var($request->input($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                ]);
            }
        }
    }
}
