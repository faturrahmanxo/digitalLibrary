<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * Display the authenticated user's reading collection and borrowing portal.
     */
    public function index(Request $request): View
    {
        $rawTab = $request->query('tab', 'sedang-dibaca');
        abort_unless(
            is_string($rawTab) && in_array($rawTab, [
                'sedang-dibaca',
                'daftar-bacaan',
                'selesai-dibaca',
                'pengajuan',
                'daftar-pengajuan',
                'peminjaman-aktif',
                'riwayat',
            ], true),
            404
        );

        // Normalize tab aliases
        $tab = match ($rawTab) {
            'peminjaman-aktif' => 'sedang-dibaca',
            'daftar-pengajuan' => 'pengajuan',
            'riwayat' => 'selesai-dibaca',
            default => $rawTab,
        };

        $user = Auth::user();

        // Strict ownership query: only the current user's borrowings
        $userBorrowingsQuery = Borrowing::query()
            ->with(['book.category', 'book.shelf', 'items.book.category', 'items.book.shelf'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('borrower_user_id', $user->id);
                if ($user->email) {
                    $q->orWhereHas('member', fn ($mq) => $mq->where('email', $user->email));
                }
            });

        // Tab counts for badges
        $activeCount = (clone $userBorrowingsQuery)
            ->whereIn('status', [Borrowing::STATUS_BORROWED, Borrowing::STATUS_APPROVED])
            ->count();

        $pendingCount = (clone $userBorrowingsQuery)
            ->where('status', Borrowing::STATUS_PENDING)
            ->count();

        $historyCount = (clone $userBorrowingsQuery)
            ->whereIn('status', [
                Borrowing::STATUS_RETURNED,
                Borrowing::STATUS_REJECTED,
                Borrowing::STATUS_CANCELLED,
            ])
            ->count();

        $favoritesCount = $user->favoriteBooks()->count();

        // Data based on active tab
        $activeBorrowings = null;
        $pendingBorrowings = null;
        $historyBorrowings = null;
        $ebooks = collect();
        $novels = collect();
        $physicalBooks = collect();

        if ($tab === 'sedang-dibaca') {
            $activeBorrowings = (clone $userBorrowingsQuery)
                ->whereIn('status', [Borrowing::STATUS_BORROWED, Borrowing::STATUS_APPROVED])
                ->latest('id')
                ->paginate(10, ['*'], 'active_page')
                ->withQueryString();
        } elseif ($tab === 'pengajuan') {
            $pendingBorrowings = (clone $userBorrowingsQuery)
                ->where('status', Borrowing::STATUS_PENDING)
                ->latest('id')
                ->paginate(10, ['*'], 'pending_page')
                ->withQueryString();
        } elseif ($tab === 'selesai-dibaca') {
            $historyBorrowings = (clone $userBorrowingsQuery)
                ->whereIn('status', [
                    Borrowing::STATUS_RETURNED,
                    Borrowing::STATUS_REJECTED,
                    Borrowing::STATUS_CANCELLED,
                ])
                ->latest('id')
                ->paginate(10, ['*'], 'history_page')
                ->withQueryString();
        } elseif ($tab === 'daftar-bacaan') {
            $ebooks = $user->favoriteBooks()
                ->with('category')
                ->where('collection_type', 'e-book')
                ->latest('favorites.created_at')
                ->simplePaginate(12, ['*'], 'ebooks_page')
                ->withQueryString();

            $novels = $user->favoriteBooks()
                ->with('category')
                ->where('collection_type', 'novel')
                ->latest('favorites.created_at')
                ->simplePaginate(12, ['*'], 'novels_page')
                ->withQueryString();

            $physicalBooks = $user->favoriteBooks()
                ->with(['category', 'shelf'])
                ->whereIn('collection_type', [Book::COLLECTION_TYPE_PHYSICAL, 'fisik'])
                ->latest('favorites.created_at')
                ->simplePaginate(12, ['*'], 'physical_page')
                ->withQueryString();
        }

        return view('collections.index', [
            'tab' => $tab,
            'activeBorrowings' => $activeBorrowings,
            'pendingBorrowings' => $pendingBorrowings,
            'historyBorrowings' => $historyBorrowings,
            'ebooks' => $ebooks,
            'novels' => $novels,
            'physicalBooks' => $physicalBooks,
            'activeCount' => $activeCount,
            'pendingCount' => $pendingCount,
            'historyCount' => $historyCount,
            'favoritesCount' => $favoritesCount,
        ]);
    }

    /**
     * Toggle favorite status of a book.
     */
    public function toggle(Request $request, Book $book): JsonResponse|RedirectResponse
    {
        if (! Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'unauthenticated',
                    'message' => 'Silakan masuk dengan akun kampus terlebih dahulu.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->guest(route('login'))->with('status', 'Silakan masuk dengan akun kampus untuk menyimpan buku ke favorit.');
        }

        $user = Auth::user();
        $existing = Favorite::where('user_id', $user->id)->where('book_id', $book->id)->first();

        if ($existing) {
            $existing->delete();
            $status = 'removed';
            $isFavorited = false;
            $message = "Buku \"{$book->title}\" dihapus dari daftar favorit.";
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
            $status = 'added';
            $isFavorited = true;
            $message = "Buku \"{$book->title}\" berhasil ditambahkan ke daftar favorit Anda.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => $status,
                'is_favorited' => $isFavorited,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
