<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Users\ErasePersonalDataAction;
use App\Http\Requests\ProfileUpdateRequest;
use App\Support\AuthenticatedUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = AuthenticatedUser::from($request);
        $user->fill($request->safe()->except('avatar'));
        $oldAvatar = null;

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar')->store('avatars', 'public');
            abort_if($avatar === false, 500);

            $oldAvatar = $user->avatar;
            $user->avatar = $avatar;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($oldAvatar && ! str_starts_with($oldAvatar, 'https://')) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return Redirect::route('profile.edit')
            ->with('success', trans('ui.profile.updated'));
    }

    public function destroyPersonalData(Request $request, ErasePersonalDataAction $action): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $action->handle(AuthenticatedUser::from($request));

        return Redirect::route('profile.settings')
            ->with('success', trans('ui.profile.erase_success'));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = AuthenticatedUser::from($request);

        if ($user->hasActiveLoans()) {
            return Redirect::route('profile.edit')
                ->with('error', trans('ui.errors.account_has_active_loans'));
        }

        if ($user->avatar && ! str_starts_with($user->avatar, 'https://')) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
