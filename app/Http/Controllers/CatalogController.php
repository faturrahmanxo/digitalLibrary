<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $author = trim((string) $request->query('author', ''));
        $isbn = trim((string) $request->query('isbn', ''));
        $publisher = trim((string) $request->query('publisher', ''));
        $year = $request->query('year');
        $categoryId = $request->query('category_id');
        $genre = trim((string) $request->query('genre', ''));
        $availability = trim((string) $request->query('availability', ''));
        $sort = trim((string) $request->query('sort', 'newest'));

        $query = Book::query()->with('category');

        // Genre query filter support
        if ($genre !== '') {
            $genreMap = [
                'novel-fiksi' => ['Sastra & Fiksi'],
                'keilmuan-edukasi' => ['Sains & Matematika', 'Keilmuan & Edukasi', 'Literasi Digital'],
                'sejarah-budaya' => ['Sejarah'],
                'sastra-klasik' => ['Sastra & Fiksi'],
                'teknologi-pemrograman' => ['Teknologi & Pemrograman'],
                'ekonomi-manajemen' => ['Ekonomi & Manajemen'],
            ];

            if (isset($genreMap[$genre])) {
                $categoryNames = $genreMap[$genre];
                $query->whereHas('category', function ($categoryQuery) use ($categoryNames) {
                    $categoryQuery->where(function ($matches) use ($categoryNames) {
                        foreach ($categoryNames as $categoryName) {
                            $matches->orWhere('name', 'like', "%{$categoryName}%");
                        }
                    });
                });
            } else {
                $slugSearch = str_replace('-', ' ', $genre);
                $query->where(function ($sub) use ($slugSearch) {
                    $sub->whereHas('category', fn ($c) => $c->where('name', 'like', "%{$slugSearch}%"))
                        ->orWhere('title', 'like', "%{$slugSearch}%");
                });
            }
        }

        // Global search term
        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        // Specific field filters (Gunadarma-style advanced filters)
        if ($author !== '') {
            $query->where('author', 'like', "%{$author}%");
        }

        if ($isbn !== '') {
            $query->where('isbn', 'like', "%{$isbn}%");
        }

        if ($publisher !== '') {
            $query->where('publisher', 'like', "%{$publisher}%");
        }

        if ($year !== null && $year !== '') {
            $query->where('publication_year', (int) $year);
        }

        if ($categoryId !== null && $categoryId !== '') {
            $query->where('category_id', (int) $categoryId);
        }

        if ($availability === 'available') {
            $query->where('stock_available', '>', 0);
        } elseif ($availability === 'empty') {
            $query->where('stock_available', '=', 0);
        }

        if ($sort === 'title_asc') {
            $query->orderBy('title', 'asc');
        } elseif ($sort === 'title_desc') {
            $query->orderBy('title', 'desc');
        } elseif ($sort === 'popular') {
            $query->orderBy('stock_total', 'desc');
        } elseif ($sort === 'year_desc') {
            $query->orderBy('publication_year', 'desc');
        } else {
            $query->latest('id');
        }

        $books = $query->paginate(15)->withQueryString();

        $categories = Category::withCount('books')->orderBy('name')->get();

        $years = Book::select('publication_year')
            ->distinct()
            ->whereNotNull('publication_year')
            ->orderByDesc('publication_year')
            ->pluck('publication_year');

        $userFavoriteIds = [];
        if (Auth::check()) {
            $userFavoriteIds = Favorite::where('user_id', Auth::id())
                ->pluck('book_id')
                ->toArray();
        }

        return view('catalog.index', [
            'books' => $books,
            'categories' => $categories,
            'years' => $years,
            'search' => $search,
            'author' => $author,
            'isbn' => $isbn,
            'publisher' => $publisher,
            'year' => $year ? (int) $year : null,
            'categoryId' => $categoryId ? (int) $categoryId : null,
            'availability' => $availability,
            'sort' => $sort,
            'userFavoriteIds' => $userFavoriteIds,
        ]);
    }
}
