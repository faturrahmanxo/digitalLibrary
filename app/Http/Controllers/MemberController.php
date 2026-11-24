<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Member::class);

        $search = trim((string) $request->query('q', ''));

        $members = Member::query()
            ->withCount('activeBorrowings')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('member_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('members.index', [
            'members' => $members,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Member::class);

        $nextNumber = Member::withTrashed()->count() + 1;
        $suggestedCode = sprintf('AGT-%05d', $nextNumber);

        return view('members.form', [
            'member' => new Member(['member_code' => $suggestedCode]),
            'isEdit' => false,
        ]);
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        Gate::authorize('create', Member::class);

        $member = Member::create($request->validated());

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Data anggota berhasil ditambahkan.');
    }

    public function show(Member $member): View
    {
        Gate::authorize('view', $member);

        $member->load([
            'borrowings' => fn ($query) => $query->with(['staff', 'items.book'])->latest('borrowed_at'),
        ]);

        return view('members.show', [
            'member' => $member,
        ]);
    }

    public function edit(Member $member): View
    {
        Gate::authorize('update', $member);

        return view('members.form', [
            'member' => $member,
            'isEdit' => true,
        ]);
    }

    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        Gate::authorize('update', $member);

        $member->update($request->validated());

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        Gate::authorize('delete', $member);

        DB::transaction(function () use ($member) {
            /** @var Member $lockedMember */
            $lockedMember = Member::withTrashed()->lockForUpdate()->findOrFail($member->id);

            if ($lockedMember->activeBorrowings()->exists()) {
                throw ValidationException::withMessages([
                    'archive' => 'Anggota tidak dapat diarsipkan karena masih memiliki peminjaman aktif.',
                ]);
            }

            $lockedMember->delete();
        });

        return redirect()
            ->route('members.index')
            ->with('success', 'Anggota berhasil diarsipkan.');
    }
}
