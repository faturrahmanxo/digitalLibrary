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

        $activeBooksQuery = Book::query()
            ->with(['category', 'shelf', 'publisher'])
            ->where(function ($q) {
                $q->where('status', Book::STATUS_ACTIVE)
                    ->orWhereNull('status');
            });

        $featuredBooks = (clone $activeBooksQuery)
            ->latest('id')
            ->take(6)
            ->get();

        $categories = Category::withCount(['books' => function ($q) {
            $q->where('status', Book::STATUS_ACTIVE)->orWhereNull('status');
        }])
            ->orderBy('name')
            ->get();

        $latestPhysicalBooks = (clone $activeBooksQuery)
            ->where(function ($q) {
                $q->whereIn('collection_type', [Book::COLLECTION_TYPE_PHYSICAL, 'physical', 'fisik'])
                    ->orWhereNull('collection_type');
            })
            ->latest('id')
            ->take(5)
            ->get();

        $latestEbooks = (clone $activeBooksQuery)
            ->where(function ($q) {
                $q->whereIn('collection_type', [Book::COLLECTION_TYPE_EBOOK, 'e-book', 'ebook', 'novel'])
                    ->orWhere(function ($sub) {
                        $sub->whereNotIn('collection_type', [Book::COLLECTION_TYPE_PHYSICAL, 'physical', 'fisik'])
                            ->whereNotNull('collection_type');
                    });
            })
            ->latest('id')
            ->take(5)
            ->get();

        $latestNovels = (clone $activeBooksQuery)
            ->where(function ($q) {
                $q->where('collection_type', 'novel')
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', '%Novel%'));
            })
            ->latest('id')
            ->take(5)
            ->get();

        $stats = [
            'total_titles' => Book::where(fn ($q) => $q->where('status', Book::STATUS_ACTIVE)->orWhereNull('status'))->count(),
            'total_available' => (int) Book::sum('available_copies'),
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
                $userActiveBorrowingsCount = Borrowing::query()
                    ->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->orWhere('borrower_user_id', $user->id);
                        if ($user->email) {
                            $q->orWhereHas('member', fn ($mq) => $mq->where('email', $user->email));
                        }
                    })
                    ->where('status', Borrowing::STATUS_BORROWED)
                    ->count();
            }
        }

        return view('landing.index', [
            'featuredBooks' => $featuredBooks,
            'physicalBooks' => $latestPhysicalBooks,
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
