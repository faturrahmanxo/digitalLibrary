<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');

        $featuredBooks = Book::with('category')
            ->latest('id')
            ->take(6)
            ->get();

        $categories = Category::withCount('books')
            ->orderBy('name')
            ->get();

        $latestEbooks = Book::with('category')
            ->where('collection_type', 'e-book')
            ->latest('id')
            ->take(5)
            ->get();

        $latestNovels = Book::with('category')
            ->where('collection_type', 'novel')
            ->latest('id')
            ->take(5)
            ->get();

        $stats = [
            'total_titles' => Book::count(),
            'total_available' => (int) Book::sum('stock_available'),
            'total_members' => Member::count(),
            'total_borrowings' => Borrowing::count(),
        ];

        /** @var User|null $user */
        $user = Auth::user();
        $userFavoriteIds = [];
        $userActiveBorrowingsCount = 0;

        if ($user) {
            $userFavoriteIds = $user->favorites()->pluck('book_id')->toArray();
            if ($user->isAnggota()) {
                $userActiveBorrowingsCount = Borrowing::whereHas('member', function ($q) use ($user) {
                    $q->where('email', $user->email);
                })->where('status', Borrowing::STATUS_BORROWED)->count();
            }
        }

        return view('landing.index', [
            'featuredBooks' => $featuredBooks,
            'categories' => $categories,
            'ebooks' => $latestEbooks,
            'novels' => $latestNovels,
            'search' => $search,
            'categoryId' => $categoryId,
            'stats' => $stats,
            'user' => $user,
            'userFavoriteIds' => $userFavoriteIds,
            'userActiveBorrowingsCount' => $userActiveBorrowingsCount,
        ]);
    }
}
