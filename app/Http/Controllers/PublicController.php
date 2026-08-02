<?php

namespace App\Http\Controllers;

use App\Models\Expo;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $expo     = Expo::active();
        $sponsors = $expo?->sponsors()->orderBy('tier')->get();
        $guest    = $expo?->guestOfHonor;
        $archives = Expo::where('is_active', false)
            ->with('guestOfHonor')
            ->orderByDesc('year')
            ->limit(5)
            ->get();

        return view('public.home', compact('expo', 'sponsors', 'guest', 'archives'));
    }

    public function floorPlan(): View
    {
        $expo = Expo::active();
        return view('public.floor-plan', compact('expo'));
    }

    public function about(): View
    {
        $expo = Expo::active();
        return view('public.about', compact('expo'));
    }
}
