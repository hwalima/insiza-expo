<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expo;
use App\Models\Stand;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $expo = Expo::active();

        $stats = [
            'total_stands'     => Stand::where('expo_id', $expo?->id)->count(),
            'available_stands' => Stand::where('expo_id', $expo?->id)->where('status', 'available')->count(),
            'reserved_stands'  => Stand::where('expo_id', $expo?->id)->where('status', 'reserved')->count(),
            'occupied_stands'  => Stand::where('expo_id', $expo?->id)->where('status', 'occupied')->count(),
            'pending_bookings' => Booking::where('expo_id', $expo?->id)->where('status', 'pending')->count(),
            'total_bookings'   => Booking::where('expo_id', $expo?->id)->count(),
        ];

        return view('admin.dashboard', compact('expo', 'stats'));
    }

    public function expoEditor(): View
    {
        return view('admin.expo-editor');
    }

    public function expoCreate(): View
    {
        return view('admin.expo-editor'); // reuses the same component; mount with no expo
    }

    public function floorPlan(): View
    {
        $expo = Expo::active();
        return view('admin.floor-plan', compact('expo'));
    }

    public function bookings(): View
    {
        $expo = Expo::active();
        return view('admin.bookings', compact('expo'));
    }
}
