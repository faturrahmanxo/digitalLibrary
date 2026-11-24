<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\NovelVolume;
use App\Models\User;
use App\Services\DigitalBorrowingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DigitalBorrowingController extends Controller
{
    public function borrowBook(
        Request $request,
        Book $book,
        DigitalBorrowingService $digitalBorrowingService,
    ): RedirectResponse {
        Gate::authorize('create', Borrowing::class);

        /** @var User $user */
        $user = $request->user();
        $digitalBorrowingService->borrowBook($book, $user);

        return to_route('books.show', $book)
            ->with('status', 'E-book berhasil dipinjam selama 7 hari kalender.');
    }

    public function borrowVolume(
        Request $request,
        NovelVolume $volume,
        DigitalBorrowingService $digitalBorrowingService,
    ): RedirectResponse {
        Gate::authorize('create', Borrowing::class);

        /** @var User $user */
        $user = $request->user();
        $digitalBorrowingService->borrowVolume($volume, $user);

        return to_route('books.show', $volume->book_id)
            ->with('status', 'Volume berhasil dipinjam selama 7 hari kalender.');
    }

    public function readBook(
        Request $request,
        Book $book,
        DigitalBorrowingService $digitalBorrowingService,
    ): BinaryFileResponse {
        /** @var User $user */
        $user = $request->user();
        $digitalBorrowingService->assertBookReadAccess($book, $user);

        $path = Storage::disk('local')->path($book->digital_file_path);
        $filename = (Str::slug($book->title) ?: 'ebook-'.$book->id).'.pdf';

        $response = response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    public function downloadBook(
        Request $request,
        Book $book,
        DigitalBorrowingService $digitalBorrowingService,
    ): StreamedResponse {
        /** @var User $user */
        $user = $request->user();
        $digitalBorrowingService->assertBookDownloadAccess($book, $user);

        $extension = strtolower(pathinfo($book->digital_file_path, PATHINFO_EXTENSION));
        $filename = (Str::slug($book->title) ?: 'ebook-'.$book->id).'.'.$extension;

        return Storage::disk('local')->download($book->digital_file_path, $filename, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function downloadVolume(
        Request $request,
        NovelVolume $volume,
        DigitalBorrowingService $digitalBorrowingService,
    ): StreamedResponse {
        /** @var User $user */
        $user = $request->user();
        $digitalBorrowingService->assertVolumeDownloadAccess($volume, $user);

        $bookTitle = Str::slug($volume->book?->title ?? 'novel') ?: 'novel';
        $volumeTitle = filled($volume->title) ? Str::slug($volume->title) : 'volume-'.$volume->volume_number;
        $extension = strtolower(pathinfo($volume->digital_file_path, PATHINFO_EXTENSION));
        $filename = $bookTitle.'-'.$volumeTitle.'.'.$extension;

        return Storage::disk('local')->download($volume->digital_file_path, $filename, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
