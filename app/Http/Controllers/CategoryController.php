<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Category::class);

        $search = trim((string) $request->query('q', ''));
        $genre = trim((string) ($request->query('genre') ?: $request->query('collection_type', '')));

        $categories = Category::query()
            ->withCount('books')
            ->when($genre !== '', function ($query) use ($genre) {
                $query->where(function ($sub) use ($genre) {
                    $sub->whereHas('books', function ($bq) use ($genre) {
                        $bq->where('collection_type', $genre);
                    })
                    ->orWhere('description', 'like', "%{$genre}%")
                    ->orWhere('name', 'like', "%{$genre}%");
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
            'search' => $search,
            'genre' => $genre,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Category::class);

        return view('categories.form', [
            'category' => new Category(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Gate::authorize('create', Category::class);

        Category::create($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        return view('categories.form', [
            'category' => $category,
            'isEdit' => true,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        DB::transaction(function () use ($category) {
            /** @var Category $lockedCategory */
            $lockedCategory = Category::withTrashed()->lockForUpdate()->findOrFail($category->id);

            if ($lockedCategory->books()->exists()) {
                throw ValidationException::withMessages([
                    'archive' => 'Kategori tidak dapat diarsipkan karena masih digunakan oleh buku aktif.',
                ]);
            }

            $lockedCategory->delete();
        });

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diarsipkan.');
    }
}
