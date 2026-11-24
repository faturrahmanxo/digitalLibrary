<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isAnggota = (bool) $user?->isAnggota();
        $isAdmin = (bool) $user?->isAdmin();
        $isPetugas = (bool) $user?->isPetugas();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // -------------------------------------------------------------
        // SECTION A: MAIN SUMMARY (ADMIN & GENERAL KPIS)
        // -------------------------------------------------------------
        // 1. Total Judul Buku (COUNT of active book records, not copies)
        $totalBookTitles = (int) Book::count();

        // Physical Books vs E-books Titles
        $physicalBooksQuery = Book::query()->where(function (Builder $q) {
            $q->whereIn('collection_type', [Book::COLLECTION_TYPE_PHYSICAL, 'fisik'])
                ->orWhere('stock_total', '>', 0)
                ->orWhere('total_copies', '>', 0);
        });

        $ebookBooksQuery = Book::query()->where(function (Builder $q) {
            $q->whereIn('collection_type', [Book::COLLECTION_TYPE_EBOOK, 'ebook', 'digital'])
                ->where(function (Builder $sq) {
                    $sq->whereNull('stock_total')->orWhere('stock_total', '<=', 0);
                });
        });

        $totalPhysicalTitles = (int) (clone $physicalBooksQuery)->count();
        $totalEbookTitles = (int) (clone $ebookBooksQuery)->count();

        // If all books are digital or physical, ensure counts don't exceed total titles
        if ($totalPhysicalTitles + $totalEbookTitles < $totalBookTitles) {
            $totalPhysicalTitles = $totalBookTitles - $totalEbookTitles;
        }

        // 4. Total Anggota (Total active registered members)
        $totalActiveMembers = (int) Member::count();

        // 5. Peminjaman Aktif (Active loans: status = borrowed, returned_at IS NULL)
        $activeLoansCount = (int) Borrowing::query()
            ->where('status', Borrowing::STATUS_BORROWED)
            ->whereNull('returned_at')
            ->count();

        // Total Borrowed Copies (sum of quantities across active borrowings for test contract TEST-013)
        $totalBorrowedCopies = (int) BorrowingItem::query()
            ->whereHas('borrowing', fn ($q) => $q->where('status', Borrowing::STATUS_BORROWED)->whereNull('returned_at'))
            ->sum('quantity');

        if ($totalBorrowedCopies === 0 && $activeLoansCount > 0) {
            $totalBorrowedCopies = $activeLoansCount;
        }

        // 6. Peminjaman Terlambat (Overdue loans: borrowed and due_date < today)
        $overdueLoansCount = (int) Borrowing::query()
            ->where('status', Borrowing::STATUS_BORROWED)
            ->whereNull('returned_at')
            ->where(function (Builder $q) use ($today) {
                $q->whereDate('due_at', '<', $today)
                    ->orWhere(function (Builder $sq) use ($today) {
                        $sq->whereNull('due_at')->whereDate('due_date', '<', $today);
                    });
            })
            ->count();

        // -------------------------------------------------------------
        // SECTION B: INVENTORY SUMMARY (PHYSICAL BOOKS ONLY)
        // -------------------------------------------------------------
        $totalPhysicalCopies = (int) Book::sum('stock_total');
        if ($totalPhysicalCopies === 0 && Schema::hasColumn('books', 'total_copies')) {
            $totalPhysicalCopies = (int) Book::sum('total_copies');
        }

        $totalAvailableStock = (int) Book::sum('stock_available');
        if ($totalAvailableStock === 0 && Schema::hasColumn('books', 'available_copies')) {
            $totalAvailableStock = (int) Book::sum('available_copies');
        }

        $totalReservedStock = 0;
        if (Schema::hasColumn('books', 'reserved_copies')) {
            $totalReservedStock = (int) (clone $physicalBooksQuery)->sum('reserved_copies');
        }
        $approvedReservations = (int) Borrowing::query()
            ->where('status', Borrowing::STATUS_APPROVED)
            ->count();
        $totalReservedStock = max($totalReservedStock, $approvedReservations);

        $totalBorrowedStock = 0;
        if (Schema::hasColumn('books', 'borrowed_copies')) {
            $totalBorrowedStock = (int) (clone $physicalBooksQuery)->sum('borrowed_copies');
        }
        if ($totalBorrowedStock === 0) {
            $totalBorrowedStock = $totalBorrowedCopies;
        }

        $damagedLostStock = 0;

        $outOfStockPhysicalTitles = (int) (clone $physicalBooksQuery)
            ->where(function (Builder $q) {
                $q->where('stock_available', '<=', 0)
                    ->orWhere('stock_total', '<=', 0);
            })
            ->count();

        // -------------------------------------------------------------
        // SECTION C: BORROWING TREND (7 DAYS, 30 DAYS, 12 MONTHS)
        // -------------------------------------------------------------
        $trendData = $this->calculateBorrowingTrends();

        // -------------------------------------------------------------
        // SECTION D: COLLECTION DISTRIBUTION
        // -------------------------------------------------------------
        $collectionDistribution = [
            'physical' => $totalPhysicalTitles,
            'ebook' => $totalEbookTitles,
            'total' => $totalBookTitles,
            'physical_percent' => $totalBookTitles > 0 ? round(($totalPhysicalTitles / $totalBookTitles) * 100, 1) : 0,
            'ebook_percent' => $totalBookTitles > 0 ? round(($totalEbookTitles / $totalBookTitles) * 100, 1) : 0,
        ];

        // -------------------------------------------------------------
        // SECTION E: CATEGORY DISTRIBUTION
        // -------------------------------------------------------------
        $categories = Category::query()->withCount('books')->orderBy('name')->get();
        $categoryDistribution = $categories->sortByDesc('books_count')->values()->map(function ($category) use ($totalBookTitles) {
            $count = (int) $category->books_count;
            $percent = $totalBookTitles > 0 ? round(($count / $totalBookTitles) * 100, 1) : 0;

            return [
                'id' => $category->id,
                'name' => $category->name,
                'count' => $count,
                'percent' => $percent,
            ];
        });

        // -------------------------------------------------------------
        // SECTION F: BORROWING STATUS BREAKDOWN
        // -------------------------------------------------------------
        $pendingCount = (int) Borrowing::where('status', Borrowing::STATUS_PENDING)->count();
        $awaitingPickupCount = (int) Borrowing::where('status', Borrowing::STATUS_APPROVED)->count();
        $activeOnTimeCount = max(0, $activeLoansCount - $overdueLoansCount);
        $returnedCount = (int) Borrowing::where('status', Borrowing::STATUS_RETURNED)->count();
        $rejectedCount = (int) Borrowing::whereIn('status', [Borrowing::STATUS_REJECTED, Borrowing::STATUS_CANCELLED])->count();
        $returnsTodayCount = (int) Borrowing::query()
            ->where('status', Borrowing::STATUS_RETURNED)
            ->whereDate('returned_at', $today)
            ->count();

        $statusBreakdown = [
            'pending' => $pendingCount,
            'approved' => $awaitingPickupCount,
            'active_on_time' => $activeOnTimeCount,
            'overdue' => $overdueLoansCount,
            'active_total' => $activeLoansCount,
            'returned' => $returnedCount,
            'rejected' => $rejectedCount,
        ];

        // -------------------------------------------------------------
        // SECTION G: POPULAR BOOKS (TOP 5 MOST BORROWED PHYSICAL BOOKS)
        // -------------------------------------------------------------
        $popularBooks = (clone $physicalBooksQuery)
            ->with('category')
            ->withCount([
                'borrowingItems as item_borrow_count' => function ($query) {
                    $query->whereHas('borrowing', function ($b) {
                        $b->whereIn('status', [Borrowing::STATUS_BORROWED, Borrowing::STATUS_RETURNED]);
                    });
                },
                'borrowings as direct_borrow_count' => function ($query) {
                    $query->whereIn('status', [Borrowing::STATUS_BORROWED, Borrowing::STATUS_RETURNED]);
                },
            ])
            ->get()
            ->map(function ($book) {
                $book->total_borrow_count = (int) $book->item_borrow_count + (int) $book->direct_borrow_count;

                return $book;
            })
            ->sortByDesc('total_borrow_count')
            ->take(5)
            ->values();

        // -------------------------------------------------------------
        // SECTION H: RECENT TRANSACTIONS (5-10 LATEST)
        // -------------------------------------------------------------
        $recentTransactions = Borrowing::query()
            ->with(['member', 'staff', 'items.book', 'book.category', 'borrowerUser'])
            ->latest('id')
            ->limit(10)
            ->get();

        $recentBorrowings = $recentTransactions->take(5);

        // -------------------------------------------------------------
        // SECTION I & SECTION 26: ATTENTION REQUIRED & OPERATIONAL
        // -------------------------------------------------------------
        $attentionRequired = [
            'pending' => $pendingCount,
            'awaiting_pickup' => $awaitingPickupCount,
            'overdue' => $overdueLoansCount,
            'out_of_stock' => $outOfStockPhysicalTitles,
            'has_attention' => ($pendingCount + $awaitingPickupCount + $overdueLoansCount + $outOfStockPhysicalTitles) > 0,
        ];

        // -------------------------------------------------------------
        // SIVITAS AKADEMIKA / MEMBER SPECIFIC DATA
        // -------------------------------------------------------------
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

                if ($memberActiveCount === 0) {
                    $memberActiveCount = (int) Borrowing::where('member_id', $member->id)
                        ->where('status', Borrowing::STATUS_BORROWED)
                        ->count();
                }

                $memberHistoryCount = (int) Borrowing::where('member_id', $member->id)
                    ->where('status', Borrowing::STATUS_RETURNED)
                    ->count();

                $activeBorrowings = Borrowing::with(['items.book', 'book', 'member'])
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

        // 2 Core Genre Tracking
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
            // Backward-compatible original KPIs (TEST-013)
            'totalBookTitles' => $totalBookTitles,
            'totalAvailableStock' => $totalAvailableStock,
            'totalActiveMembers' => $totalActiveMembers,
            'totalBorrowedCopies' => $totalBorrowedCopies,
            'recentBorrowings' => $recentBorrowings,

            // Section A: Main Summary
            'totalPhysicalTitles' => $totalPhysicalTitles,
            'totalEbookTitles' => $totalEbookTitles,
            'activeLoansCount' => $activeLoansCount,
            'overdueLoansCount' => $overdueLoansCount,

            // Section B: Inventory Summary
            'totalPhysicalCopies' => $totalPhysicalCopies,
            'totalReservedStock' => $totalReservedStock,
            'totalBorrowedStock' => $totalBorrowedStock,
            'damagedLostStock' => $damagedLostStock,
            'outOfStockPhysicalTitles' => $outOfStockPhysicalTitles,

            // Section C: Borrowing Trend
            'trendData' => $trendData,

            // Section D: Collection Distribution
            'collectionDistribution' => $collectionDistribution,

            // Section E: Category Distribution
            'categoryDistribution' => $categoryDistribution,

            // Section F: Borrowing Status Breakdown
            'statusBreakdown' => $statusBreakdown,

            // Section G: Popular Books
            'popularBooks' => $popularBooks,

            // Section H: Recent Transactions
            'recentTransactions' => $recentTransactions,

            // Section I & Section 26: Attention & Operational Focus
            'pendingCount' => $pendingCount,
            'awaitingPickupCount' => $awaitingPickupCount,
            'returnsTodayCount' => $returnsTodayCount,
            'attentionRequired' => $attentionRequired,

            // Role and Sivitas Akademika variables
            'isAnggota' => $isAnggota,
            'isAdmin' => $isAdmin,
            'isPetugas' => $isPetugas,
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
        ]);
    }

    /**
     * Calculate 7-day, 30-day, and 12-month borrowing & return trends with zero-filled gaps.
     *
     * @return array<string, array{labels: array<int, string>, borrowed: array<int, int>, returned: array<int, int>}>
     */
    private function calculateBorrowingTrends(): array
    {
        $nowJakarta = Carbon::now('Asia/Jakarta');
        $driver = DB::connection()->getDriverName();

        $sqlDayBorrow = $driver === 'sqlite' ? "strftime('%Y-%m-%d', borrowed_at)" : 'DATE(borrowed_at)';
        $sqlDayReturn = $driver === 'sqlite' ? "strftime('%Y-%m-%d', returned_at)" : 'DATE(returned_at)';
        $sqlMonthBorrow = $driver === 'sqlite' ? "strftime('%Y-%m', borrowed_at)" : "DATE_FORMAT(borrowed_at, '%Y-%m')";
        $sqlMonthReturn = $driver === 'sqlite' ? "strftime('%Y-%m', returned_at)" : "DATE_FORMAT(returned_at, '%Y-%m')";

        // 1. Pre-aggregate last 30 days daily data
        $start30Days = (clone $nowJakarta)->subDays(29)->startOfDay();

        $borrowingsDaily = Borrowing::query()
            ->selectRaw("{$sqlDayBorrow} as date_val, COUNT(*) as aggregate_count")
            ->whereNotNull('borrowed_at')
            ->whereIn('status', [Borrowing::STATUS_BORROWED, Borrowing::STATUS_RETURNED])
            ->where('borrowed_at', '>=', $start30Days)
            ->groupBy('date_val')
            ->pluck('aggregate_count', 'date_val')
            ->all();

        $returnsDaily = Borrowing::query()
            ->selectRaw("{$sqlDayReturn} as date_val, COUNT(*) as aggregate_count")
            ->whereNotNull('returned_at')
            ->where('status', Borrowing::STATUS_RETURNED)
            ->where('returned_at', '>=', $start30Days)
            ->groupBy('date_val')
            ->pluck('aggregate_count', 'date_val')
            ->all();

        // 2. Pre-aggregate last 12 months monthly data
        $start12Months = (clone $nowJakarta)->subMonths(11)->startOfMonth();

        $borrowingsMonthly = Borrowing::query()
            ->selectRaw("{$sqlMonthBorrow} as ym_val, COUNT(*) as aggregate_count")
            ->whereNotNull('borrowed_at')
            ->whereIn('status', [Borrowing::STATUS_BORROWED, Borrowing::STATUS_RETURNED])
            ->where('borrowed_at', '>=', $start12Months)
            ->groupBy('ym_val')
            ->pluck('aggregate_count', 'ym_val')
            ->all();

        $returnsMonthly = Borrowing::query()
            ->selectRaw("{$sqlMonthReturn} as ym_val, COUNT(*) as aggregate_count")
            ->whereNotNull('returned_at')
            ->where('status', Borrowing::STATUS_RETURNED)
            ->where('returned_at', '>=', $start12Months)
            ->groupBy('ym_val')
            ->pluck('aggregate_count', 'ym_val')
            ->all();

        // Build 7-day dataset (last 7 days)
        $labels7d = [];
        $borrowed7d = [];
        $returned7d = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = (clone $nowJakarta)->subDays($i);
            $dayKey = $day->toDateString();
            $labels7d[] = $day->translatedFormat('d M');
            $borrowed7d[] = (int) ($borrowingsDaily[$dayKey] ?? 0);
            $returned7d[] = (int) ($returnsDaily[$dayKey] ?? 0);
        }

        // Build 30-day dataset (last 30 days)
        $labels30d = [];
        $borrowed30d = [];
        $returned30d = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = (clone $nowJakarta)->subDays($i);
            $dayKey = $day->toDateString();
            $labels30d[] = $day->translatedFormat('d M');
            $borrowed30d[] = (int) ($borrowingsDaily[$dayKey] ?? 0);
            $returned30d[] = (int) ($returnsDaily[$dayKey] ?? 0);
        }

        // Build 12-month dataset (last 12 months)
        $labels12m = [];
        $borrowed12m = [];
        $returned12m = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = (clone $nowJakarta)->subMonths($i);
            $monthKey = $month->format('Y-m');
            $labels12m[] = $month->translatedFormat('M Y');
            $borrowed12m[] = (int) ($borrowingsMonthly[$monthKey] ?? 0);
            $returned12m[] = (int) ($returnsMonthly[$monthKey] ?? 0);
        }

        return [
            '7d' => [
                'labels' => $labels7d,
                'borrowed' => $borrowed7d,
                'returned' => $returned7d,
            ],
            '30d' => [
                'labels' => $labels30d,
                'borrowed' => $borrowed30d,
                'returned' => $returned30d,
            ],
            '12m' => [
                'labels' => $labels12m,
                'borrowed' => $borrowed12m,
                'returned' => $returned12m,
            ],
        ];
    }
}
