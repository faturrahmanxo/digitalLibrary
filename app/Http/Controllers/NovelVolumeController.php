<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNovelVolumeRequest;
use App\Http\Requests\UpdateNovelVolumeRequest;
use App\Models\Book;
use App\Models\NovelVolume;
use App\Services\DigitalFileStorage;
use App\Services\NovelVolumeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NovelVolumeController extends Controller
{
    public function index(Book $book): View
    {
        Gate::authorize('update', $book);
        $this->ensureNovel($book);

        $volumes = $book->novelVolumes()
            ->withCount([
                'borrowingItems as active_digital_loan_count' => fn (Builder $query) => $query
                    ->whereHas('borrowing', fn (Builder $borrowingQuery) => $borrowingQuery->activeDigital()),
            ])
            ->orderBy('volume_number')
            ->get();

        foreach ($volumes as $volume) {
            $volume->setAttribute(
                'digital_access_available',
                max(0, (int) $volume->digital_license_count - (int) $volume->active_digital_loan_count),
            );
        }

        return view('books.volumes.index', [
            'book' => $book,
            'volumes' => $volumes,
        ]);
    }

    public function create(Book $book): View
    {
        Gate::authorize('update', $book);
        $this->ensureNovel($book);

        return view('books.volumes.form', [
            'book' => $book,
            'volume' => new NovelVolume([
                'digital_license_count' => 1,
                'is_published' => false,
                'digital_lending_allowed' => false,
            ]),
            'isEdit' => false,
        ]);
    }

    public function store(
        StoreNovelVolumeRequest $request,
        Book $book,
        NovelVolumeService $volumeService,
        DigitalFileStorage $fileStorage,
    ): RedirectResponse {
        Gate::authorize('update', $book);
        $this->ensureNovel($book);

        $data = $request->validated();
        $digitalFilePath = null;
        if ($request->hasFile('digital_file')) {
            $digitalFilePath = $fileStorage->store($request->file('digital_file'), 'novel-volumes');
            $data['digital_file_path'] = $digitalFilePath;
        }
        unset($data['digital_file']);

        try {
            $volumeService->create($book, $data);
        } catch (ValidationException $exception) {
            if ($digitalFilePath) {
                Storage::disk('local')->delete($digitalFilePath);
            }

            throw $exception;
        }

        return to_route('books.volumes.index', $book)
            ->with('success', 'Volume novel berhasil ditambahkan.');
    }

    public function edit(Book $book, NovelVolume $volume): View
    {
        Gate::authorize('update', $book);
        $this->ensureNovel($book);
        $this->ensureVolumeBelongsToBook($book, $volume);

        return view('books.volumes.form', [
            'book' => $book,
            'volume' => $volume,
            'isEdit' => true,
        ]);
    }

    public function update(
        UpdateNovelVolumeRequest $request,
        Book $book,
        NovelVolume $volume,
        NovelVolumeService $volumeService,
        DigitalFileStorage $fileStorage,
    ): RedirectResponse {
        Gate::authorize('update', $book);
        $this->ensureNovel($book);
        $this->ensureVolumeBelongsToBook($book, $volume);

        $data = $request->validated();
        $digitalFilePath = null;
        if ($request->hasFile('digital_file')) {
            $digitalFilePath = $fileStorage->store($request->file('digital_file'), 'novel-volumes');
            $data['digital_file_path'] = $digitalFilePath;
        }
        unset($data['digital_file']);

        try {
            $volumeService->update($book, $volume, $data);
        } catch (ValidationException $exception) {
            if ($digitalFilePath) {
                Storage::disk('local')->delete($digitalFilePath);
            }

            throw $exception;
        }

        return to_route('books.volumes.index', $book)
            ->with('success', 'Data volume berhasil diperbarui.');
    }

    public function destroy(Book $book, NovelVolume $volume, NovelVolumeService $volumeService): RedirectResponse
    {
        Gate::authorize('update', $book);
        $this->ensureNovel($book);
        $this->ensureVolumeBelongsToBook($book, $volume);
        $volumeService->archive($book, $volume);

        return to_route('books.volumes.index', $book)
            ->with('success', 'Volume berhasil diarsipkan.');
    }

    private function ensureNovel(Book $book): void
    {
        abort_unless(
            $book->collection_type === 'novel'
            || str_contains(strtolower($book->category?->name ?? ''), 'novel'),
            404
        );
    }

    private function ensureVolumeBelongsToBook(Book $book, NovelVolume $volume): void
    {
        abort_unless($volume->book_id === $book->id, 404);
    }
}
