<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * Display the authenticated user's reading collection.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'sedang-dibaca');
        abort_unless(
            is_string($tab) && in_array($tab, ['sedang-dibaca', 'daftar-bacaan', 'selesai-dibaca'], true),
            404
        );

        $user = Auth::user();
        $ebooks = null;
        $novels = null;

        if ($tab === 'daftar-bacaan') {
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
        }

        return view('collections.index', [
            'tab' => $tab,
            'ebooks' => $ebooks,
            'novels' => $novels,
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
