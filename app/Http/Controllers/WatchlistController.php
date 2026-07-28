<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Watchlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function index(): View
    {
        $watchlists = Watchlist::with('country')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('watchlists.index', compact('watchlists'));
    }

    public function toggle(string $code, Request $request): RedirectResponse
    {
        $country = Country::query()->findOrFail(strtoupper($code));
        $watchlist = Watchlist::query()
            ->where('user_id', $request->user()->id)
            ->where('country_code', $country->code)
            ->first();

        if ($watchlist) {
            $watchlist->delete();
            $message = $country->name.' dihapus dari Favorite Monitoring.';
        } else {
            Watchlist::create([
                'user_id' => $request->user()->id,
                'country_code' => $country->code,
            ]);
            $message = $country->name.' ditambahkan ke Favorite Monitoring.';
        }

        return redirect()->back()->with('watchlist_message', $message);
    }

    public function destroy(string $code): RedirectResponse
    {
        $country = Country::query()->findOrFail(strtoupper($code));
        Watchlist::query()
            ->where('user_id', auth()->id())
            ->where('country_code', $country->code)
            ->delete();

        return redirect()->route('watchlists.index')->with('watchlist_message', $country->name.' dihapus dari monitoring.');
    }
}
