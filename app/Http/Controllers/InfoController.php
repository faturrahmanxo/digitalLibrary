<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InfoController extends Controller
{
    /**
     * Tampilkan Halaman Profil & Sejarah Perpustakaan Kampus
     */
    public function profil(): View
    {
        $stats = [
            'total_titles' => Book::count(),
            'total_members' => Member::count(),
            'total_categories' => Category::count(),
            'total_borrowings' => Borrowing::count(),
        ];

        return view('info.profil', [
            'stats' => $stats,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Tampilkan Halaman Layanan Perpustakaan Kampus
     */
    public function layanan(): View
    {
        $stats = [
            'total_titles' => Book::count(),
            'total_available' => (int) Book::sum('stock_available'),
        ];

        return view('info.layanan', [
            'stats' => $stats,
            'user' => Auth::user(),
        ]);
    }
}
