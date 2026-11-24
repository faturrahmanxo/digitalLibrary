<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShelfRequest;
use App\Http\Requests\UpdateShelfRequest;
use App\Models\Shelf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShelfController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Shelf::class);

        $search = trim((string) $request->query('q', ''));

        $shelves = Shelf::query()
            ->withCount('books')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('shelves.index', [
            'shelves' => $shelves,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Shelf::class);

        return view('shelves.form', [
            'shelf' => new Shelf,
            'isEdit' => false,
        ]);
    }

    public function store(StoreShelfRequest $request): RedirectResponse
    {
        Gate::authorize('create', Shelf::class);

        Shelf::create($request->validated());

        return redirect()
            ->route('shelves.index')
            ->with('success', 'Rak buku berhasil ditambahkan.');
    }

    public function edit(Shelf $shelf): View
    {
        Gate::authorize('update', $shelf);

        return view('shelves.form', [
            'shelf' => $shelf,
            'isEdit' => true,
        ]);
    }

    public function update(UpdateShelfRequest $request, Shelf $shelf): RedirectResponse
    {
        Gate::authorize('update', $shelf);

        $shelf->update($request->validated());

        return redirect()
            ->route('shelves.index')
            ->with('success', 'Data rak buku berhasil diperbarui.');
    }

    public function destroy(Shelf $shelf): RedirectResponse
    {
        Gate::authorize('delete', $shelf);

        DB::transaction(function () use ($shelf) {
            /** @var Shelf $lockedShelf */
            $lockedShelf = Shelf::lockForUpdate()->findOrFail($shelf->id);

            if ($lockedShelf->books()->exists()) {
                throw ValidationException::withMessages([
                    'shelf' => 'Rak tidak dapat dihapus karena masih digunakan oleh buku fisik.',
                ]);
            }

            $lockedShelf->delete();
        });

        return redirect()
            ->route('shelves.index')
            ->with('success', 'Rak buku berhasil dihapus.');
    }
}
