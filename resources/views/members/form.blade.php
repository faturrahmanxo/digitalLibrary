<x-layouts.app :title="$isEdit ? 'Edit Anggota' : 'Tambah Anggota'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('members.index') }}" class="hover:text-ink">Anggota</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ $isEdit ? 'Edit Anggota' : 'Tambah Anggota' }}</span>
    </x-slot:breadcrumbs>

    <!-- SCR-012: Member Create / Edit -->
    <div class="max-w-2xl">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                {{ $isEdit ? 'Edit Anggota' : 'Tambah Anggota' }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $isEdit ? 'Perbarui identitas dan informasi kontak anggota.' : 'Daftarkan anggota baru untuk keperluan pencatatan peminjaman buku.' }}
            </p>
        </div>

        <form method="POST" action="{{ $isEdit ? route('members.update', $member) : route('members.store') }}" class="bg-surface rounded-card border border-line p-6 space-y-5" novalidate>
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <p class="text-xs text-text-muted">Bidang dengan tanda <span class="text-danger-fg font-semibold">*</span> wajib diisi.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="member_code" class="block text-sm font-semibold text-ink mb-1.5">
                        Kode Anggota <span class="text-danger-fg" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="member_code"
                        name="member_code"
                        type="text"
                        required
                        value="{{ old('member_code', $member->member_code) }}"
                        aria-invalid="{{ $errors->has('member_code') ? 'true' : 'false' }}"
                        @if($errors->has('member_code')) aria-describedby="member-code-error" @endif
                        placeholder="AGT-00042"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('member_code') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm font-mono focus:outline-2 focus:outline-focus"
                    >
                    @error('member_code')
                        <p id="member-code-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-ink mb-1.5">
                        Nama Lengkap <span class="text-danger-fg" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        value="{{ old('name', $member->name) }}"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        @if($errors->has('name')) aria-describedby="name-error" @endif
                        placeholder="Contoh: Alya Pratama"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('name') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    @error('name')
                        <p id="name-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-ink mb-1.5">
                        Email (Opsional)
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $member->email) }}"
                        placeholder="nama@example.test"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('email') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-ink mb-1.5">
                        Nomor Telepon (Opsional)
                    </label>
                    <input
                        id="phone"
                        name="phone"
                        type="text"
                        value="{{ old('phone', $member->phone) }}"
                        placeholder="0812XXXXXXXX"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('phone') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    @error('phone')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-semibold text-ink mb-1.5">
                        Alamat (Opsional)
                    </label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        placeholder="Alamat domisili anggota..."
                        class="w-full px-3.5 py-2.5 rounded-control border {{ $errors->has('address') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >{{ old('address', $member->address) }}</textarea>
                    @error('address')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <a href="{{ $isEdit ? route('members.show', $member) : route('members.index') }}" class="min-h-11 inline-flex items-center justify-center px-4 py-2.5 rounded-control border border-line bg-surface text-sm font-semibold text-ink hover:bg-surface-soft">
                    Batal
                </a>
                <button type="submit" class="min-h-11 inline-flex items-center justify-center px-5 py-2.5 rounded-control bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 cursor-pointer">
                    Simpan Anggota
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
