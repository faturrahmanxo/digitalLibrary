<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DigitalBorrowingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NovelVolumeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShelfController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureActiveInternalUser;
use Illuminate\Support\Facades\Route;

// Public Campus Portal
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/jelajahi', [CatalogController::class, 'index'])->name('jelajahi');
Route::get('/katalog', fn () => redirect()->route('catalog.index'));
Route::get('/profil', [InfoController::class, 'profil'])->name('info.profil');
Route::get('/layanan', [InfoController::class, 'layanan'])->name('info.layanan');
Route::get('/books/{book}', [CatalogController::class, 'show'])
    ->whereNumber('book')
    ->name('books.show');

// Guest Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
});

// Authenticated Routes
Route::middleware(['auth', EnsureActiveInternalUser::class])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/books/{book}/read', [DigitalBorrowingController::class, 'readBook'])->name('books.read');
    Route::get('/books/{book}/download', [DigitalBorrowingController::class, 'downloadBook'])->name('books.digital.download');
    Route::get('/novel-volumes/{volume}/download', [DigitalBorrowingController::class, 'downloadVolume'])->name('novel-volumes.digital.download');
    Route::get('/books/{book}/manage', [BookController::class, 'show'])->name('books.manage');

    // Campus Member Actions: Favorites & Borrow
    Route::post('/books/{book}/favorite', [FavoriteController::class, 'toggle'])->name('books.favorite');
    Route::get('/koleksi-saya', [FavoriteController::class, 'index'])->name('collection.index');
    Route::get('/favorites', fn () => redirect()->route('collection.index', ['tab' => 'daftar-bacaan']))
        ->name('favorites.index');
    Route::get('/akun/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/akun/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/akun/profil/foto', [ProfileController::class, 'storePhoto'])->name('profile.photo.store');
    Route::delete('/akun/profil/foto', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::put('/akun/profil/password', [ProfileController::class, 'updatePassword'])
        ->middleware('throttle:5,1')
        ->name('profile.password.update');

    // Dedicated Account Settings Pages (Profil, Keamanan, Tampilan)
    Route::get('/pengaturan', fn () => redirect()->route('settings.profile'))->name('settings.index');
    Route::get('/pengaturan/profil', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::get('/pengaturan/keamanan', [SettingsController::class, 'security'])->name('settings.security');
    Route::get('/pengaturan/tampilan', [SettingsController::class, 'appearance'])->name('settings.appearance');
    Route::post('/books/{book}/borrow', [BorrowingController::class, 'borrowBook'])->name('books.borrow');
    Route::post('/books/{book}/digital-borrowing', [DigitalBorrowingController::class, 'borrowBook'])->name('books.digital.borrow');
    Route::post('/novel-volumes/{volume}/digital-borrowing', [DigitalBorrowingController::class, 'borrowVolume'])->name('novel-volumes.digital.borrow');

    // Internal Dashboard & Operations
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('shelves', ShelfController::class)->except(['show']);
    Route::resource('publishers', PublisherController::class)->except(['show']);
    Route::resource('books', BookController::class)->except(['show']);
    Route::get('/books/{book}/volumes', [NovelVolumeController::class, 'index'])->name('books.volumes.index');
    Route::get('/books/{book}/volumes/create', [NovelVolumeController::class, 'create'])->name('books.volumes.create');
    Route::post('/books/{book}/volumes', [NovelVolumeController::class, 'store'])->name('books.volumes.store');
    Route::get('/books/{book}/volumes/{volume}/edit', [NovelVolumeController::class, 'edit'])->whereNumber('volume')->name('books.volumes.edit');
    Route::put('/books/{book}/volumes/{volume}', [NovelVolumeController::class, 'update'])->whereNumber('volume')->name('books.volumes.update');
    Route::delete('/books/{book}/volumes/{volume}', [NovelVolumeController::class, 'destroy'])->whereNumber('volume')->name('books.volumes.destroy');
    Route::post('/members/{id}/restore', [MemberController::class, 'restore'])->name('members.restore');
    Route::resource('members', MemberController::class)->withTrashed(['show', 'edit', 'update']);

    Route::get('/borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');
    Route::get('/borrowings/create', [BorrowingController::class, 'create'])->name('borrowings.create');
    Route::post('/borrowings', [BorrowingController::class, 'store'])->name('borrowings.store');
    Route::get('/borrowings/{borrowing}', [BorrowingController::class, 'show'])->name('borrowings.show');
    Route::post('/borrowings/{borrowing}/approve', [BorrowingController::class, 'approve'])->name('borrowings.approve');
    Route::post('/borrowings/{borrowing}/handover', [BorrowingController::class, 'handover'])->name('borrowings.handover');
    Route::post('/borrowings/{borrowing}/reject', [BorrowingController::class, 'reject'])->name('borrowings.reject');
    Route::post('/borrowings/{borrowing}/cancel', [BorrowingController::class, 'cancel'])->name('borrowings.cancel');
    Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnBorrowing'])->name('borrowings.return');
});
