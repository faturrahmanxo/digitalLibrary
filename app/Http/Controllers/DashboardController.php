<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Member;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // FR-008 & ADR-009: Four authoritative KPIs
        // 1. Jumlah Judul Buku: COUNT(active books)
        $totalBookTitles = (int) Book::count();

        // 2. Stok Buku Tersedia: SUM(stock_available) of active books
        $totalAvailableStock = (int) Book::sum('stock_available');

        // 3. Jumlah Anggota Aktif: COUNT(active members)
        $totalActiveMembers = (int) Member::count();

        // 4. Buku Sedang Dipinjam: SUM(quantity) of items in borrowings with status = 'borrowed'
        $totalBorrowedCopies = (int) BorrowingItem::query()
            ->whereHas('borrowing', fn ($q) => $q->where('status', Borrowing::STATUS_BORROWED))
            ->sum('quantity');

        $user = auth()->user();
        $isAnggota = $user?->isAnggota();

        $recentBorrowings = Borrowing::with(['member', 'staff', 'items.book'])
            ->when($isAnggota, function ($q) use ($user) {
                $q->whereHas('member', fn ($m) => $m->where('email', $user->email));
            })
            ->latest('borrowed_at')
            ->limit(5)
            ->get();

        $member = null;
        $memberActiveCount = 0;
        $memberHistoryCount = 0;
        $activeBorrowings = collect();
        $featuredBooks = collect();
        $favoriteBooks = collect();
        $userFavoritesCount = 0;
        $nextDueBorrowing = null;

        if ($isAnggota && $user) {
            $member = Member::where('email', $user->email)->first();
            if ($member) {
                $memberActiveCount = (int) BorrowingItem::whereHas('borrowing', function ($q) use ($member) {
                    $q->where('member_id', $member->id)
                      ->where('status', Borrowing::STATUS_BORROWED);
                })->sum('quantity');

                $memberHistoryCount = (int) Borrowing::where('member_id', $member->id)
                    ->where('status', Borrowing::STATUS_RETURNED)
                    ->count();

                $activeBorrowings = Borrowing::with(['items.book', 'member'])
                    ->where('member_id', $member->id)
                    ->where('status', Borrowing::STATUS_BORROWED)
                    ->latest('borrowed_at')
                    ->get();

                $nextDueBorrowing = $activeBorrowings->sortBy('due_at')->first();
            }

            $userFavoritesCount = $user->favorites()->count();
            $favoriteBooks = $user->favoriteBooks()->with('category')->latest('favorites.created_at')->take(4)->get();

            $featuredBooks = Book::with('category')
                ->latest('id')
                ->limit(8)
                ->get();
        }

        $availableBooks = Book::with('category')->where('stock_available', '>', 0)->orderBy('title')->get();
        $allMembers = Member::orderBy('name')->get();
        $categories = Category::withCount('books')->orderBy('name')->get();

        // 2 Core Genre Tracking (Novel & Fiksi vs Keilmuan & Edukasi)
        $novelCategoryFilter = fn ($q) => $q->where('name', 'like', '%Novel%')->orWhere('name', 'like', '%Sastra%');
        $keilmuanCategoryFilter = fn ($q) => $q->where('name', 'like', '%Keilmuan%')->orWhere('name', 'like', '%Sains%')->orWhere('name', 'like', '%Teknologi%');

        $novelTitles = Book::whereHas('category', $novelCategoryFilter)->count();
        $novelTotalStock = (int) Book::whereHas('category', $novelCategoryFilter)->sum('stock_total');
        $novelAvailableStock = (int) Book::whereHas('category', $novelCategoryFilter)->sum('stock_available');
        $novelBorrowed = (int) BorrowingItem::whereHas('book.category', $novelCategoryFilter)
            ->whereHas('borrowing', fn ($b) => $b->where('status', Borrowing::STATUS_BORROWED))
            ->sum('quantity');
        $novelBooks = Book::with('category')->whereHas('category', $novelCategoryFilter)->latest('id')->take(3)->get();

        $keilmuanTitles = Book::whereHas('category', $keilmuanCategoryFilter)->count();
        $keilmuanTotalStock = (int) Book::whereHas('category', $keilmuanCategoryFilter)->sum('stock_total');
        $keilmuanAvailableStock = (int) Book::whereHas('category', $keilmuanCategoryFilter)->sum('stock_available');
        $keilmuanBorrowed = (int) BorrowingItem::whereHas('book.category', $keilmuanCategoryFilter)
            ->whereHas('borrowing', fn ($b) => $b->where('status', Borrowing::STATUS_BORROWED))
            ->sum('quantity');
        $keilmuanBooks = Book::with('category')->whereHas('category', $keilmuanCategoryFilter)->latest('id')->take(3)->get();

        $genreTracking = [
            'novel' => [
                'name' => 'Novel & Fiksi',
                'genre_param' => 'novel-fiksi',
                'badge' => 'Fiksi & Sastra',
                'description' => 'Koleksi novel naratif, sastra klasik, fiksi modern, dan prosa kontemporer.',
                'titles' => $novelTitles,
                'total_stock' => $novelTotalStock,
                'available' => $novelAvailableStock,
                'borrowed' => $novelBorrowed,
                'rate' => $novelTotalStock > 0 ? round(($novelAvailableStock / $novelTotalStock) * 100) : 0,
                'books' => $novelBooks,
            ],
            'keilmuan' => [
                'name' => 'Keilmuan & Edukasi',
                'genre_param' => 'keilmuan-edukasi',
                'badge' => 'Riset & Akademik',
                'description' => 'Literatur akademik, rekayasa teknologi, sains murni, dan materi pendidikan.',
                'titles' => $keilmuanTitles,
                'total_stock' => $keilmuanTotalStock,
                'available' => $keilmuanAvailableStock,
                'borrowed' => $keilmuanBorrowed,
                'rate' => $keilmuanTotalStock > 0 ? round(($keilmuanAvailableStock / $keilmuanTotalStock) * 100) : 0,
                'books' => $keilmuanBooks,
            ],
        ];

        return view('dashboard.index', [
            'totalBookTitles' => $totalBookTitles,
            'totalAvailableStock' => $totalAvailableStock,
            'totalActiveMembers' => $totalActiveMembers,
            'totalBorrowedCopies' => $totalBorrowedCopies,
            'recentBorrowings' => $recentBorrowings,
            'member' => $member,
            'memberActiveCount' => $memberActiveCount,
            'memberHistoryCount' => $memberHistoryCount,
            'activeBorrowings' => $activeBorrowings,
            'nextDueBorrowing' => $nextDueBorrowing,
            'favoriteBooks' => $favoriteBooks,
            'userFavoritesCount' => $userFavoritesCount,
            'featuredBooks' => $featuredBooks,
            'availableBooks' => $availableBooks,
            'allMembers' => $allMembers,
            'categories' => $categories,
            'genreTracking' => $genreTracking,
            'isAnggota' => (bool) $isAnggota,
        ]);
    }
}
