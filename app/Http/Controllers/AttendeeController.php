<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Expo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class AttendeeController extends Controller
{
    public function showForm(): View
    {
        $expo = Expo::where('is_active', true)->first();
        return view('public.attend', compact('expo'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:120',
            'organisation' => 'nullable|string|max:120',
            'email'        => 'nullable|email|max:120',
            'phone'        => 'required|string|max:30',
        ]);

        $expo = Expo::where('is_active', true)->firstOrFail();

        $attendee = Attendee::create([
            ...$data,
            'expo_id'             => $expo->id,
            'registration_number' => Attendee::generateRegNumber($expo->year),
        ]);

        return redirect()->route('attend.success', $attendee->registration_number);
    }

    public function success(string $code): View
    {
        $attendee = Attendee::where('registration_number', $code)->firstOrFail();
        return view('public.attend-success', compact('attendee'));
    }

    public function verify(string $code): View
    {
        $attendee = Attendee::where('registration_number', $code)
                            ->with('expo')->firstOrFail();
        return view('public.verify', compact('attendee'));
    }

    // Admin: list attendees
    public function adminList(): View
    {
        $expo      = Expo::where('is_active', true)->first();
        $attendees = Attendee::where('expo_id', $expo?->id)
                             ->orderByDesc('created_at')
                             ->paginate(50);
        return view('admin.attendees', compact('attendees', 'expo'));
    }

    // Admin: check in an attendee
    public function checkIn(string $code): \Illuminate\Http\JsonResponse
    {
        $attendee = Attendee::where('registration_number', $code)->firstOrFail();
        $attendee->update([
            'checked_in'    => true,
            'checked_in_at' => now(),
        ]);
        return response()->json(['status' => 'ok', 'name' => $attendee->name]);
    }
}
