<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewAttendee;
use App\Mail\AttendeeWelcome;
use App\Models\Attendee;
use App\Models\Expo;
use App\Services\NotificationMailer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

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

        // Send emails (fire-and-forget after response)
        dispatch(function () use ($attendee) {
            $attendee->load('expo');
            $mailer = new NotificationMailer();
            if ($attendee->email) {
                $mailer->send(new AttendeeWelcome($attendee), $attendee->email);
            }
            if ($admin = $mailer->adminEmail()) {
                $mailer->send(new AdminNewAttendee($attendee), $admin);
            }
        })->afterResponse();

        return redirect()->route('attend.success', $attendee->registration_number);
    }

    public function success(string $code): View
    {
        $attendee = Attendee::where('registration_number', $code)->firstOrFail();
        return view('public.attend-success', compact('attendee'));
    }

    public function verify(string $code): View
    {
        $attendee = Attendee::where('registration_number', $code)->with('expo')->firstOrFail();
        return view('public.verify', compact('attendee'));
    }

    public function adminList(Request $request): View
    {
        $expo  = Expo::where('is_active', true)->first();
        $query = Attendee::where('expo_id', $expo?->id)->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('organisation', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $attendees = $query->paginate(50)->withQueryString();
        return view('admin.attendees', compact('attendees', 'expo'));
    }

    public function adminStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:120',
            'organisation' => 'nullable|string|max:120',
            'email'        => 'nullable|email|max:120',
            'phone'        => 'required|string|max:30',
        ]);

        $expo = Expo::where('is_active', true)->firstOrFail();

        Attendee::create([
            ...$data,
            'expo_id'             => $expo->id,
            'registration_number' => Attendee::generateRegNumber($expo->year),
        ]);

        return back()->with('success', 'Attendee added successfully.');
    }

    public function adminUpdate(Request $request, Attendee $attendee): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:120',
            'organisation' => 'nullable|string|max:120',
            'email'        => 'nullable|email|max:120',
            'phone'        => 'required|string|max:30',
            'checked_in'   => 'nullable|boolean',
        ]);

        if (!empty($data['checked_in']) && !$attendee->checked_in) {
            $data['checked_in_at'] = now();
        } elseif (empty($data['checked_in'])) {
            $data['checked_in']    = false;
            $data['checked_in_at'] = null;
        }

        $attendee->update($data);
        return back()->with('success', 'Attendee updated.');
    }

    public function adminDestroy(Attendee $attendee): RedirectResponse
    {
        $attendee->delete();
        return back()->with('success', 'Attendee deleted.');
    }

    public function checkIn(string $code): JsonResponse
    {
        $attendee = Attendee::where('registration_number', $code)->firstOrFail();
        $attendee->update(['checked_in' => true, 'checked_in_at' => now()]);
        return response()->json(['status' => 'ok', 'name' => $attendee->name]);
    }
}
