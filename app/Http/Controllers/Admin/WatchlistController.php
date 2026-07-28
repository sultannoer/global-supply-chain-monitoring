<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $watchlists = Watchlist::with(['user:id,name,email', 'country:code,name,region'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('country', function ($countryQuery) use ($search) {
                    $countryQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.watchlists.index', compact('watchlists', 'search'));
    }

    public function destroy(Watchlist $watchlist): RedirectResponse
    {
        $watchlist->delete();

        return redirect()->route('admin.watchlists.index')
            ->with('success', 'Negara berhasil dihapus dari monitoring user.');
    }
}
