<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminWelcome;
use App\Models\User;
use App\Services\NotificationMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::role(['admin', 'super_admin'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.users', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role'  => ['required', Rule::in(['admin', 'super_admin'])],
        ]);

        $plainPassword = Str::password(12, symbols: false); // readable 12-char password

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'password'             => Hash::make($plainPassword),
            'must_change_password' => true,
            'email_verified_at'    => now(), // admin accounts are pre-verified
        ]);

        $user->assignRole($validated['role']);

        // Send welcome email with temporary password
        dispatch(function () use ($user, $plainPassword) {
            $mailer = new NotificationMailer();
            $mailer->send(new AdminWelcome($user, $plainPassword), $user->email);
        })->afterResponse();

        return back()->with('success', "Admin account created for {$user->name}. A welcome email with login credentials has been sent.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'  => ['required', Rule::in(['admin', 'super_admin'])],
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Sync role
        $user->syncRoles([$validated['role']]);

        return back()->with('success', "{$user->name}'s account updated.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $plainPassword = Str::password(12, symbols: false);

        $user->update([
            'password'             => Hash::make($plainPassword),
            'must_change_password' => true,
        ]);

        dispatch(function () use ($user, $plainPassword) {
            $mailer = new NotificationMailer();
            $mailer->send(new AdminWelcome($user, $plainPassword), $user->email);
        })->afterResponse();

        return back()->with('success', "Password reset for {$user->name}. New credentials emailed.");
    }

    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'Admin account deleted.');
    }
}
