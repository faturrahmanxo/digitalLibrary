<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublisherRequest;
use App\Http\Requests\UpdatePublisherRequest;
use App\Models\Publisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublisherController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Publisher::class);

        $search = trim((string) $request->query('q', ''));

        $publishers = Publisher::query()
            ->withCount('books')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('website', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('publishers.index', [
            'publishers' => $publishers,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Publisher::class);

        return view('publishers.form', [
            'publisher' => new Publisher,
            'isEdit' => false,
        ]);
    }

    public function store(StorePublisherRequest $request): RedirectResponse
    {
        Gate::authorize('create', Publisher::class);

        Publisher::create($request->validated());

        return redirect()
            ->route('publishers.index')
            ->with('success', 'Penerbit berhasil ditambahkan.');
    }

    public function edit(Publisher $publisher): View
    {
        Gate::authorize('update', $publisher);

        return view('publishers.form', [
            'publisher' => $publisher,
            'isEdit' => true,
        ]);
    }

    public function update(UpdatePublisherRequest $request, Publisher $publisher): RedirectResponse
    {
        Gate::authorize('update', $publisher);

        $publisher->update($request->validated());

        return redirect()
            ->route('publishers.index')
            ->with('success', 'Data penerbit berhasil diperbarui.');
    }

    public function destroy(Publisher $publisher): RedirectResponse
    {
        Gate::authorize('delete', $publisher);

        DB::transaction(function () use ($publisher) {
            /** @var Publisher $lockedPublisher */
            $lockedPublisher = Publisher::lockForUpdate()->findOrFail($publisher->id);

            if ($lockedPublisher->books()->exists()) {
                throw ValidationException::withMessages([
                    'publisher' => 'Penerbit tidak dapat dihapus karena masih terkait dengan buku.',
                ]);
            }

            $lockedPublisher->delete();
        });

        return redirect()
            ->route('publishers.index')
            ->with('success', 'Penerbit berhasil dihapus.');
    }
}
